<?php
if ( ! defined( 'WTD_SETTINGS_HIDE' ) ) {
	define( 'WTD_SETTINGS_HIDE', '0' );
}

require 'includes/cpt.php';
require 'includes/dashboard.php';
require 'includes/divi.php';
require 'includes/filters.php';
// include( 'includes/login.php' );
require 'includes/shortcodes.php';
require 'includes/utility.php';
require 'includes/woocommerce.php';
require 'includes/widget.php';

if ( WTD_SETTINGS_HIDE != '1' ) {
	include 'includes/settings.php';
}

$whitelabel_option = get_option( 'wtd_whitelabel' );
if ( $whitelabel_option == 1 ) {
	define( 'WTD_WHITELABLE_COMPANY', get_option( 'wtd_whitelabel_company_name' ) );
	define( 'WTD_WHITELABEL_NAME', get_option( 'wtd_whitelabel_name' ) );
	define( 'WTD_WHITELABLE_STRAP', get_option( 'wtd_whitelabel_strap' ) );
	define( 'WTD_WHITELABLE_URL', get_option( 'wtd_whitelabel_url' ) );
	define( 'WTD_WHITELABLE_TEL', get_option( 'wtd_whitelabel_tel' ) );
	define( 'WTD_WHITELABLE_LOGO', get_option( 'wtd_whitelabel_logo' ) );
} else {
	define( 'WTD_WHITELABLE_COMPANY', 'Watch the Dot Ltd' );
	define( 'WTD_WHITELABEL_NAME', 'Watch the Dot' );
	define( 'WTD_WHITELABLE_STRAP', 'WordPress Experts' );
	define( 'WTD_WHITELABLE_URL', 'https://www.watchthedot.com' );
	define( 'WTD_WHITELABLE_TEL', '01223 969426' );
	define( 'WTD_WHITELABLE_LOGO', 'https://www.watchthedot.com/wtd-logo.png' );
}

add_action( 'wp_enqueue_scripts', 'theme_enqueue_styles' );
function theme_enqueue_styles() {
	wp_enqueue_style( 'parent-style', get_template_directory_uri() . '/style.css' );
}

add_action( 'wp_enqueue_scripts', 'wtd_scripts' );
function wtd_scripts() {
	wp_enqueue_style( 'wtd-style', get_stylesheet_uri() );
	wp_enqueue_script( 'wtd-js', get_stylesheet_directory_uri() . '/js/wtd.js', array(), '1.0.0', true );
}

// Include FontAwesome fonts on pages when WooCommerce is activated.
if ( function_exists( 'WC' ) ) {
	add_filter(
		'et_global_assets_list',
		static function ( $asset_list, $assets_args, $et_dynamic_assets ) {
			if ( ! isset( $asset_list['et_icons_fa'] ) ) {
				$assets_prefix = et_get_dynamic_assets_path();

				$asset_list['et_icons_fa'] = array(
					'css' => "{$assets_prefix}/css/icons_fa_all.css",
				);
			}

			return $asset_list;
		},
		10,
		3
	);
}

add_filter(
	'auto_core_update_send_email',
	static function ( $send, $type, $core_update, $result ) {
		if ( ! empty( $type ) && $type == 'success' ) {
			return false;
		}

		return true;
	},
	10,
	4
);

add_filter( 'auto_plugin_update_send_email', '__return_false' );
add_filter( 'auto_theme_update_send_email', '__return_false' );

new WTDSupportWidget();


add_action( 'wp_enqueue_scripts', function () {
	if ( ! is_singular() ) {
		return;
	}

	if ( get_post_type() !== 'sfwd-quiz' ) {
		return;
	}

	wp_enqueue_script( 'hp-child-learndash-quiz', get_stylesheet_directory_uri() . '/js/ld-quiz.js', ['jquery'] );
} );