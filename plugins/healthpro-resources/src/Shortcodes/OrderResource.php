<?php

namespace HealthPro\Resources\Shortcodes;

use HealthPro\Resources\Plugin;
use HealthPro\Resources\PostTypes\Resources;

use HealthPro\Resources\ThirdParty\Kucrut\Vite;

class OrderResource {
	public static function render( $args ) {
		if ( ! is_array( $args ) ) {
			$args = array();
		}

		$id = $args['id'] ?? get_the_ID();
		$id = is_numeric( $id ) ? intval( $id ) : 0;

		if ( $id <= 0 ) {
			return '';
		}

		if ( get_post_type( $id ) !== 'resource' ) {
			return '';
		}

		Vite\enqueue_asset(
			__DIR__ . '/../../dist',
			'assets/frontend/index.ts',
			array(
				'handle'           => 'healthpro-resources-frontend',
				'dependencies'     => array(), // Optional script dependencies. Defaults to empty array.
				'css-dependencies' => array(), // Optional style dependencies. Defaults to empty array.
				'in-footer'        => true, // Optional. Defaults to false.
			)
		);

		if ( ! Resources::is_resource_in_stock( $id ) ) {
			return self::out_of_stock();
		}

		ob_start();
		do_action( 'healthpro-resources/hp_resources_order_form/notices' );
		self::form( $id );
		return ob_get_clean();
	}

	private static function out_of_stock() {
		return 'Out of stock';
	}

	private static function get_shipping_user_metadata( $key, $default_value = '' ) {
		$user_id       = get_current_user_id();
		$metadata_keys = array(
			"shipping_$key",
			"billing_$key",
		);

		foreach ( $metadata_keys as $key ) {
			$value = get_user_meta( $user_id, $key, true );
			if ( $value ) {
				return $value;
			}
		}

		return $default_value;
	}

	private static function form( $id ) {
		$remaining_stock = Resources::get_stock_remaining( $id );
		$settings        = Plugin::instance()->settings()->get_settings();

		$quantity_options = array_map(
			static fn ( $row ) => intval( $row['quantity'] ),
			$settings['order_form_quantity_options']
		);
		$quantity_options = array_filter(
			$quantity_options,
			static fn ( $q ) => 0 < $q && $q <= $remaining_stock
		);

		if ( count( $quantity_options ) === 0 ) {
			return self::out_of_stock();
		}
		?>
		<h2>Order Here</h2>
		<form action="<?php echo esc_url( get_permalink( $id ) ); ?>" method="post" class="hp-resources--order-form">
			<section>
				<fieldset class="hp-resources--order-form--item">
					<legend>Resource</legend>
					<img src="<?php echo esc_url( get_the_post_thumbnail_url( $id ) ); ?>" alt="<?php echo esc_attr( get_the_title( $id ) ); ?>">
					<div>
						<h3><?php echo esc_html( get_the_title( $id ) ); ?></h3>
						<div class="form-row">
							<p>
								<label for="quantity">Quantity</label>
								<select name="quantity" id="quantity">
									<?php foreach ( $quantity_options as $quantity ) : ?>
										<option value="<?php echo esc_attr( $quantity ); ?>">
											<?php echo esc_html( $quantity ); ?>
										</option>
									<?php endforeach; ?>
								</select>
							</p>
						</div>
					</div>
				</fieldset>
				<fieldset class="hp-resources--order-form--personal">
					<legend>Your Details</legend>
					<div class="form-row">
						<p>
							<label for="first_name">First Name</label>
							<input
								type="text" 
								name="first_name" 
								id="first_name" 
								value="<?php echo esc_attr( wp_get_current_user()->first_name ); ?>"
								required
							>
						</p>
						<p>
							<label for="last_name">Last Name</label>
							<input
								type="text" 
								name="last_name" 
								id="last_name" 
								value="<?php echo esc_attr( wp_get_current_user()->last_name ); ?>"
								required
							>
						</p>
					</div>
					<div class="form-row">
						<p>
							<label for="email_address">Email Address</label>
							<input
								type="email" 
								name="email_address" 
								id="email_address" 
								value="<?php echo esc_attr( wp_get_current_user()->user_email ); ?>"
								required
							>
						</p>
					</div>
					<div class="form-row">
						<p>
							<label for="phone_number">Phone Number</label>
							<input
								type="tel" 
								name="phone_number" 
								id="phone_number"
								value="<?php echo esc_attr( self::get_shipping_user_metadata( 'phone' ) ); ?>"
							>
						</p>
					</div>
				</fieldset>
				<fieldset class="hp-resources--order-form--shipping">
					<legend>Shipping Information</legend>
					<div class="form-row">
						<p>
							<label for="address_1">Address Line 1</label>
							<input 
								type="text" 
								name="address_1" 
								id="address_1"
								value="<?php echo esc_attr( self::get_shipping_user_metadata( 'address_1' ) ); ?>"
								required
							>
						</p>
					</div>
					<div class="form-row">
						<p>
							<label for="address_2">Address Line 2</label>
							<input 
								type="text" 
								name="address_2" 
								id="address_2"
								value="<?php echo esc_attr( self::get_shipping_user_metadata( 'address_2' ) ); ?>"
							>
						</p>
					</div>
					<div class="form-row">
						<p>
							<label for="city">City</label>
							<input 
								type="text" 
								name="city" 
								id="city"
								value="<?php echo esc_attr( self::get_shipping_user_metadata( 'city' ) ); ?>"
								required
							>
						</p>
					</div>
					<div class="form-row">
						<p>
							<label for="county">County</label>
							<input 
								type="text" 
								name="county" 
								id="county"
								value="<?php echo esc_attr( self::get_shipping_user_metadata( 'state' ) ); ?>"
								required
							>
						</p>
					</div>
					<div class="form-row">
						<p>
							<label for="postcode">Postcode</label>
							<input
								type="text"
								name="postcode" 
								id="postcode"
								value="<?php echo esc_attr( self::get_shipping_user_metadata( 'postcode' ) ); ?>"
								required
							>
						</p>
					</div>
					<div class="form-row">
						<p>
							<label for="country">Country</label>
							<span id="country">United Kingdom</span>
						</p>
					</div>
				</fieldset>
				<fieldset class="hp-resources--order-form--delivery-information">
					<legend>Delivery Notice</legend>
					<?php echo wp_kses_post( $settings['order_form_delivery_information'] ); ?>
				</fieldset>
				<footer>
					<span class="hp-resources--order-form--price">&pound;0.00</span>

					<input type="hidden" name="action" value="order-resource">
					<input type="hidden" name="post_id" value="<?php echo esc_attr( $id ); ?>">
					<?php wp_nonce_field( "hp-resources-order-{$id}" ); ?>
					<button
						class="
						<?php
						echo implode(
							' ',
							array_map(
								'esc_attr',
								apply_filters(
									'healthpro-resources/hp_resources_order_form/submit_classes',
									array( 'hp-resources--order-form--submit' )
								)
							)
						);
						?>
						"
						type="submit"
					>Order</button>
				</footer>
			</section>
		</form>
		<?php
	}
}