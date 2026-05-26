<?php

if ( ! function_exists( 'wtd_create_post_type_cpt' ) ) {
	//add_action( 'init', 'wtd_create_post_type_cpt' );
	function wtd_create_post_type_cpt( $args ) {

		$post_types = array(
			array(
				'post_type' => 'example',
				'menu_icon' => 'dashicons-media-document',
				'singular'  => 'example',
				'plural'    => 'examples',
				'terms'     => array(
					'example_category' => array(
						'label'        => 'Example Category',
						'rewrite'      => 'example_category',
						'hierarchical' => 'true',
					),
					'example_tag'      => array(
						'label'        => 'Example Tag',
						'rewrite'      => 'example_tag',
						'hierarchical' => 'false',
					),
				),
			),

			array(
				'post_type' => 'example2',
				'menu_icon' => 'dashicons-media-document',
				'singular'  => 'example2',
				'plural'    => 'examples2',
				'terms'     => array(
					'example2_category' => array(
						'label'        => 'Example2 Category',
						'rewrite'      => 'example2_category',
						'hierarchical' => 'true',
					),
					'example2_tag'      => array(
						'label'        => 'Example2 Tag',
						'rewrite'      => 'example2_tag',
						'hierarchical' => 'false',
					),
				),
			),

		);
		wtd_create_post_type( $post_types );
	}
}

if ( ! function_exists( 'wtd_create_post_type' ) ) {
	function wtd_create_post_type( $post_types ) {

		foreach ( $post_types as $post_type ) :

			$labels = array(
				'name'               => __( ucfirst( $post_type['plural'] ) ),
				'singular_name'      => __( $post_type['singular'] ),
				'add_new'            => __( 'Add ' . ucfirst( $post_type['singular'] ) ),
				'all_items'          => __( 'All ' . ucfirst( $post_type['plural'] ) ),
				'add_new_item'       => __( 'Add ' . $post_type['singular'] ),
				'edit_item'          => __( 'Edit ' . $post_type['singular'] ),
				'new_item'           => __( 'New ' . $post_type['singular'] ),
				'view_item'          => __( 'View ' . $post_type['singular'] ),
				'search_items'       => __( 'Search ' . $post_type['plural'] ),
				'not_found'          => __( 'No ' . $post_type['plural'] . ' found' ),
				'not_found_in_trash' => __( 'No ' . $post_type['plural'] . ' found in trash' ),
				'parent_item_colon'  => __( 'Parent ' . $post_type['singular'] ),
			);
			$args   = array(
				'labels'               => $labels,
				'public'               => true,
				'has_archive'          => true,
				'publicly_queryable'   => true,
				'query_var'            => true,
				'rewrite'              => true,
				'capability_type'      => 'post',
				'hierarchical'         => false,
				'supports'             => array(
					'title',
					'editor',
					'excerpt',
					'thumbnail',
					'author',
					'comments',
					'revisions',
				),
				'menu_position'        => 50,
				'menu_icon'            => $post_type['menu_icon'],
				'register_meta_box_cb' => 'quote_add_post_type_metabox',
			);
			register_post_type( $post_type['post_type'], $args );

			foreach ( $post_type['terms'] as $term => $args ) :
				register_taxonomy(
					$term,
					$post_type['post_type'],
					array(
						'label'        => __( $args['label'] ),
						'rewrite'      => array( 'slug' => $args['rewrite'] ),
						'hierarchical' => $args['hierarchical'],
					)
				);
			endforeach;

		endforeach;
	}
}
