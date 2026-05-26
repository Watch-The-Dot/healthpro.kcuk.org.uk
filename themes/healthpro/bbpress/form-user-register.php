<?php

/**
 * User Registration Form
 *
 * @package bbPress
 * @subpackage Theme
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;
?>

<div class="et_pb_newsletter_form et_pb_login_form">
<form method="post" action="<?php bbp_wp_login_action( array( 'context' => 'login_post' ) ); ?>" class="bbp-login-form">
	<fieldset class="bbp-form">
		<legend><?php esc_html_e( 'Create an Account', 'bbpress' ); ?></legend>

		<?php do_action( 'bbp_template_before_register_fields' ); ?>

		<div class="bbp-template-notice">
			<ul>
				<li><?php esc_html_e( 'Your username must be unique, and cannot be changed later.', 'bbpress' ); ?></li>
			</ul>
		</div>

		<div class="bbp-username et_pb_contact_form_field">
			<label for="user_login" style="display: none;"><?php esc_html_e( 'Username', 'bbpress' ); ?>: (required) </label>
			<input type="text" name="user_login" class="input" value="<?php bbp_sanitize_val( 'user_login' ); ?>" size="20" id="user_login" maxlength="100" autocomplete="off" placeholder="<?php esc_attr_e( 'Username', 'bbpress' ); ?> (required)" />
		</div>

		<div class="bbp-email et_pb_contact_form_field">
			<label for="user_email" style="display: none;"><?php esc_html_e( 'Email', 'bbpress' ); ?>: (required) </label>
			<input type="text" name="user_email" class="input" value="<?php bbp_sanitize_val( 'user_email' ); ?>" size="20" id="user_email" maxlength="100" autocomplete="off" placeholder="<?php esc_attr_e( 'Email', 'bbpress' ); ?> (required)" />
		</div>

		<?php do_action( 'register_form' ); ?>

		<div class="bbp-submit-wrapper">

			<button type="submit" name="user-submit" class="button submit user-submit et_pb_button"><?php esc_html_e( 'Register', 'bbpress' ); ?></button>

			<?php bbp_user_register_fields(); ?>

		</div>

		<?php do_action( 'bbp_template_after_register_fields' ); ?>

	</fieldset>
</form>
</div>
