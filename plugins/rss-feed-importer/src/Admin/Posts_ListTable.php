<?php

namespace WatchTheDot\Plugins\RSSImporter\Admin;

use WatchTheDot\Plugins\RSSImporter\Model\Feed;
use WatchTheDot\Plugins\RSSImporter\Model\FeedPost;

use WatchTheDot\Plugins\RSSImporter\Actions\ImportPost;
use function WatchTheDot\Plugins\RSSImporter\array_kmap;

class Posts_ListTable extends ListTable {
	public function __construct() {
		parent::__construct( 'feed-post', 'feed-posts', false );
	}

	public function get_columns() {
		return array(
			'title'     => __( 'Title', 'rss-feed-importer' ),
			'source'    => __( 'Source', 'rss-feed-importer' ),
			'preview'   => __( 'Preview', 'rss-feed-importer' ),
			'published' => __( 'Published At', 'rss-feed-importer' ),
		);
	}

	public function get_sortable_columns() {
		return array(
			'published' => array( 'published', true ),
		);
	}

	protected function get_current_filter() {
		return $_GET['filter'] ?? 'pending';
	}

	protected function get_rows(): array {
		global $wpdb;
		
		$orderby = $_GET['orderby'] ?? '';
		if ( ! isset( $this->get_sortable_columns()[ $orderby ] ) ) {
			$orderby = 'published';
		}

		$order = $_GET['order'] ?? 'DESC';
		if ( ! in_array( $order, array( 'ASC', 'DESC' ), true ) ) {
			$order = 'DESC';
		}

		$filter_status = $this->get_current_filter();
		if ( ! isset( $this->get_views()[ $filter_status ] ) ) {
			return [];
		}

		$extra_query = '';

		$feed = $_GET['feed'] ?? '';
		if ( ! empty( $feed ) ) {
			$extra_query .= $wpdb->prepare( 'AND `rss_feeds`.`id` = %d', $feed );
		}

		$search = trim( $_GET['search'] ?? '' );
		if ( ! empty( $search ) ) {
			$extra_query .= $wpdb->prepare(
				'AND (
					`rss_feed_posts`.`post_title` LIKE %s
					OR `rss_feed_posts`.`preview` LIKE %s
				)',
				"%{$wpdb->esc_like( $search )}%",
				"%{$wpdb->esc_like( $search )}%",
			);
		}

        $per_page = $this->get_items_per_page("items_per_page");
        $current_page = $this->get_pagenum();
        $total_items = $this->get_total_items( $filter_status, $extra_query );

        $this->set_pagination_args([
            'total_items' => $total_items,
            'per_page'    => $per_page,
            'total_pages' => ceil($total_items / $per_page)
        ]);

		$start_index = ($current_page - 1) * $per_page;
		
		$sql = $wpdb->prepare(
			"SELECT 
				`rss_feed_posts`.`hash`,
				`rss_feed_posts`.`post_title`, 
				`rss_feed_posts`.`site_title`, 
                `rss_feed_posts`.`preview`, 
				`rss_feeds`.`id` as `source_id`,
                `rss_feeds`.`name` as `source`, 
                `rss_feed_posts`.`link` as `url`, 
                `rss_feed_posts`.`published_at` as `published`
            FROM `{$wpdb->prefix}rss_feed_posts` as `rss_feed_posts`
            JOIN `{$wpdb->prefix}rss_feeds` as `rss_feeds`
            ON `rss_feed_posts`.`feed_id` = `rss_feeds`.`id`
            WHERE `rss_feed_posts`.`status` = %s
            {$extra_query}
            ORDER BY `{$orderby}` {$order}
			LIMIT {$start_index}, {$per_page}",
			$filter_status
		);

