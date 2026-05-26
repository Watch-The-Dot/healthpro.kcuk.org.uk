<?php

namespace HealthPro\Resources;

use Exception;
use HealthPro\Resources\Actions\ImportProduct;
use HealthPro\Resources\Actions\SyncProduct;
use HealthPro\Resources\PostTypes\Resources;
use WP_REST_Request;
use WP_REST_Response;

class RESTEndpoints {
	const V1_NAMESPACE = 'healthpro/resources/v1';

	public static function init() {
		register_rest_route(
			self::V1_NAMESPACE,
			'/search',
			array(
				'methods'             => 'GET',
				'callback'            => array( self::class, 'search' ),
				'permission_callback' => static fn() => current_user_can( 'manage_options' ),
			)
		);

		register_rest_route(
			self::V1_NAMESPACE,
			'/categories',
			array(
				'methods'             => 'GET',
				'callback'            => array( self::class, 'list_all_categories' ),
				'permission_callback' => static fn() => current_user_can( 'manage_options' ),
			)
		);

		register_rest_route(
			self::V1_NAMESPACE,
			'/resource',
			array(
				'methods'             => 'PUT',
				'callback'            => array( self::class, 'put_resource' ),
				'permission_callback' => static fn() => current_user_can( 'manage_options' ),
			)
		);

		register_rest_route(
			self::V1_NAMESPACE,
			'/resource/(?P<id>\d+)',
			array(
				'methods'             => 'DELETE',
				'callback'            => array( self::class, 'delete_resource' ),
				'permission_callback' => static fn() => current_user_can( 'manage_options' ),
				'args'                => array(
					'id' => array(
						'validate_callback' => static function ( $param, $request, $key ) {
							return is_numeric( $param ) && get_post_type( $param ) === 'resource';
						},
					),
				),
			)
		);

		register_rest_route(
			self::V1_NAMESPACE,
			'/resource/(?P<id>\d+)/sync',
			array(
				'methods'             => 'GET',
				'callback'            => array( self::class, 'sync_resource' ),
				'permission_callback' => static fn() => current_user_can( 'manage_options' ),
				'args'                => array(
					'id' => array(
						'validate_callback' => static function ( $param, $request, $key ) {
							return is_numeric( $param ) && get_post_type( $param ) === 'resource';
						},
					),
				),
			)
		);
	}

	public static function search( WP_REST_Request $request ) {
		$get_params = $request->get_query_params();

		$parameters             = array();
		$parameters['search']   = $get_params['search'] ?? null;
		$parameters['category'] = $get_params['category'] ?? null;
		$parameters             = array_filter( $parameters );

		/** @var stdClass[] */
		$products = Plugin::instance()->get_woo_client()?->get( 'products', $parameters ) ?? array();

		return array_map(
			static fn ( $product ) => array(
				'id'               => $product->id,
				'title'            => $product->name,
				'url'              => $product->permalink,
				'backorders'       => $product->backorders_allowed,
				'quantity'         => $product->stock_quantity,
				'image'            => $product->images[0]?->src,
				'already_imported' => ImportProduct::check_for_duplicate( $product->id ),
			),
			$products
		);
	}

	public static function list_all_categories( WP_REST_Request $request ) {
		/** @var stdClass[] */
		$categories = Plugin::instance()->get_woo_client()->get(
			'products/categories',
			array(
				'per_page' => 100,
			)
		);

		return array_map(
			static fn ( $category ) => array(
				'id'    => $category->id,
				'name'  => $category->name,
				'count' => $category->count,
			),
			$categories
		);
	}

	public static function put_resource( WP_REST_Request $request ) {
		$params = $request->get_json_params();
		if ( ! isset( $params['post'] ) ) {
			return new WP_REST_Response( array( 'error' => 'Missing post object' ), 400 );
		}

		try {
			$id = ImportProduct::run( $params['post'] );
		} catch ( Exception $e ) {
			return new WP_REST_Response( array( 'error' => $e->getMessage() ), 400 );
		}

		return new WP_REST_Response(
			array(
				'success' => true,
				'post_id' => $id,
			)
		);
	}

	public static function sync_resource( WP_REST_Request $request ) {
		$id = $request['id'];
		SyncProduct::run( $id );

		return array(
			'success' => true,
			'data'    => Resources::convert_to_array( $id ),
		);
	}

	public static function delete_resource( WP_REST_Request $request ) {
		$success = wp_delete_post( $request['id'], true );

		return array(
			'success' => is_object( $success ),
		);
	}
}
