<?php

/*
 * Admin Settings
 */

if ( is_admin() ) { // admin actions
	add_action( 'admin_menu', 'wtd_add_settings_admin_menu' );
	add_action( 'admin_init', 'wtd_register_theme_options' );

}
function wtd_add_settings_admin_menu() {
	add_options_page(
		get_bloginfo( 'name' ) . ' Settings',
		get_bloginfo( 'name' ),
		'read',
		'wtd',
		'wtd_display_plugin_admin_page'
	);
}

function wtd_display_plugin_admin_page() {
	include_once 'settings-page.php';
}

function wtd_register_theme_options() {
	register_setting( 'wtd-maintenance-mode', 'wtd_whitelabel' );
	register_setting( 'wtd-maintenance-mode', 'wtd_whitelabel_company_name' );
	register_setting( 'wtd-maintenance-mode', 'wtd_whitelabel_name' );
	register_setting( 'wtd-maintenance-mode', 'wtd_whitelabel_strap' );
	register_setting( 'wtd-maintenance-mode', 'wtd_whitelabel_url' );
	register_setting( 'wtd-maintenance-mode', 'wtd_whitelabel_tel' );
	register_setting( 'wtd-maintenance-mode', 'wtd_whitelabel_logo' );
	register_setting( 'wtd-maintenance-mode', 'wtd_debug_data' );
}
