<?php

namespace HealthPro\Resources\Actions;

use HealthPro\Resources\Plugin;

class SyncProduct {
	public static function run( $post_id ) {
		if ( get_post_type( $post_id ) !== 'resource' ) {
			return;
		}

		$product_id = get_post_meta( $post_id, ImportProduct::META_LINKED_PRODUCT_ID, true );
		if ( ! $product_id ) {
			return;
		}

		$client  = Plugin::instance()->get_woo_client();
		$product = $client?->get( "products/{$product_id}" );
		if ( ! $product ) {
			return;
		}

		wp_update_post(
			array(
				'ID'           => $post_id,
				'post_title'   => $product->name,
				'post_content' => $product->description,
			)
		);
		update_post_meta( $post_id, ImportProduct::META_SYNC_TIME, time() );
		update_post_meta( $post_id, ImportProduct::META_LINKED_PRODUCT_BACKORDERS, $product->backorders_allowed );
		update_post_meta( $post_id, ImportProduct::META_LINKED_PRODUCT_STOCK, $product->stock_quantity );
		update_post_meta( $post_id, ImportProduct::META_LINKED_PRODUCT_URL, $product->permalink );

		$src = $product->images[0]?->src ?? false;
		if ( $src ) {
			update_post_meta( $post_id, ImportProduct::META_LINKED_PRODUCT_IMAGE, $src );
		}

		$wp_response = wp_remote_get("https://www.kcuk.org.uk/wp-json/wp/v2/product/{$product_id}");
		if ( ! is_wp_error( $wp_response ) ) {
			$resp = json_decode( wp_remote_retrieve_body( $wp_response ) );
			$online_version = $resp?->acf?->online_version ?? null;
			if ( is_array( $online_version ) ) {
				update_post_meta( $post_id, ImportProduct::META_LINKED_PRODUCT_ONLINE, $online_version[0] );
			}
		}
	}
}
