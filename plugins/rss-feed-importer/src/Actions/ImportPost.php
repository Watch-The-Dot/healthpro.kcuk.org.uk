<?php
declare( strict_types=1 );

namespace WatchTheDot\Plugins\RSSImporter\Actions;

use WatchTheDot\Plugins\RSSImporter\Fetch\Fetcher;
use WatchTheDot\Plugins\RSSImporter\Fetch\FetchResult;
use WatchTheDot\Plugins\RSSImporter\Fetch\Media\ImageDownloader;
use WatchTheDot\Plugins\RSSImporter\Model\Feed;
use WatchTheDot\Plugins\RSSImporter\Model\FeedPost;
use WatchTheDot\Plugins\RSSImporter\Settings;
use WP_Error;

class ImportPost {
	public static function run( $item ) {
		$rss_post = FeedPost::find( $item );
		if ( is_null( $rss_post ) || $rss_post->status === "imported" ) {
			return;
		}

		$fetcher = Fetcher::from_feed_post( $rss_post );
		try {
			$fetch_result = $fetcher->get_result();
			$post_id = self::save( $fetch_result, $rss_post );
		} catch ( \Exception $e ) {
			return new WP_Error('', $e->getMessage());
		}

		$rss_post->status = 'imported';
		$rss_post->save();

		do_action( 'rss-feed-importer/post_imported', $rss_post, $post_id );

		return $post_id;
	}

	private static function save( FetchResult $result, FeedPost $rss_post ) {
		$meta = array(
			'link'        => $rss_post->link,
			'imported_at' => current_time( 'mysql' ),
			'feed_id'     => $rss_post->feed_id,
		);

		if ( $feed = Feed::find( $rss_post->feed_id ) ) {
			$meta['feed_name'] = $feed->name;
		}

		if ( ! is_null( $result->article_authors ) ) {
			$meta['authors'] = $result->article_authors;
		}

		if ( ! is_null( $result->site_favicon ) ) {
			$host = wp_parse_url( $rss_post->link, PHP_URL_HOST );

			$attachment = ( new ImageDownloader( $result->site_favicon ) )
				->set_metadata( '_feed_importer_favicon', $host )
				->save();
			if ( ! is_wp_error( $attachment ) ) {
				$meta['site_favicon'] = $attachment->attachment_id;
			}
		}

		if ( ! is_null( $result->article_publisher ) ) {
			$meta['publisher'] = $result->article_publisher;
		}

		if ( ! is_null( $result->publisher_logo ) ) {
			$host = wp_parse_url( $rss_post->link, PHP_URL_HOST );

			$attachment = ( new ImageDownloader( $result->publisher_logo ) )
				->set_metadata( '_feed_importer_logo', $host )
				->save();
			if ( ! is_wp_error( $attachment ) ) {
				$meta['site_logo'] = $attachment->attachment_id;
			}
		}

		$tags = Settings::instance()->wpsf->get_settings()['import_settings_tag'];
		$tags = array_map( 'intval', $tags );

		$postarr = array(
			'post_type'    => 'post',
			'post_status'  => 'imported',
			'post_author'  => get_current_user_id(),
			'post_title'   => $result->article_title,
			'post_content' => $result->article_content,
			'post_excerpt' => $result->article_excerpt,
			'post_date'    => $result->article_published->format( 'Y-m-d H:i:s' ),
			'meta_input'   => [
				'rss-feed-importer_import_meta' => $meta
			],
			'tax_input'    => [
				'post_tag' => $tags,
			]
		);

		/**
		 * @param array $postarr
		 * @param FeedPost $rss_post
		 */
		$postarr = apply_filters( 'rss-feed-importer/import_post_data', $postarr, $rss_post );

		$post_id = wp_insert_post( $postarr );
		if ( is_wp_error( $post_id ) ) {
			return $post_id;
		}

		if ( ! is_null( $result->featured_image ) ) {
			$attachment_id = ( new ImageDownloader( $result->featured_image ) )
				->set_max_dimensions( 150 )
				->save();
				
			set_post_thumbnail( $post_id, $attachment_id );
		}

		return $post_id;
	}

	public static function bulk_run( $items ) {
		return array_map( self::run(...), $items );
	}
}
