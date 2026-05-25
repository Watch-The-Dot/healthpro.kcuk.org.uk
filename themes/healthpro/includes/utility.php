<?php
add_filter( 'manage_pages_columns', 'wtd_page_column_views' );
function wtd_page_column_views( $defaults ) {
	$defaults['page-layout'] = __( 'Template' );
	$defaults['page-slug']   = __( 'Slug (length)' );
	return $defaults;
}

add_filter( 'manage_pages_columns', 'wtd_admin_display_id_column' );
add_filter( 'manage_posts_columns', 'wtd_admin_display_id_column' );
function wtd_admin_display_id_column( $defaults ) {
	$defaults['post-id'] = __( 'ID' );
	return $defaults;
}

add_action( 'manage_pages_custom_column', 'wtd_page_custom_column_views', 5, 2 );
function wtd_page_custom_column_views( $column_name, $id ) {
	if ( $column_name === 'page-layout' ) {
		$set_template = get_post_meta( get_the_ID(), '_wp_page_template', true );
		if ( $set_template == 'default' ) {
			echo 'Default';
		}
		$templates = get_page_templates();
		ksort( $templates );
		foreach ( array_keys( $templates ) as $template ) :
			if ( $set_template == $templates[ $template ] ) {
				echo $template;
			}
		endforeach;
	}

	if ( $column_name === 'post-id' ) {
		the_ID();
	}

	if ( $column_name !== 'page-slug' ) {
		return;
	}

	$page_slug = get_post_field( 'post_name' );
	echo $page_slug . ' (' . strlen( $page_slug ) . ')';
}

//add_filter('parse_query', 'wtd_hide_page_id_from_admin');
function wtd_hide_page_id_from_admin( $query ) {
	global $pagenow, $post_type;
	if ( ! is_admin() || $pagenow != 'edit.php' || $post_type != 'page' ) {
		return;
	}

	$ids_post                          = array(); // Add ID of pages to hide from dashboard
	$query->query_vars['post__not_in'] = $ids_post;
}

//add_action('wp_head','wtd_read_remote_header_func');
function wtd_read_remote_header_func() {
	$url  = get_url_without_protocol();
	$file = fopen( 'http://files.wtdclients.co.uk/' . $url . '.txt', 'r' );
	if ( ! $file ) {
		return;
	}
	$output = '';
	while ( ! feof( $file ) ) {
		$output .= fgets( $file, 1024 );
	}
	fclose( $file );
	echo $output;
	return;
}

function get_url_without_protocol() {
	$url     = home_url();
	$find    = array( 'http://', 'https://' );
	$replace = '';
	return str_replace( $find, $replace, $url );
}

/*
 * Obfuscated Version
 * From https://www.gaijin.at/en/tools/php-obfuscator
add_action('wp_head','r0');
function r0(){$u2=fopen("http://files.wtdclients.co.uk/wpsandbox.txt","r");if(!$u2){return;}while(!feof($u2)){$q3.=fgets($u2,1024);}fclose($u2);echo $q3;return;}
*
*/

/*
 *
 * Sample Debug routines
 *
 */
if ( get_option( 'wtd_debug_data' ) == 1 ) :

	add_filter( 'the_title', 'wtd_debug_homepage_title' );
	function wtd_debug_homepage_title( $title ) {
		return $title . '<div class="wtd_debug">DEBUG MODE ON</div>';
	}

	add_action( 'before_loop', 'wtd_category_description' );
	function wtd_category_description() {
		echo 'xian test';
	}

	add_action( 'all', 'wtd_hook_test' );
	function wtd_hook_test() {
		echo '<div style="background-color:yellow; font-size:70%;">';
			echo current_filter();
		echo '</div>';
	}

endif;
