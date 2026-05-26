<?php

namespace WatchTheDot\Plugins\RSSImporter\Admin;

use DateTime;
use WatchTheDot\Plugins\RSSImporter\Model\Feed;

class Errors_ListTable extends ListTable {
	public function __construct() {
		parent::__construct( 'sync-error', 'sync-errors' );
	}

	public function get_columns() {
		return array(
			'datetime' => __( 'Date/Time', 'rss-feed-importer' ),
			'feed'     => __( 'Feed', 'rss-feed-importer' ),
			'message'  => __( 'Message', 'rss-feed-importer' ),
		);
	}

	protected function get_bulk_actions() {
		return array();
	}

	private function get_feed_dropdown() {
		$feeds = Feed::all();
		$current_filter = $_GET['filter']['feed'] ?? '';
		?>
		<select name="filter[feed]">
			<option value="">Select Feed</option>
			<?php foreach ( $feeds as $feed ) : ?>
				<option
					value="<?php echo esc_attr( $feed->id ); ?>"
					<?php selected( $current_filter, $feed->id ); ?>
				><?php echo esc_html( $feed->name ); ?></option>
			<?php endforeach; ?>
		</select>
		<?php
	}

	protected function extra_tablenav( $which ) {
		if ( $which !== 'top' ) {
			return;
		}

		$this->get_feed_dropdown();
		submit_button( __( 'Filter' ), '', '', false, array( 'formmethod' => 'GET' ) );

		if ( count( $this->items ) ) {
			echo '&nbsp;';
			submit_button( __( 'Empty' ), 'primary', 'empty_errors', false, array() );
		}
	}

	protected function get_rows(): array {
		global $wpdb;

		$extra_queries = array();
		$filter = $_GET['filter'] ?? [];
		if ( isset( $filter['feed'] ) ) {
			$extra_queries[] = $wpdb->prepare( '`feeds`.`id` = %d', $filter['feed'] );
		}

		$conditions = empty( $extra_queries ) ? '' : 'WHERE ' . implode( ' AND ', $extra_queries );
		$rows       = $wpdb->get_results(
			"SELECT
				`errors`.`id`,
				`feeds`.`id` as `feed_id`,
				`feeds`.`name` as `feed_name`,
				`errors`.`created_at`,
				`errors`.`error`
			FROM `{$wpdb->prefix}rss_sync_errors` errors
			JOIN `{$wpdb->prefix}rss_feeds` feeds ON errors.feed_id = feeds.id
			{$conditions}
			ORDER BY `errors`.`created_at` DESC",
			ARRAY_A
		);

		return $rows;
	}

	public function column_default( $item, $column ) {
		switch ( $column ) {
			case 'datetime':
				return ( new DateTime( $item['created_at'] ) )->format( 'd/m/Y H:i' );
			case 'feed':
				return sprintf(
					"<a href='%s'>%s</a>",
					esc_url( add_query_arg( array( 'filter_feed' => $item['feed_id'] ) ) ),
					esc_html( $item['feed_name'] )
				);
			case 'message':
				return nl2br( esc_html( $item['error'] ) );
			default:
				return '-';
		}
	}
}