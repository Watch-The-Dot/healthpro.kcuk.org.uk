<?php
/**
 * Plugin Name:       bbPress Modules for Divi
 * Plugin URI:
 * Description:       Adds Divi Modules for bbPress
 *
 * Version:           0.2.4
 * Requires at least: 6.0
 * Requires PHP:      7.4
 *
 * Author:            Dominic Carrington
 * Author URI:        https://www.watchthedot.com
 *
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 *
 * Update URI:        https://example.com/my-plugin/
 *
 * Text Domain:       divi-bbpress
 * Domain Path:       /languages
 *
 * Requires Plugins:  bbpress
 */

namespace WatchTheDot\Plugins\DivibbPress;

require_once __DIR__ . '/vendor/autoload.php';

class Plugin {
	const NAME = 'bbPress Modules for Divi';

	const VERSION = '0.2.4';

	private static self $instance;

	private static array $optional_dependencies = array();

	private ModuleRegistry $modules;

	private function __construct() {
		$this->modules = new ModuleRegistry();

		add_action( 'divi_extensions_init', array( $this, 'action_divi_extensions_init' ) );
		add_action( 'admin_init', array( $this, 'action_admin_init' ) );
		add_action( 'init', array( $this, 'init' ) );
	}

	public function init() {
	}

	public function action_admin_init() {
		if ( ! $this->is_divi_active() ) {
			add_action(
				'admin_notices',
				static function () {
					?>
					<div class="notice notice-error">
						<p>
							<?php // phpcs:ignore WordPress.WP.I18n.NonSingularStringLiteralText ?>
							<strong><?php echo esc_html( __( self::NAME, 'divi-bbpress' ) ); ?></strong> 
							requires Divi to be installed and active.
						</p>
					</div>
					<?php
				}
			);
			return;
		}

		add_action( 
			'after_plugin_row_meta', 
			array( $this, 'add_optional_plugins_to_plugin_row_meta' ), 
			10, 
			1
		);
	}

	public function action_divi_extensions_init() {
		new DiviExtension( __FILE__ );
	}

	public function add_optional_plugins_to_plugin_row_meta( $plugin_file ) {
		if ( plugin_basename( __FILE__ ) !== $plugin_file ) {
			return;
		}
		?>
		<strong><?php echo esc_html( __( 'Optional', 'divi-bbpress' ) ); ?>:</strong>
		<?php
		echo implode(
			' | ',
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			array_map(
				static fn ( $plugin_file, $name ) => sprintf(
					'<span class="dashicons %2$s"></span><%1$s>%3$s</%1$s>',
					is_plugin_active( $plugin_file ) ? 'span' : 'em',
					is_plugin_active( $plugin_file ) ? 'dashicons-yes' : 'dashicons-no',
					esc_html( $name ),
				),
				array_keys( self::$optional_dependencies ),
				array_values( self::$optional_dependencies )
			)
		)
		?>
		<?php
	}

	private function is_divi_active() {
		$template = get_option( 'template' );
		return 'Extra' === $template || 'Divi' === $template || defined( 'ET_BUILDER_PLUGIN_VERSION' );
	}

	public function get_modules() {
		return $this->modules;
	}

	public static function instance() {
		return self::$instance ??= new self();
	}
}

Plugin::instance();