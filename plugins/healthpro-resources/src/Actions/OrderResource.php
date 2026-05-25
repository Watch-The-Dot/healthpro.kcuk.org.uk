<?php

namespace HealthPro\Resources\Actions;

use Exception;
use HealthPro\Resources\Plugin;
use WP_Error;
use WP_Post;

class OrderResource {
	const FIELDS = array(
		'post_id',
		'quantity',

		'first_name',
		'last_name',
		'email_address',
		'phone_number',

		'address_1',
		'address_2',
		'city',
		'county',
		'postcode',
	);

	public static function run( array $order_data ) {
		$order_data = self::sanitize( $order_data );
		$validation = self::validate( $order_data );

		if ( $validation->has_errors() ) {
			return $validation;
		}

		do_action( 'healthpro-resources/sync', $order_data['post_id'] );

		$product_id = get_post_meta( $order_data['post_id'], ImportProduct::META_LINKED_PRODUCT_ID, true );

		$user_id = get_current_user_id();
		update_user_meta( $user_id, 'shipping_phone', $order_data['phone_number'] );
		update_user_meta( $user_id, 'shipping_address_1',  $order_data['address_1'] );
		update_user_meta( $user_id, 'shipping_address_2',  $order_data['address_2'] );
		update_user_meta( $user_id, 'shipping_city',  $order_data['city'] );
		update_user_meta( $user_id, 'shipping_county',  $order_data['county'] );
		update_user_meta( $user_id, 'shipping_postcode',  $order_data['postcode'] );

		$data = array(
			'payment_method' => '',
			'set_paid'       => true,
			'billing'        => array(
				'first_name' => $order_data['first_name'],
				'last_name'  => $order_data['last_name'],
				'address_1'  => $order_data['address_1'],
				'address_2'  => $order_data['address_2'],
				'city'       => $order_data['city'],
				'state'      => $order_data['county'],
				'postcode'   => $order_data['postcode'],
				'country'    => 'GB',
				'email'      => $order_data['email_address'],
				'phone'      => $order_data['phone_number'],
			),
			'shipping'       => array(
				'first_name' => $order_data['first_name'],
				'last_name'  => $order_data['last_name'],
				'address_1'  => $order_data['address_1'],
				'address_2'  => $order_data['address_2'],
				'city'       => $order_data['city'],
				'state'      => $order_data['county'],
				'postcode'   => $order_data['postcode'],
				'country'    => 'GB',
				'email'      => $order_data['email_address'],
				'phone'      => $order_data['phone_number'],
			),
			'line_items'     => array(
				array(
					'product_id' => $product_id,
					'quantity'   => $order_data['quantity'],
					'total'      => '0',
				),
			),
			'shipping_lines' => array(),
		);

		return self::send_order( $data );
	}

	protected static function sanitize( $order_data ) {
		$order_data = array_map( 'sanitize_text_field', $order_data );

		$order_data['post_id']  = is_numeric( $order_data['post_id'] ) ? intval( $order_data['post_id'] ) : 0;
		$order_data['quantity'] = is_numeric( $order_data['quantity'] ) ? intval( $order_data['quantity'] ) : 0;

		return $order_data;
	}

	protected static function validate( array $order_data ) {
		$errors = new WP_Error();

		extract( $order_data );
		if ( empty( $post_id ) ) {
			$errors->add( 'post_id', 'Post ID is required.' );
		} elseif ( get_post_type( $post_id ) !== 'resource' ) {
			$errors->add( 'post_id', 'Invalid Post.' );
		}

		if ( ! empty( $quantity ) ) {
			$settings = Plugin::instance()->settings()->get_settings();

			$quantity_options = array_map(
				static fn ( $row ) => intval( $row['quantity'] ),
				$settings['order_form_quantity_options']
			);

			if ( ! in_array( $quantity, $quantity_options, true ) ) {
				$errors->add( 'quantity', 'Quantity is not a valid option.' );
			}
		} else {
			$errors->add( 'quantity', 'Quantity is required.' );
		}

		if ( empty( $first_name ) ) {
			$errors->add( 'first_name', 'First Name is required.' );
		}

		if ( empty( $last_name ) ) {
			$errors->add( 'last_name', 'Last Name is required.' );
		}

		if ( empty( $email_address ) ) {
			$errors->add( 'email_address', 'Email Address is required.' );
		} elseif ( ! is_email( $email_address ) ) {
			$errors->add( 'email_address', 'Email Address is not valid' );
		}

		if ( empty( $address_1 ) ) {
			$errors->add( 'address_1', 'Address Line 1 is required.' );
		}

		if ( empty( $city ) ) {
			$errors->add( 'city', 'City is required.' );
		}

		if ( empty( $county ) ) {
			$errors->add( 'county', 'County is required.' );
		}

		if ( empty( $postcode ) ) {
			$errors->add( 'postcode', 'Postcode is required.' );
		}

		return $errors;
	}

	protected static function send_order( array $order_object ) {
		$client = Plugin::instance()->get_woo_client();
		if ( is_wp_error( $client ) ) {
			return new WP_Error( 500, 'An internal error has occurred. Try Again Later.' );
		}

		try {
			$response = $client->post( 'orders', $order_object );

			$order_id = $response->id;
			$client->post(
				"orders/{$order_id}/notes",
				array(
					'note' => "Order created via HealthPro.\nUser: " . wp_get_current_user()->user_login,
				)
			);

			return true;
		} catch ( Exception $e ) {
			error_log( $e->getMessage() );
			return new WP_Error( 'order_creation', 'An error occurred creating your order.' );
		}
	}

	public static function handle_form_submission( WP_Post $queried_object, array $post_data ) {
		$post_id = sanitize_text_field( $post_data['post_id'] ?? '' );
		$post_id = is_numeric( $post_id ) ? intval( $post_id ) : 0;
		if ( $post_id !== $queried_object->ID ) {
			return new WP_Error( 400, "Post ID doesn't match queried object" );
		}

		$nonce = sanitize_text_field( $post_data['_wpnonce'] ?? '' );
		if ( ! wp_verify_nonce( $nonce, "hp-resources-order-{$post_id}" ) ) {
			return new WP_Error( 400, 'Invalid Nonce' );
		}

		$post_variables = array_reduce(
			self::FIELDS,
			static function ( $carry, $key ) use ( $post_data ) {
				$carry[ $key ] = $post_data[ $key ] ?? '';
				return $carry;
			},
			array()
		);

		return self::run( $post_variables );
	}
}
