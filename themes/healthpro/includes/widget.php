<?php
declare( strict_types=1 );

class WTDSupportWidget {
	public function __construct() {
		add_action( 'init', array( $this, 'init' ) );
		add_action( 'wp_dashboard_setup', array( $this, 'register_widgets' ) );

		if ( ! wp_next_scheduled( 'wtd-blank-cron' ) ) {
			wp_schedule_event( time(), 'weekly', 'wtd-blank-cron' );
		}
		add_action( 'wtd-blank-cron', array( $this, 'search_for_abandoned_plugins' ) );
	}

	public function init(): void {
		if ( is_admin() && isset( $_GET['wtd_abandoned_plugins'] ) ) {
			$this->search_for_abandoned_plugins();
			wp_redirect( get_admin_url() );
			exit;
		}
	}

	public function register_widgets(): void {
		wp_add_dashboard_widget( 'wtd_support_dashboard_widget', 'WordPress Support', array( $this, 'dashboard_widget' ) );
	}

	public function search_for_abandoned_plugins(): void {
		if ( ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$plugins           = get_plugins();
		$abandoned_plugins = array();
		foreach ( $plugins as $plugin_data ) {
			$plugin_slug = isset( $plugin_data['slug'] ) ? $plugin_data['slug'] : sanitize_title( $plugin_data['Name'] );

			$url = 'http://api.wordpress.org/plugins/info/1.0/' . $plugin_slug . '.json';
			$ssl = wp_http_supports( array( 'ssl' ) );

			if ( $ssl ) {
				$url = set_url_scheme( $url, 'https' );
			}

			$raw_response = wp_remote_post( $url );

			if ( is_wp_error( $raw_response ) ) {
				continue;
			}

			/** @var Requests_Utility_CaseInsensitiveDictionary */
			$headers = $raw_response['headers'];
			if ( $headers->getAll()['content-type'] !== 'application/json' ) {
				continue;
			}

			$data = json_decode( $raw_response['body'], true );

			if ( isset( $data['error'] ) ) {
				continue;
			}

			$dt = strtotime( $data['last_updated'] );

			if ( $dt >= strtotime( '-1 year' ) ) {
				continue;
			}

			$abandoned_plugins[] = array( $plugin_data, $data );
		}

		set_site_transient( 'abandoned_plugins', $abandoned_plugins );
		set_site_transient( 'abandoned_plugins__last_run', time() );
	}

	public function dashboard_widget(): string {
		global $wpdb;

		ob_start();
		$my_theme = wp_get_theme();

		$abandoned_plugins = get_site_transient( 'abandoned_plugins' );
		if ( $abandoned_plugins === false ) {
			$abandoned_plugins = array();
		}

		$last_run = get_site_transient( 'abandoned_plugins__last_run' );
		if ( $last_run === false ) {
			$last_run = 'Unknown';
		} else {
			$dt       = DateTime::createFromFormat( 'U', (string) $last_run );
			$last_run = $dt === false ? 'Unknown' : $dt->format( 'Y-m-d' );
		}
		?>
		<div style="text-align:center">
			<a href="https://www.watchthedot.com" target="_blank">
				<img src="https://www.watchthedot.com/wtd-logo.png">
				<p>This site is supported by Watch the Dot, the WordPress experts.</p>
			</a>
		</div>
		<strong>System Information</strong><br />
		<table>
			<tr><td>Site URL</td><td><?php echo get_bloginfo( 'url' ); ?></td></tr>
			<tr><td>WP Version</td><td><?php echo get_bloginfo( 'version' ); ?></td></tr>
			<tr><td>Theme</td><td><?php echo $my_theme->get( 'Name' ) . ' ' . $my_theme->get( 'Version' ); // @phpstan-ignore-line ?></td></tr> 
			<tr><td>database</td><td><?php echo $wpdb->dbname; ?></td></tr>
			<tr><td>Hostname</td><td><?php echo php_uname( 'n' ); ?></td></tr>
			<?php if ( isset( $_SERVER['SERVER_ADDR'] ) ) : ?>
			<tr><td>IP Address</td><td><?php echo strval( wp_unslash( $_SERVER['SERVER_ADDR'] ) ); ?></td></tr>
			<?php endif; ?>
			<tr><td>PHP Version</td><td><?php echo PHP_VERSION . ' @' . ( PHP_INT_SIZE * 8 ); ?>BitOS</td></tr>
			<tr><td>OS</td><td><?php echo php_uname( 's' ); ?></td></tr>
			<tr><td>OS Release</td><td><?php echo php_uname( 'r' ); ?></td></tr>
			<tr><td>OS Version</td><td><?php echo php_uname( 'v' ); ?></td></tr>
			<tr><td>Machine Type</td><td><?php echo php_uname( 'm' ); ?></td></tr>
		</table>
		<div style="text-align:center">
			<p>
				<strong>
					<?php echo count( $abandoned_plugins ); ?> plugin<?php echo count( $abandoned_plugins ) === 1 ? ' is' : 's are'; ?> abandonded. 
					<?php
					if ( count( $abandoned_plugins ) > 0 ) :
						?>
						Contact us for support.<?php endif; ?>
				</strong><br>
				<em><a href='?wtd_abandoned_plugins'>Refresh...</a> Last Checked: <?php echo $last_run; ?></em>
			</p>
			<?php if ( count( $abandoned_plugins ) > 0 ) : ?>
				<table>
					<tr>
						<th>Name</th>
						<th>Version</th>
						<th>Last Updated</th>
						<th>Tested Up to</th>
					</tr>
					<?php foreach ( $abandoned_plugins as [ $plugin, $wp ] ) : ?>
						<tr>
							<td><a href="<?php echo $plugin['PluginURI'] === '' ? 'https://en-gb.wordpress.org/plugins/' . $wp['slug'] : $plugin['PluginURI']; ?>"><?php echo $wp['name']; ?></a></td>
							<td><?php echo $plugin['Version']; ?></td>
							<?php $lf = DateTime::createFromFormat( 'U', (string) strtotime( $wp['last_updated'] ) ); ?>
							<td><?php echo $lf === false ? 'Unknown' : $lf->format( 'Y-m-d' ); ?></td>
							<td><?php echo $wp['tested']; ?></td>
						</tr>
					<?php endforeach; ?>
				</table>
			<?php endif; ?>
		</div>
		<p style="text-align:center"><strong><a href="https://www.watchthedot.co.uk/knowledgebase/9/WordPress" target="_blank">WordPress Knowledgebase</a> | <a href="https://www.watchthedot.co.uk/submitticket.php?step=2&deptid=7" target="_blank">Submit a support ticket</a></strong></p>
		<p style="text-align:center"><strong></strong></p>
		<p style="text-align:center"><small>Watch The Dot Ltd.  Tel <a href='tel:01223969426'>01223 969426</a></small></p>
		<?php
		return ob_get_flush() ?: '';
	}
}
