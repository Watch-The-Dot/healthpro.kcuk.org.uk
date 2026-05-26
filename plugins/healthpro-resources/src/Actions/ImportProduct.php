<?php

namespace HealthPro\Resources\Actions;

use InvalidArgumentException;

class ImportProduct {
	const META_LINKED_PRODUCT_ID         = '_linked_product_id';
	const META_LINKED_PRODUCT_URL        = '_linked_product_url';
	const META_LINKED_PRODUCT_STOCK      = '_linked_product_quantity';
	const META_LINKED_PRODUCT_BACKORDERS = '_linked_product_backorders';
	const META_LINKED_PRODUCT_IMAGE      = '_linked_product_image';
	const META_LINKED_PRODUCT_ONLINE     = '_linked_product_online_version';
	const META_SYNC_TIME                 = '_linked_product__synced_at';

	public static function run( $product_data ) {
		$product_id = $product_data['id'] ?? throw new InvalidArgumentException( 'No ID provided' );
		$title      = $product_data['title'] ?? throw new InvalidArgumentException( 'No title provided' );

		if ( self::check_for_duplicate( $product_id ) ) {
			throw new Exception( 'Already Imported' );
		}

		$post_id = wp_insert_post(
			array(
				'post_title'   => $title,
				'post_type'    => 'resource',
				'post_content' => '',
				'post_status'  => 'publish',
			)
		);
		if ( is_wp_error( $post_id ) ) {
			throw new Exception( $post_id->get_error_message() );
		}

		update_post_meta( $post_id, self::META_LINKED_PRODUCT_ID, $product_id );

		do_action( 'healthpro-resources/sync', $post_id );

		return $post_id;
	}

	public static function check_for_duplicate( $product_id ) {
		$posts_found = get_posts(
			array(
				'post_type'   => 'resource',
				'meta_query'  => array(
					'relation' => 'AND',
					array(
						'key'   => self::META_LINKED_PRODUCT_ID,
						'value' => $product_id,
					),
				),
				'numberposts' => 1,
				'fields'      => 'ids',
			)
		);

		return (bool) count( $posts_found );
	}
}
