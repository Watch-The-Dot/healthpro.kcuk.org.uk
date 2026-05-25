<?php
add_action( 'login_enqueue_scripts', 'wtd_login' );
function wtd_login() {
	?>
	<style type="text/css">
		body.login div#login h1 a {background-image: url(<?php echo WTD_WHITELABLE_LOGO; ?>);padding-bottom: 0px;}
		.login h1 a{-webkit-background-size: 300px 80px !important;background-size: 300px 80px !important;width:100%;width:300px!important;}
		.wtd-login-message {text-align: center; margin-bottom:20px !important;}
		.wtd-login-message a{text-decoration: none;}
	</style>
	<?php
}

add_filter( 'login_headerurl', 'wtd_login_logo_url' );
function wtd_login_logo_url() {
	return WTD_WHITELABLE_URL;
}

add_filter( 'login_headertext', 'wtd_login_logo_url_title' );
function wtd_login_logo_url_title() {
	return 'Website by ' . WTD_WHITELABEL_NAME;
}

$whitelabel_option = get_option( 'wtd_whitelabel' );
if ( $whitelabel_option == 0 ) {
	add_filter( 'login_message', 'wtd_login_message' );
	function wtd_login_message() {
		return '<div class="wtd-login-message"><a href="https://www.wphelpdesk.co.uk/wordpress-knowledge/" target="_blank">WordPress Knowledgebase</a> | <a href="https://www.wphelpdesk.co.uk/product/wordpress-single-issue-fix/" target="_blank">Submit a support ticket</a></div>';
	}
}
