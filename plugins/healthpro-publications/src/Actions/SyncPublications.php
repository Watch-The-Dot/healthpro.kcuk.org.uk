<?php

namespace HealthPro\Publications\Actions;

use Generator;
use HealthPro\Publications\PostTypes\Publications;

class SyncPublications {
	const API_BASE_URL = 'https://www.kcuk.org.uk/wp-json/wp/v2';

	public static function run() {
		$categories = self::get_categories();

		$seen      = array();
		$api_posts = self::run_get_request( self::API_BASE_URL . '/publication' );
		foreach ( $api_posts as $post ) {
			$modified    = $post->modified;
			$title       = $post->title->rendered;
			$link        = $post->link;
			$featured_id = $post->featured_media;
			$cats        = $post->publication_category ?? array();
			sort($cats);

			$posts = get_posts(
				array(
					'post_type'   => Publications::POST_NAME,
					'numberposts' => -1,
					'fields'      => 'ids',
					'meta_query'  => array(
						'relation' => 'AND',
						array(
							'key'   => '_kcuk_id',
							'value' => $post->id,
						),
					),
				)
			);

			$details = array(
				'title'          => $title,
				'link'           => $link,
				'featured_image' => $featured_id,
				'categories' => implode( "|", $cats ),
			);

			if ( count( $posts ) ) {
				$seen[] = current( $posts );
				$meta   = get_post_meta( current( $posts ), '_kcuk_details', true );
				
				if ( 
					empty( array_diff( array_keys( $details ), array_keys( $meta ) ) ) && 
					empty( array_diff( $meta, $details ) ) 
				) {
					continue; 
				}
			}

			$args = array(
				'post_type'      => Publications::POST_NAME,
				'post_status'    => 'publish',
				'comment_status' => 'closed',
				'ping_status'    => 'closed',
				'post_date_gmt'  => $modified,
				'post_title'     => $title,
				'tax_input'      => array(
					Publications::CATEGORY_NAME => array_map(
						static fn ( $category ) => intval( $categories[ $category ]['term_id'] ),
						array_filter(
							$cats,
							static fn ( $category ) => isset( $categories[ $category ] ),
						)
					),
				),
				'meta_input'     => array(
					'_kcuk_id'      => $post->id,
					'_kcuk_link' 	=> $link,
					'_kcuk_details' => $details,
				),
			);
			if ( count( $posts ) ) {
				$args['ID'] = current( $posts );
			}

			$featured_media = $post->{'_links'}?->{'wp:featuredmedia'} ?? array();
			if ( count( $featured_media ) ) {
				$featured_image_endpoint = $featured_media[0]?->href;
				if ( $featured_image_endpoint ) {
					$featured_image_response = wp_remote_get( $featured_image_endpoint );
					if ( wp_remote_retrieve_response_code( $featured_image_response ) === 200 ) {
						$featured_image = json_decode( wp_remote_retrieve_body( $featured_image_response ) );

						$args['meta_input']['_kcuk_image'] = $featured_image->source_url;
					}
				}
			}

			$seen[] = wp_insert_post( $args );
		}
	}

	private static function get_categories() {
		$categories     = array();
		$api_categories = self::run_get_request( self::API_BASE_URL . '/publication_category' );
		foreach ( $api_categories as $category ) {
			$term = term_exists( $category->slug, Publications::CATEGORY_NAME );
			if ( $term === 0 || $term === null ) {
				$term = wp_insert_term(
					$category->name,
					Publications::CATEGORY_NAME,
					array( 'slug' => $category->slug ),
				);
			}

			$categories[ $category->id ] = $term;
		}

		return $categories;
	}
	
	/**
	 * @return array|Generator
	 */
	private static function run_get_request( string $url ) {
		$page = 1;

		while ( true ) {
			$paged_url = add_query_arg( 'page', $page, $url );
			$response  = wp_remote_get( $paged_url );

			if ( wp_remote_retrieve_response_code( $response ) !== 200 ) {
				return array();
			}

			$response_body = wp_remote_retrieve_body( $response );
			$json          = json_decode( $response_body );
			if ( empty( $json ) ) {
				return array();
			}

			foreach ( $json as $obj ) {
				yield $obj;
			}
			++$page;
		}
	}
}
