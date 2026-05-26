<?php

/**
 * Remove the Express Payment Request button from woocommerce_checkout_before_customer_details because this times EVERY TIME under EVERY Divi Element
 * The only way to get this button back is via shortcode or via a module. Module would be nice but that's a TODO.
 * The meantime though: Use [wtd_display_payment_request_button]
 */
add_action(
	'init',
	static function () {
		if ( class_exists( 'WC_Stripe_Payment_Request', false ) ) {
			$payment_request_class = \WC_Stripe_Payment_Request::instance();
			remove_action( 'woocommerce_checkout_before_customer_details', array( $payment_request_class, 'display_payment_request_button_html' ), 1 );
			remove_action( 'woocommerce_checkout_before_customer_details', array( $payment_request_class, 'display_payment_request_button_separator_html' ), 2 );
		}

		add_shortcode(
			'wtd_display_payment_request_button',
			static function () {
				if ( ! class_exists( 'WC_Stripe_Payment_Request', false ) ) {
					return '';
				}

				if ( is_checkout() && str_contains( $_SERVER['REQUEST_URI'], 'order-received' ) ) {
					return '';
				}

				$payment_request_class = \WC_Stripe_Payment_Request::instance();

				ob_start();
				$payment_request_class->display_payment_request_button_html();
				$payment_request_class->display_payment_request_button_separator_html();
				return ob_get_clean();
			}
		);
	}
);
