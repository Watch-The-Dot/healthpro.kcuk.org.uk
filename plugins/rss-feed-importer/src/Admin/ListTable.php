<?php

namespace WatchTheDot\Plugins\RSSImporter\Admin;


/**
 * @template T
 * 
 */
abstract class ListTable extends \WP_List_Table {
    protected bool $has_integer_ids;

    public function __construct( string $singular, string $plural, bool $integer_ids = true ) {
        parent::__construct([
            'singular' => $singular, // Singular label
            'plural'   => $plural, // Plural label
            'ajax'     => false, // Enable AJAX
        ]);

        $this->has_integer_ids = $integer_ids;
    }

	public function prepare_items() {
		global $wpdb;

		$this->handle_actions();
		$_SERVER['REQUEST_URI'] = remove_query_arg(['item', 'action', '_wpnonce']);

		// Define column headers
		$columns  = [
            'cb'        => '<input type="checkbox" />',
            ...$this->get_columns()
        ];
		$hidden   = array();
		$sortable = $this->get_sortable_columns();

        $rows = $this->get_rows();

		// Set up table items
		$this->_column_headers = array( $columns, $hidden, $sortable );
		$this->items           = $rows;
	}

    /**
     * @return T[]
     */
    protected abstract function get_rows(): array;

    public function handle_actions() {
        $action = sanitize_text_field( wp_unslash( $_POST['action'] ?? $_GET['action'] ?? '' ) );
        if ( empty( $action ) ) {
            return;
        }

        if ( ! isset( $this->get_bulk_actions()[ $action ] ) ) {
			return;
		}

        $items = $this->parse_action_items( $action );
        if ( empty( $items ) ) {
            $this->add_notice( 'error', __( 'Please select items', 'rss-feed-importer' ) );
            return;
        }

        $this->{"action_{$action}"}( $items );
    }

    private function parse_action_items( string $action ) {
        $item = wp_unslash( $_POST['item'] ?? $_GET['item'] ?? '' );

        if ( empty( $item ) ) {
            return [];
        }

        $method = is_scalar( $item ) ? "parse_action_items_scalar" : "parse_action_items_array";
        return $this->{$method}( $action, $item );
    }

    private function parse_action_items_scalar( string $action, $item ) {
        // Need to check single action nonce
        check_admin_referer( "single-{$this->_args['singular']}-{$action}-{$item}" );

        $item = sanitize_text_field( wp_unslash( $item ) );
        if ( $this->has_integer_ids ) {
            $item = is_numeric( $item ) ? intval( $item ) : 0;
            $item = $item > 0 ? $item : 0;
        }

        return array_filter( [ $item ] );
    }

    private function parse_action_items_array( string $action, array $items ) {
        // Need to check bulk action nonce
        check_admin_referer( "bulk-{$this->_args['plural']}" );

        $items = array_map( "sanitize_text_field", $items );
        if ( $this->has_integer_ids ) {
			$items = array_filter( $items, 'is_numeric' );
			$items = array_map( 'intval', $items );
			$items = array_filter( $items, static fn ( $v ) => $v > 0 );
        }
        $items = array_filter( $items, fn ($v) => ! empty( $v ) );

        return $items;
    }

    protected function add_notice( string $type, string $body ) {
        ?>
        <div class="notice notice-<?php echo esc_attr( $type ); ?>">
			<p>
			<?php
			echo wp_kses(
				$body,
				array(
					'br'     => array(),
					'strong' => array(),
                    'a' => array( 'href' => true ),
				)
			);
			?>
			</p>
		</div>
        <?php
    }

    protected function create_action_url( string $action, $item, $url = null ) {
        $query_args = array(
            'item'   => $item,
            'action' => $action,
        );

        if ( is_null( $url ) ) {
            $url = add_query_arg( $query_args );
        } else {
            $url = add_query_arg( $query_args, $url );
        }

        return wp_nonce_url( $url, "single-{$this->_args['singular']}-{$action}-{$item}" );
    }
}