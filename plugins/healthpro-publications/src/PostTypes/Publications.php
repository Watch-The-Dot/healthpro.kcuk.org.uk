<?php

namespace HealthPro\Publications\PostTypes;

use InvalidArgumentException;
use WP_Post;
use WP_Post_Type;

class Publications {
	const POST_NAME     = 'kcuk-publication';
	const CATEGORY_NAME = 'kcuk-publication-cat';

	public static self $instance;

	private WP_Post_Type $post_type;

	private function __construct() {
		add_action( 'init', array( $this, 'register' ) );

		add_action( 'template_redirect', $this->redirect_to_canonical_url(...) );

		add_filter( 'post_thumbnail_id', array( $this, 'override_thumbnail_id_to_use_remote' ), 10, 2 );
		add_filter( 'post_thumbnail_url', array( $this, 'override_thumbnail_url_to_use_remote' ), 10, 3 );
		add_filter( 'dpdfg_thumbnail_url', array( $this, 'override_thumbnail_url_to_use_remote_divi_filtergrid' ), 10, 5 );
	}

	/**
	 * @hook init
	 */
	public function register() {
		$labels = array(
			'name'               => _x( 'Publications', 'post type general name', 'healthpro-publications' ),
			'singular_name'      => _x( 'Publication', 'post type singular name', 'healthpro-publications' ),
			'menu_name'          => _x( 'Publications', 'admin menu', 'healthpro-publications' ),
			'name_admin_bar'     => _x( 'Publication', 'add new on admin bar', 'healthpro-publications' ),
			'add_new'            => _x( 'Add New', 'publication', 'healthpro-publications' ),
			'add_new_item'       => __( 'Add New Publication', 'healthpro-publications' ),
			'new_item'           => __( 'New Publication', 'healthpro-publications' ),
			'edit_item'          => __( 'Edit Publication', 'healthpro-publications' ),
			'view_item'          => __( 'View Publication', 'healthpro-publications' ),
			'all_items'          => __( 'All Publications', 'healthpro-publications' ),
			'search_items'       => __( 'Search Publications', 'healthpro-publications' ),
			'parent_item_colon'  => __( 'Parent Publications:', 'healthpro-publications' ),
			'not_found'          => __( 'No publications found.', 'healthpro-publications' ),
			'not_found_in_trash' => __( 'No publications found in Trash.', 'healthpro-publications' ),
		);

		$args = array(
			'labels'             => $labels,
			'public'             => true,
			'publicly_queryable' => true,
			'show_ui'            => false,
			'show_in_menu'       => false,
			'query_var'          => true,
			'rewrite'            => array( 'slug' => 'publication' ),
			'capability_type'    => 'post',
			'has_archive'        => false,
			'hierarchical'       => false,
			'menu_position'      => null,
			'supports'           => array( 'title', 'thumbnail' ),
		);

		$this->post_type = register_post_type( self::POST_NAME, $args );

		$labels = array(
			'name'                       => _x( 'Publication Categories', 'Taxonomy General Name', 'healthpro-publications' ),
			'singular_name'              => _x( 'Publication Category', 'Taxonomy Singular Name', 'healthpro-publications' ),
			'menu_name'                  => __( 'Publication Category', 'healthpro-publications' ),
			'all_items'                  => __( 'All Publication Categories', 'healthpro-publications' ),
			'parent_item'                => __( 'Parent Publication Category', 'healthpro-publications' ),
			'parent_item_colon'          => __( 'Parent Publication Category:', 'healthpro-publications' ),
			'new_item_name'              => __( 'New Publication Category Name', 'healthpro-publications' ),
			'add_new_item'               => __( 'Add New Publication Category', 'healthpro-publications' ),
			'edit_item'                  => __( 'Edit Publication Category', 'healthpro-publications' ),
			'update_item'                => __( 'Update Publication Category', 'healthpro-publications' ),
			'view_item'                  => __( 'View Publication Category', 'healthpro-publications' ),
			'separate_items_with_commas' => __( 'Separate genres with commas', 'healthpro-publications' ),
			'add_or_remove_items'        => __( 'Add or remove genres', 'healthpro-publications' ),
			'choose_from_most_used'      => __( 'Choose from the most used', 'healthpro-publications' ),
			'popular_items'              => __( 'Popular Publication Categories', 'healthpro-publications' ),
			'search_items'               => __( 'Search Publication Categories', 'healthpro-publications' ),
			'not_found'                  => __( 'Not Found', 'healthpro-publications' ),
			'no_terms'                   => __( 'No genres', 'healthpro-publications' ),
			'items_list'                 => __( 'Publication Categories list', 'healthpro-publications' ),
			'items_list_navigation'      => __( 'Publication Categories list navigation', 'healthpro-publications' ),
		);

		$args = array(
			'labels'            => $labels,
			'hierarchical'      => true,
			'public'            => true,
			'show_ui'           => true,
			'show_admin_column' => true,
			'show_in_nav_menus' => true,
			'show_tagcloud'     => true,
		);

		register_taxonomy( self::CATEGORY_NAME, array( 'kcuk-publication' ), $args );
	}

	/**
	 * @hook template_redirect
	 */
	public function redirect_to_canonical_url() {
		if ( is_singular() && $this->is_post_this_type( get_the_ID() ) ) {
			$link = get_post_meta( get_the_ID(), '_kcuk_link', true );
			if ( filter_var( $link, FILTER_VALIDATE_URL ) ) {
				wp_redirect( $link, 308 );
				exit;
			}
		}

		if ( is_tax( self::CATEGORY_NAME ) ) {
			$object = get_queried_object();
			wp_redirect( "https://kcuk.org.uk/publication_category/{$object->slug}", 308 );
			exit;
		}
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

		return get_post_meta( is_object( $post ) ? $post->ID : $post, '_kcuk_image', true );
	}

	public function override_thumbnail_url_to_use_remote_divi_filtergrid( $thumbnail_url, $props, $width, $height, $post_id ) {
		return $this->override_thumbnail_url_to_use_remote( $thumbnail_url, $post_id, array( $width, $height ) );
	}

	private function is_post_this_type( WP_Post|int $post ) {
		return get_post_type( $post ) === $this->post_type->name;
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
