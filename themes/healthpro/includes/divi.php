<?php

add_action(
	'wp_loaded',
	static function () {
		// We should use Relevanssi Support now it is baked in
		remove_action( 'pre_get_posts', 'et_pb_custom_search' );
	}
);

add_filter(
	'dpdfg_custom_loader',
	static function () {
		ob_start();
		?>
	<div class="dp-dfg-loader"><div class="my_custom_loader"></div></div>
	<style>
		.dp-dfg-loader {
			position: absolute;
			top: 50%;
			left: 50%;
			margin-top: -30px;
			margin-left: -30px;
		}
		
		.my_custom_loader {
			border: 8px solid #f3f3f3;
			border-top: 8px solid #2a3990;
			border-bottom: 8px solid #2a3990;
			border-radius: 50%;
			width: 60px;
			height: 60px;
			animation: spin 2s linear infinite;
			margin: 0 auto;
		}
		
		@keyframes spin {
			0% { transform: rotate(0deg); }
			100% { transform: rotate(360deg); }
		}
	</style>
		<?php
		return ob_get_clean();
	}
);

/**
 * Hide Divi Builder "project" type
 */
add_filter( 'et_project_posttype_args', 'wtd_et_project_posttype_args', 10, 1 );
function wtd_et_project_posttype_args( $args ) {
	return array_merge(
		$args,
		array(
			'public'              => false,
			'exclude_from_search' => false,
			'publicly_queryable'  => false,
			'show_in_nav_menus'   => false,
			'show_ui'             => false,
		)
	);
}

/**
 * Adjust Divi Builder supported types
 */
add_filter( 'et_builder_post_types', 'wtd_et_builder_post_types', 10, 1 );
function wtd_et_builder_post_types( $types_array ) {
	if ( ! in_array( 'some_custom_type', $types_array ) ) {
		$types_array [] = 'some_custom_type';
	}
	return $types_array;
}

// Add Custom Filtering to Divi Filter Grid
add_filter(
	'dpdfg_ext_get_fields',
	static function ( $fields ) {
		$fields['wtd_enable_filter_settings'] = array(
			'label'           => __( '[WTD] Enable Custom Filter Settings', 'watchthedot' ),
			'type'            => 'yes_no_button',
			'option_category' => 'configuration',
			'options'         => array(
				'off' => __( 'No', 'dpdfg-dp-divi-filtergrid' ),
				'on'  => __( 'Yes', 'dpdfg-dp-divi-filtergrid' ),
			),
			'default'         => 'off',
			'show_if'         => array(
				'show_filters'             => 'on',
				'use_custom_terms_filters' => 'on',
			),
			'description'     => __( '', 'watchthedot' ),
			'tab_slug'        => 'general',
			'toggle_slug'     => 'filter_options',
		);

		$fields['wtd_taxonomies'] = array(
			'label'           => __( '[WTD] Filter Taxonomies', 'watchthedot' ),
			'type'            => 'text',
			'option_category' => 'configuration',
			'default'         => 'category',
			'show_if'         => array(
				'wtd_enable_filter_settings' => 'on',
			),
			'show_if_not'     => array( 'custom_query' => 'basic' ),
			'description'     => __( 'Enter the slugs of the taxonomy seperated by commas (,)', 'dpdfg-dp-divi-filtergrid' ),
			'tab_slug'        => 'general',
			'toggle_slug'     => 'filter_options',
		);

		$fields['wtd_sort_terms_by'] = array(
			'label'           => __( '[WTD] Sort Terms By', 'watchthedot' ),
			'type'            => 'select',
			'option_category' => 'configuration',
			'options'         => array(
				'term_order' => __( 'Term Order', 'watchthedot' ),
				'term_id'    => __( 'Term ID', 'watchthedot' ),
				'name'       => __( 'Term Name', 'watchthedot' ),
				'count'      => __( 'Post Count', 'watchthedot' ),
			),
			'default'         => 'term_order',
			'show_if'         => array(
				'wtd_enable_filter_settings' => 'on',
			),
			'description'     => __( '', 'dpdfg-dp-divi-filtergrid' ),
			'tab_slug'        => 'general',
			'toggle_slug'     => 'filter_options',
		);

		$fields['wtd_respect_queried_object'] = array(
			'label'           => __( '[WTD] Respect Global Query', 'watchthedot' ),
			'type'            => 'yes_no_button',
			'option_category' => 'configuration',
			'options'         => array(
				'off' => __( 'No', 'dpdfg-dp-divi-filtergrid' ),
				'on'  => __( 'Yes', 'dpdfg-dp-divi-filtergrid' ),
			),
			'default'         => 'off',
			'show_if'         => array(
				'wtd_enable_filter_settings' => 'on',
			),
			'description'     => __( 'Respect the global query. This is brilliant for only getting the tags used within a category (Or vice versa)', 'watchthedot' ),
			'tab_slug'        => 'general',
			'toggle_slug'     => 'filter_options',
		);

		return $fields;
	},
	10,
	1
);