		$data = $wpdb->get_results( $sql, ARRAY_A );
		return $data;
	}

	protected function get_total_items( $filter_status, $extra_query ) {
		global $wpdb;

		return $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*)
				FROM `{$wpdb->prefix}rss_feed_posts` as `rss_feed_posts`
				WHERE `rss_feed_posts`.`status` = %s
				{$extra_query}",
				$filter_status
			)
		) ?? 0;
	}

	protected function get_bulk_actions() {
		$actions = array(
			'import' => __( 'Import', 'rss-feed-importer' ),
		);

		if ( $this->get_current_filter() === 'pending' ) {
			$actions['reject'] = __( 'Reject', 'rss-feed-importer' );
		}

		return $actions;
	}

	public function action_import( $items ) {
		$post_ids = ImportPost::bulk_run( $items );

		$first_line = sprintf(
			_n( 'Imported %d post', 'Imported %d posts', count( $items ), 'rss-feed-importer' ),
			count( $items ),
		);

		$second_line = '<ul>' . implode(
			'',
			array_map(
				fn ( $post_id ) => sprintf(
					"<li>%s: <a href='%s'>View</a> | <a href='%s'>Edit</a></li>",
					get_the_title( $post_id ),
					get_permalink( $post_id ),
					get_edit_post_link( $post_id ),
				),
				$post_ids
			)
		) . '</ul>';

		$this->add_notice(
			'success',
			$first_line . "<br>" . $second_line,
		);
	}

	public function action_reject( $items ) {
		foreach ( $items as $item ) {
			$rss_post         = FeedPost::find( $item );
			$rss_post->status = 'rejected';
			$rss_post->save();
		}

		$this->add_notice(
			'success',
			sprintf(
				_n( 'Rejected %d post', 'Rejected %d posts', count( $items ), 'rss-feed-importer' ),
				count( $items ),
			)
		);
		return true;
	}

	protected function get_views() {
		$current_filter = $this->get_current_filter();
		$views          = array(
			'pending'   => __( 'Pending', 'rss-feed-importer' ),
			'imported'  => __( 'Imported', 'rss-feed-importer' ),
			'rejected' => __( 'Rejected', 'rss-feed-importer' ),
		);

		return array_kmap(
			static fn ( $key, $value ) => sprintf(
				"<a href='%s' %s>%s (%s)</a>",
				esc_url( add_query_arg( 'filter', $key ) ),
				$current_filter === $key ? 'class="current"' : '',
				$value,
				FeedPost::count( array( 'status' => $key ) ),
			),
			$views
		);
	}

	private function get_feed_dropdown() {
		$feeds = Feed::all();
		?>
		<select name="feed">
			<option value="">Select Feed</option>
			<?php foreach ( $feeds as $feed ) : ?>
				<option
					value="<?php echo esc_attr( $feed->id ); ?>"
					<?php selected( $_GET['feed'] ?? '', $feed->id ); ?>
				><?php echo esc_html( $feed->name ); ?></option>
			<?php endforeach; ?>
		</select>
		<?php
	}

	private function add_search_option() {
		$search = $_GET['search'] ?? '';
		?>
		<label style="position: relative">
			<span class="dashicons dashicons-search" style="position: absolute; top: 50%; left: 4px; transform: translateY(-50%)"></span>
			<input type="search" name="search" placeholder="Search Posts" style="padding-left: 2em" value="<?php echo esc_attr( $search ); ?>">
		</label>
		<script>
			jQuery(function ($) {
				$("input[name='search']").on('keypress', function (event) {
					if (!["Enter", "NumpadEnter"].includes(event.code)) return;
					event.preventDefault();
					const $this = $(this);
					
					let btn;
					if (
						(btn = $this.siblings("input[type='submit']")).length
						|| (btn = $this.parent().siblings("input[type='submit']")).length
					) {
						btn.click();
					}
				});
			});
		</script>
		<?php
	}

	protected function extra_tablenav( $which ) {
		if ( $which !== 'top' ) {
			return;
		}

		$this->get_feed_dropdown();
		$this->add_search_option();
		submit_button(
			__( 'Filter' ),
			'',
			'',
			false,
			array( 'formmethod' => 'GET' )
		);
	}

	public function column_cb( $item ) {
		?>
		<input type="checkbox" name="item[]" value="<?php echo esc_attr( $item['hash'] ); ?>">
		<?php
	}

	public function column_title( $item ) {
		$current_filter = $this->get_current_filter();

		$actions = array(
			'read' => sprintf(
				"<a href='%s' target='_blank'>%s</a>",
				esc_url( $item['url'] ),
				__( 'Read Article', 'rss-feed-importer' ),
			),
		);

		switch ( $current_filter ) {
			case 'imported':
				break;
			case 'pending':
				$actions['import'] = sprintf(
					"<a href='%s' data-import-id='%s'>%s</a>",
					$this->create_action_url( 'import', $item['hash'] ),
					esc_attr( $item['hash'] ),
					__( 'Import', 'rss-feed-importer' )
				);

				$actions['delete'] = sprintf(
					"<a href='%s'>%s</a>",
					$this->create_action_url( 'reject', $item['hash'] ),
					__( 'Reject', 'rss-feed-importer' )
				);
				break;
			case 'rejected':
				break;
		}

		echo '<strong>' . esc_html( $item['post_title'] ) . '</strong>';
		echo $this->row_actions( $actions );
	}

	/**
	 * @param FeedPost $item
	 */
	public function column_source( $item ) {
		printf(
			<<<HTML
			<p style='margin-bottom: 0'>
			<a href='%s'>
				%s
				<span class="dashicons dashicons-filter" style="width: 15px; height: 15px; font-size: 15px;"></span>
			</a>
			</p>
			HTML,
			add_query_arg( 'feed', $item['source_id'] ),
			esc_html( $item['source'] ),
		);

		$favicon_url = FeedPost::find( $item['hash'] )?->favicon();

		printf(
			"<p style='display: flex; align-items: center; gap: 4px;'>
				<img src='%s' alt='%s' style='height: 16px; width: 16px;'> 
				<span>%s</span>
			</p>",
			esc_url( $favicon_url ?? '#' ),
			esc_attr( $item['site_title'] ),
			esc_html( $item['site_title'] ),
		);
	}

	public function column_default( $item, $column_name ) {
		echo $item[ $column_name ] ?? '';
	}
}