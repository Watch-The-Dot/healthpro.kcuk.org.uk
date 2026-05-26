<?php
declare( strict_types=1 );

namespace WatchTheDot\Plugins\RSSImporter\Actions;

use SimpleXMLElement;
use WatchTheDot\Plugins\RSSImporter\Model\Feed;
use WatchTheDot\Plugins\RSSImporter\Model\FeedPost;
use WatchTheDot\Plugins\RSSImporter\Model\SyncError;
use WatchTheDot\Plugins\RSSImporter\Support\OpenGraph;

class SyncFeed {
	public static function run( $feed_id ) {
		$feed = Feed::find( $feed_id );
		if ( ! $feed ) {
			return;
		}

		$response = wp_remote_get( $feed->url );
		$body     = wp_remote_retrieve_body( $response );
		if ( ! $body ) {
			$response_code    = wp_remote_retrieve_response_code( $response );
			$response_message = wp_remote_retrieve_response_message( $response );
			$message          = "Body was blank.\nResponse returned {$response_code} ({$response_message})";

			SyncError::create( $feed_id, $message );
			return;
		}

		libxml_use_internal_errors( true );
		$xml = simplexml_load_string( $body );
		if ( ! $xml ) {
			$message = "Couldn't parse RSS Feed.\nErrors were:";
			foreach ( libxml_get_errors() as $error ) {
				$message .= $error->message . "\n";
			}

			SyncError::create( $feed_id, $message );
			return;
		}
		libxml_use_internal_errors( false );

		if ( isset( $xml->channel ) ) {
			self::sync_feed_rss( $feed, $xml );
		} elseif ( isset( $xml->entry ) ) {
			self::sync_feed_google_alert( $feed, $xml );
		} else {
			$message = "Couldn't parse RSS Feed.\nCouldn't find entries (RSS Feed uses <channel>, Google Alerts uses <entry>)";

			SyncError::create( $feed_id, $message );
			return;
		}

		$feed->synced_at = current_datetime();
		$feed->save();
	}

	private static function sync_feed_rss( Feed $feed, SimpleXMLElement $xml ) {
		$channel    = $xml->channel;
		$item_count = $channel->item->count();

		$found_posts = self::get_current_posts( $feed->id );

		for ( $i = 0; $i < $item_count; $i++ ) {
			$item = $channel->item[ $i ];
			$guid = (string) $item->link;
			if ( isset( $found_posts[ $guid ] ) ) {
				$found_posts[ $guid ] = true;
				continue;
			}

			$link         = (string) $item->link;
			$published_at = strtotime( (string) $item->pubDate );
			
			/* PCM Fix 20260401 */
			//$title        = sanitize_text_field( (string) $item->title );
			$title = sanitize_text_field( (string) $item->title );
            if (empty($title)) {
                $title = 'Untitled';
            }
			
			$preview      = sanitize_textarea_field( (string) $item->description );

			$rss_post               = new FeedPost();
			$rss_post->guid         = $guid;
			$rss_post->feed_id      = $feed->id;
			$rss_post->post_title   = $title;
			$rss_post->link         = $link;
			$rss_post->preview      = $preview;
			$rss_post->published_at = $published_at;

			self::set_common_attributes( $rss_post );	
			$rss_post->save();

			$found_posts[ $guid ] = true;
		}
	}

	private static function sync_feed_google_alert( Feed $feed, SimpleXMLElement $xml ) {
		$entries_count = $xml->entry->count();
		$found_posts   = self::get_current_posts( $feed->id );

		for ( $i = 0; $i < $entries_count; $i++ ) {
			/** @var SimpleXMLElement */
			$entry = $xml->entry[ $i ];
			$guid  = (string) $entry->id;
			if ( isset( $found_posts[ $guid ] ) ) {
				$found_posts[ $guid ] = true;
				continue;
			}

			$title = wp_kses( (string) $entry->title, array() );

			$link = (string) $entry->link->attributes()->href;
			parse_str( parse_url( $link, PHP_URL_QUERY ), $results );
			if ( isset( $results['url'] ) ) {
				$link = $results['url'];
			}

			$og           = OpenGraph::fetch( $link );
			
			/* PCM Fix 20260401 */
			//$preview      = $og?->description ?? wp_kses( (string) $entry->content, array() );
			$preview = ($og && !empty($og->description))
                ? $og->description
                : wp_kses( (string) $entry->content, array() );
			
			
			
			$published_at = (string) $entry->published;

			$rss_post               = new FeedPost();
			$rss_post->guid         = $guid;
			$rss_post->feed_id      = $feed->id;
			$rss_post->post_title   = $title;
			$rss_post->link         = $link;
			$rss_post->preview      = $preview;
			$rss_post->published_at = $published_at;

			self::set_common_attributes( $rss_post );
			$rss_post->save();

			$found_posts[ $guid ] = true;
		}
	}

	private static function set_common_attributes( FeedPost &$rss_post ) {
		$opengraph = OpenGraph::fetch( $rss_post->link );
		if ( $opengraph === false ) {
			return;
		}

		// TODO: Add <title> tag use?
		/* PCM Fix 20260401 */
		//$rss_post->site_title = $opengraph->site_name;
		$rss_post->site_title = !empty($opengraph->site_name)
            ? $opengraph->site_name
            : parse_url($rss_post->link, PHP_URL_HOST);
		
	}

	private static function get_current_posts( $feed_id ) {
		global $wpdb;

		$current_posts = array_map(
			static fn ( FeedPost $rss_post ) => $rss_post->guid,
			FeedPost::all( array( 'feed_id' => $feed_id ) ),
		);
		return array_combine(
			$current_posts,
			array_pad( array(), count( $current_posts ), false )
		);
	}
}
