<?php
/**
 * Represents the view for the administration dashboard.
 *
 * This includes the header, options, and other information that should provide
 * The User Interface to the end user.
 */
?>

<?php global $wpdb; ?>

<div class="wrap">

	<h2><?php echo esc_html( get_admin_page_title() ); ?></h2>


	<form method="post" action="options.php"> 


		<?php settings_fields( 'wtd-maintenance-mode' ); ?>
		<?php do_settings_sections( 'wtd-maintenance-mode' ); ?>


		<h3>White Label</h3>
		
		<h4>Activate Whitelabel mode</h4>
		<?php
			$whitelabel_option  = get_option( 'wtd_whitelabel' );
			$whitelabel_options = array(
				0 => 'No - Brand as Watch the Dot',
				1 => 'Yes - use branding options below',
			);
			foreach ( $whitelabel_options as $key => $option ) :
				if ( $whitelabel_option == $key ) :
					$checked = ' checked';
				else :
					$checked = '';
				endif;
				echo '<input type="radio" name="wtd_whitelabel" value="' . $key . '" ' . $checked . '>' . $option . '<br>';
			endforeach;
			?>

		
		<div><label style="display:inline-block; width:10%;">Company Name</label><input name="wtd_whitelabel_company_name" value='<?php echo get_option( 'wtd_whitelabel_company_name' ); ?>' style="width:40%; min-width:400px;"></div>
		<div><label style="display:inline-block; width:10%;">Whitelabel Name</label><input name="wtd_whitelabel_name" value='<?php echo get_option( 'wtd_whitelabel_name' ); ?>' style="width:40%; min-width:400px;"></div>
		<div><label style="display:inline-block; width:10%;">Strapline</label><input name="wtd_whitelabel_strap" value='<?php echo get_option( 'wtd_whitelabel_strap' ); ?>' style="width:40%; min-width:400px;"></div>
		<div><label style="display:inline-block; width:10%;">URL</label><input name="wtd_whitelabel_url" value='<?php echo get_option( 'wtd_whitelabel_url' ); ?>' style="width:40%; min-width:400px;"></div>
		<div><label style="display:inline-block; width:10%;">Telephone</label><input name="wtd_whitelabel_tel" value='<?php echo get_option( 'wtd_whitelabel_tel' ); ?>' style="width:40%; min-width:400px;"></div>
		<div><label style="display:inline-block; width:10%;">Logo URL (332px x 94px)</label><input name="wtd_whitelabel_logo" value='<?php echo get_option( 'wtd_whitelabel_logo' ); ?>' style="width:40%; min-width:400px;"></div>

		<p>Note you can hide the settings page by adding the line <code>define('WTD_WHITELABEL','1');</code> to your wp-config.php</p>


		<h3>Demo / Debug Data</h3>
		<p>Turn this on to display demo and debug data coded into the theme</p>
		<?php
			$current_wtd_debug_option = get_option( 'wtd_debug_data' );
			$wtd_debug_option         = array(
				0 => 'No',
				1 => 'Yes',
			);
			foreach ( $wtd_debug_option as $key => $option ) :
				if ( $current_wtd_debug_option == $key ) :
					$checked = ' checked';
				else :
					$checked = '';
				endif;
				echo '<input type="radio" name="wtd_debug_data" value="' . $key . '" ' . $checked . '>' . $option . '<br>';
			endforeach;
			?>

		<?php submit_button(); ?>
				
		<h3>Plugin Report</h3>
		<table>
			<tr>
				<th>Name</th>
				<th>Version</th>
				<th>Description</th>
				<th>Active</th>
			</tr>
			<?php foreach ( get_plugins() as $plugin => $information ) : ?>
				<tr>
					<td><?php echo $information['Name']; ?></td>
					<td><?php echo $information['Version']; ?></td>
					<td><?php echo $information['Description']; ?></td>
					<td><?php echo is_plugin_active( $plugin ) ? 'Yes' : 'No'; ?></td>
				</tr>
			<?php endforeach; ?>
		</table>
	</form>
</div>