add_filter(
	'dpdfg_custom_filters',
	static function ( $filters, $props ) {
		if ( $props['wtd_enable_filter_settings'] !== 'on' ) {
			return $filters;
		}

		$taxonomies_to_include = explode( ',', $props['wtd_taxonomies'] );

		$post_type = '';
		switch ( $props['custom_query'] ) {
			case 'archive':
				$post_type = get_post_type();
				break;
			case 'advanced':
				$post_type = Dp_Dfg_Utils::process_comma_separate_list( $props['multiple_cpt'] );
				break;
			case 'basic':
				$post_type = 'post';
				break;
		}

		if ( empty( $post_type ) ) {
			return $filters;
		}

		$tax_query = array(
			'relation' => 'AND',
		);

		if ( $props['custom_query'] === 'archive' && $props['wtd_respect_queried_object'] === 'on' ) {
			$queried_object = get_queried_object();

			$tax_query[] = array(
				'taxonomy' => $queried_object->taxonomy,
				'terms'    => array( $queried_object->term_id ),
				'field'    => 'id',
				'operator' => 'IN',
			);
		}

		$posts = get_posts(
			array(
				'post_type'   => $post_type,
				'post_status' => 'publish',
				'numberposts' => -1,
				'tax_query'   => $tax_query,
				'fields'      => 'ids',
			)
		);

		$filters = array();
		foreach ( $taxonomies_to_include as $tax ) {
			$terms        = array_map(
				static fn ( $post_id ) => wp_get_object_terms( $post_id, $tax, array( 'fields' => 'ids' ) ),
				$posts
			);
			$unique_terms = array_map(
				static fn ( $tid ) => get_term( $tid, $tax ),
				array_unique( array_merge( ...$terms ), SORT_NUMERIC )
			);
			usort(
				$unique_terms,
				static function ( $a, $b ) use ( $props ) {
					$sort_by_field = $props['wtd_sort_terms_by'];
					switch ( $sort_by_field ) {
						// These are all integers
						case 'term_order':
						case 'term_id':
						case 'count':
							return $a->{$sort_by_field} <=> $b->{$sort_by_field};

						// And these are all strings
						case 'name':
							return strcmp( $a->{$sort_by_field}, $b->{$sort_by_field} );
					}
				}
			);

			$all_name = 'All';
			if ( count( $taxonomies_to_include ) > 2 ) {
				$all_name .= ' ' . get_taxonomy( $tax )->name;
			}

			$filters[ $tax ] = array( // Group
				array(
					'id'   => 'all',
					'name' => $all_name,
				),
				...array_map(
					static fn ( $term ) => array(
						'id'     => $term->term_id,
						'name'   => $term->name,
						'slug'   => $term->slug,
						'parent' => $term->parent,
					),
					$unique_terms
				),
			);
		}

		return $filters;
	},
	10,
	2
);


add_action(
	'dpdfg_ext_get_posts_data_in_loop',
	static function ( &$post, $props, $posts_data ) {
		if ( $props['wtd_enable_filter_settings'] !== 'on' ) {
			return;
		}

		$taxonomies_to_include = explode( ',', $props['wtd_taxonomies'] );

		! isset( $post['filter_terms'] ) ? $post['filter_terms'] = array() : null;
		foreach ( $taxonomies_to_include as $tax ) {
			$the_terms = get_the_terms( $post['id'], $tax );
			if ( is_wp_error( $the_terms ) || ! $the_terms ) {
				continue;
			}

			$post['filter_terms'] = array_merge( $post['filter_terms'], $the_terms );
		}

		foreach ( $post['filter_terms'] as $term ) {
			$post['terms_classes'] .= 'dp-dfg-term-id-' . $term->term_id . ' ';
		}
	},
	10,
	3
);
