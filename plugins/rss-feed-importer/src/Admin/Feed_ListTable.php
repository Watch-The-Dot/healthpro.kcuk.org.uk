<?php

namespace WatchTheDot\Plugins\RSSImporter\Admin;

use Exception;
use WatchTheDot\Plugins\RSSImporter\Actions\SyncFeed;
use WatchTheDot\Plugins\RSSImporter\Settings;
use WatchTheDot\Plugins\RSSImporter\Model\Feed;
use WatchTheDot\Plugins\RSSImporter\Model\FeedPost;

use function WatchTheDot\Plugins\RSSImporter\array_kmap;

class Feed_ListTable extends ListTable {
	public function __construct() {
		parent::__construct( 'feed', 'feeds' );
	}

	public function get_columns() {
		return array(
			'name'        => __( 'Name', 'rss-feed-importer' ),
			'feed'        => __( 'Feed', 'rss-feed-importer' ),
			'rss_posts'   => __( 'Posts', 'rss-feed-importer' ),
			'last_synced' => __( 'Last Synced', 'rss-feed-importer' ),
		);
	}

	protected function get_bulk_actions() {
		return array(
			'sync'   => __( 'Sync', 'rss-feed-importer' ),
			'delete' => __( 'Delete', 'default' ),
		);
	}

	protected function get_rows(): array
	{
		return Feed::all();
	}

	public function handle_actions() {
		$action = sanitize_text_field( wp_unslash( $_POST['action'] ?? $_GET['action'] ?? '' ) );
		if ( $action === 'add-new-feed' ) {
			return $this->action_add_new_feed();
		}

		parent::handle_actions();
	}

	public function action_add_new_feed() {
		if ( ! check_admin_referer( 'add-new-feed' ) ) {
			return;
		}

		$title = sanitize_text_field( $_POST['feed_title'] ?? '' );
		$url   = sanitize_url( $_POST['feed_url'] ?? '' );
		try {
			if ( empty( $title ) ) {
				throw new Exception( __( 'Feed Title must be defined', 'rss-feed-importer' ) );
			}

			if ( empty( $url ) ) {
				throw new Exception( __( 'Feed URL must be defined', 'rss-feed-importer' ) );
			}

			if ( ! filter_var( $url, FILTER_VALIDATE_URL ) ) {
				throw new Exception( __( 'Feed URL must be a valid URL', 'rss-feed-importer' ) );
			}

			if ( strlen( $url ) >= 100 ) {
				throw new Exception( __( 'Feed URL must be less than 100 characters', 'rss-feed-importer' ) );
			}
		} catch ( Exception $e ) {
			Settings::flash_message(
				'error',
				$e->getMessage(),
			);
			return;
		}

		$feed       = new Feed();
		$feed->name = $title;
		$feed->url  = $url;
		
		if ( ! $feed->save() ) {
			$this->add_notice(
				'error',
				'Failed to save feed'
			);
			return;
		}

		as_enqueue_async_action( 'rss-feed-importer/sync_feed', array( $feed_id ) );

		$this->add_notice(
			'success',
			__( 'Successfully added feed', 'rss-feed-importer' ),
		);
	}

	public function action_sync( $items ) {
		foreach ( $items as $item ) {
			// as_enqueue_async_action( 'rss-feed-importer/sync_feed', array( $item ) );
			SyncFeed::run( $item );
		}

		$this->add_notice(
			'success',
			sprintf(
				_n( 'Syncing %d feed', 'Syncing %d feeds', count( $items ), 'rss-feed-importer' ),
				count( $items )
			)
		);
	}

	public function action_delete( $items ) {
		foreach ( $items as $id ) {
			$feed = Feed::find( $id );
			$feed?->delete();
		}

		$this->add_notice(
			'success',
			sprintf(
				'<strong>%s:</strong><br>%s',
				__( 'Successfully Deleted', 'rss-feed-importer' ),
				sprintf(
					_n( '%d feed', '%d feeds', count( $items ), 'rss-feed-importer' ),
					number_format_i18n( count( $items ) )
				)
			)
		);
	}

	/**
	 * @param Feed $item
	 */
	public function column_cb( $item ) {
		?>
		<input
			type="checkbox"
			id="cb-select-<?php echo esc_attr( $item->id ); ?>"
			name="items[]"
			value="<?php echo esc_attr( $item->id ); ?>"
		>
		<?php
	}

	/**
	 * @param Feed $item
	 */
	public function column_name( $item ) {
		$actions = array(
			'sync'   => __( 'Sync', 'rss-feed-importer' ),
			'delete' => __( 'Delete', 'default' ),
		);

		$actions = array_kmap(
			static function ( $action, $text ) use ( $item ) {
				$url = wp_nonce_url(
					add_query_arg(
						array(
							'item'   => $item->id,
							'action' => $action,
						)
					),
					"single-feed-{$action}-{$item->id}"
				);

				return sprintf(
					"<a href='%s'>%s</a>",
					esc_url( $url ),
					esc_html( $text )
				);
			},
			$actions,
		);

		echo '<strong>' . esc_html( $item->name ) . '</strong>';
		echo $this->row_actions( $actions );
	}

	/**
	 * @param Feed $item
	 */
	public function column_feed( $item ) {
		echo esc_url( $item->url );
	}

	/**
	 * @param Feed $item
	 */
	public function column_rss_posts( $item ) {
		$statuses = array(
			'pending'  => __( 'Pending', 'rss-feed-importer' ),
			'imported' => __( 'Imported', 'rss-feed-importer' ),
			'rejected' => __( 'Rejected', 'rss-feed-importer' ),
		);

		foreach ( $statuses as $status => $text ) {
			$count = FeedPost::count(
				array(
					'status'  => $status,
					'feed_id' => $item->id,
				)
			);

			printf(
				'%d %s<br>',
				$count,
				esc_html( $text ),
			);
		}
	}

	/**
	 * @param Feed $item
	 */
	public function column_last_synced( $item ) {
		if ( is_null( $item->synced_at ) ) {
			echo '<em>Not Synced</em>';
			return;
		}

		echo $item->synced_at->format( 'd/m/y H:i' );
	}
}