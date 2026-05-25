<?php

namespace HealthPro\Resources\PostTypes;

use HealthPro\Resources\Actions\ImportProduct;
use HealthPro\Resources\Plugin;
use InvalidArgumentException;
use WP_Post;
use WP_Post_Type;

class Resources {
	const POST_TYPE = 'resource';

	public static self $instance;

	private WP_Post_Type $post_type;

	private function __construct() {
		add_action( 'init', array( $this, 'register' ) );

		add_filter( 'post_thumbnail_id', array( $this, 'override_thumbnail_id_to_use_remote' ), 10, 2 );
		add_filter( 'post_thumbnail_url', array( $this, 'override_thumbnail_url_to_use_remote' ), 10, 3 );
		add_filter( 'dpdfg_thumbnail_url', array( $this, 'override_thumbnail_url_to_use_remote_divi_filtergrid' ), 10, 5 );
	}

	/**
	 * @hook init
	 */
	public function register() {
		$labels = array(
			'name'               => _x( 'Resources', 'post type general name', 'healthpro-resources' ),
			'singular_name'      => _x( 'Resource', 'post type singular name', 'healthpro-resources' ),
			'menu_name'          => _x( 'Resources', 'admin menu', 'healthpro-resources' ),
			'name_admin_bar'     => _x( 'Resource', 'add new on admin bar', 'healthpro-resources' ),
			'add_new'            => _x( 'Add New', 'resource', 'healthpro-resources' ),
			'add_new_item'       => __( 'Add New Resource', 'healthpro-resources' ),
			'new_item'           => __( 'New Resource', 'healthpro-resources' ),
			'edit_item'          => __( 'Edit Resource', 'healthpro-resources' ),
			'view_item'          => __( 'View Resource', 'healthpro-resources' ),
			'all_items'          => __( 'All Resources', 'healthpro-resources' ),
			'search_items'       => __( 'Search Resources', 'healthpro-resources' ),
			'parent_item_colon'  => __( 'Parent Resources:', 'healthpro-resources' ),
			'not_found'          => __( 'No resources found.', 'healthpro-resources' ),
			'not_found_in_trash' => __( 'No resources found in Trash.', 'healthpro-resources' ),
		);

		$args = array(
			'labels'             => $labels,
			'public'             => true,
			'publicly_queryable' => true,
			'show_ui'            => false,
			'show_in_menu'       => false,
			'query_var'          => true,
			'rewrite'            => array( 'slug' => 'resource' ),
			'capability_type'    => 'post',
			'has_archive'        => false,
			'hierarchical'       => false,
			'menu_position'      => null,
			'supports'           => array( 'title', 'thumbnail' ),
		);

		$this->post_type = register_post_type( self::POST_TYPE, $args );
	}

	/**
	 * @hook post_thumbnail_id
	 */
	public function override_thumbnail_id_to_use_remote( $thumbnail_id, $post ) {
		if ( ! $this->is_post_this_type( $post ) ) {
			return $thumbnail_id;
		}

		return -1;
	}

	/**
	 * @hook post_thumbnail_url
	 */
	public function override_thumbnail_url_to_use_remote( $thumbnail_url, $post, $size ) {
		if ( ! $this->is_post_this_type( $post ) ) {
			return $thumbnail_url;
		}

		return get_post_meta( is_object( $post ) ? $post->ID : $post, ImportProduct::META_LINKED_PRODUCT_IMAGE, true );
	}

	public function override_thumbnail_url_to_use_remote_divi_filtergrid( $thumbnail_url, $props, $width, $height, $post_id ) {
		return $this->override_thumbnail_url_to_use_remote( $thumbnail_url, $post_id, array( $width, $height ) );
	}

	private function is_post_this_type( WP_Post|int $post ) {
		return get_post_type( $post ) === $this->post_type->name;
	}

	public static function convert_to_array( WP_Post|int $post ) {
		$post = self::gate( $post );

		return array(
			'post_id'    => $post->ID,
			'id'         => get_post_meta( $post->ID, ImportProduct::META_LINKED_PRODUCT_ID, true ),
			'title'      => $post->post_title,
			'url'        => get_post_meta( $post->ID, ImportProduct::META_LINKED_PRODUCT_URL, true ),
			'backorders' => get_post_meta( $post->ID, ImportProduct::META_LINKED_PRODUCT_BACKORDERS, true ),
			'quantity'   => get_post_meta( $post->ID, ImportProduct::META_LINKED_PRODUCT_STOCK, true ),
			'image'      => get_the_post_thumbnail_url( $post ),
			'synced_at'  => get_post_meta( $post->ID, ImportProduct::META_SYNC_TIME, true ),
			'online_version' => get_post_meta( $post->ID, ImportProduct::META_LINKED_PRODUCT_ONLINE, true ),
		);
	}

	public static function is_resource_in_stock( WP_Post|int $post ) {
		$post = self::gate( $post );

		$quantity   = get_post_meta( $post->ID, ImportProduct::META_LINKED_PRODUCT_STOCK, true );
		$backorders = get_post_meta( $post->ID, ImportProduct::META_LINKED_PRODUCT_BACKORDERS, true );

		if ( $backorders ) {
			return true;
		}

		$quantity_options   = Plugin::instance()->settings()->get_settings()['order_form_quantity_options'] ?: array();
		$minimum_order_size = array_reduce(
			$quantity_options,
			static fn ( $carry, $curr ) => min( $carry, intval( $curr['quantity'] ) ),
			PHP_INT_MAX
		);

		return intval( $quantity ) >= $minimum_order_size;
	}

	/**
	 * Returns PHP_INT_MAX if backorders are allowed
	 */
	public static function get_stock_remaining( WP_Post|int $post ) {
		$post = self::gate( $post );

		$quantity   = get_post_meta( $post->ID, ImportProduct::META_LINKED_PRODUCT_STOCK, true );
		$backorders = get_post_meta( $post->ID, ImportProduct::META_LINKED_PRODUCT_BACKORDERS, true );

		if ( $backorders ) {
			return PHP_INT_MAX;
		}

		return intval( $quantity );
	}

	private static function gate( WP_Post|int $post ): WP_Post {
		if ( ! self::$instance->is_post_this_type( $post ) ) {
			throw new InvalidArgumentException();
		}

		return get_post( $post );
	}

	public static function init() {
		self::$instance ??= new self();
	}
}
