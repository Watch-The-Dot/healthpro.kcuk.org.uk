<?php
/**
 * Plugin Name:       [HealthPro] Referencing
 * Plugin URI:
 * Description:       Adds a referencing system using shortcodes and provides quick access via a modal in the Classic Editor
 *
 * Version:           0.3.4
 * Requires at least: 6.0
 * Requires PHP:      8.1
 *
 * Author:            Watch The Dot
 * Author URI:        https://www.watchthedot.com
 *
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 *
 * Text Domain:       healthpro-referencing
 * Domain Path:       /languages
 */

declare( strict_types=1 );

namespace HealthPro\Referencing;

use HealthPro\Referencing\Reference\OnlineJournalReference;
use HealthPro\Referencing\Reference\PrintJournalReference;
use HealthPro\Referencing\Reference\Reference;
use HealthPro\Referencing\Reference\WebArticleReference;
use HealthPro\Referencing\Reference\WebReference;
use HealthPro\Referencing\Shortcode\ReferenceShortcode;
use HealthPro\Referencing\Kucrut\Vite;
use HealthPro\Referencing\Reference\BookReference;

defined( 'ABSPATH' ) || exit;

require_once __DIR__ . '/vendor/autoload.php';

/**
 * Provides a referencing system for use in the classic editor.
 *
 * There are technically two editors that form the classic editor
 * - The Visual (TinyMCE)
 * - The Text (Quicktags)
 * Both of these require slightly different implementation
 *
 * @package HealthPro\Referencing
 */
class Plugin {
	private static self $instance;

	private function __construct() {
		add_action( 'init', $this->load_i18n( ... ), 5 );
		add_action( 'init', ReferenceShortcode::register( ... ) );

		add_action( 'admin_init', $this->load_mce_plugin( ... ) );
	}

	/**
	 * Load text domain of plugin
	 *
	 * @hook init
	 */
	public function load_i18n() {
		load_plugin_textdomain(
			'healthpro-referencing',
			false,
			dirname( plugin_basename( __FILE__ ) ) . '/languages'
		);
	}

	/**
	 * Load the TinyMCE plugin
	 *
	 * @return void
	 */
	public function load_mce_plugin() {
		// If the user can't edit posts, we will return early
		if ( ! current_user_can( 'edit_posts' ) && ! current_user_can( 'edit_pages' ) ) {
			return;
		}

		// If the user has disabled rich editing, for some reason, we will return early
		if ( get_user_option( 'rich_editing' ) !== 'true' ) {
			return;
		}

		add_action( 'admin_enqueue_scripts', $this->admin_enqueue_scripts( ... ) );
		add_action( 'admin_footer-post.php', $this->add_popup_element( ... ) );
		add_action( 'admin_footer-post-new.php', $this->add_popup_element( ... ) );
		add_filter( 'mce_buttons', $this->add_mce_button( ... ) );
		add_filter( 'mce_external_plugins', $this->enqueue_mce_plugins( ... ) );
	}

	/**
	 * Enqueue Admin Scripts
	 *
	 * @param string $hook_suffix The current admin page.
	 *
	 * @hook admin_enqueue_scripts
	 */
	public function admin_enqueue_scripts( string $hook_suffix ) {
		global $action;

		// If the page is not a post edit page, return
		if ( ! in_array( $hook_suffix, array( 'post.php', 'post-new.php' ), true ) || 'edit' !== $action ) {
			return;
		}

		// Require the Vite-for-WP library
		require_once __DIR__ . '/includes/vite-for-wp.php';

		Vite\enqueue_asset(
			__DIR__ . '/dist',
			'assets/quicktags.ts',
			array(
				'handle'           => 'healthpro-referencing-quicktags',
				'dependencies'     => array( 'jquery', 'quicktags' ), // Optional script dependencies. Defaults to empty array.
				'css-dependencies' => array(), // Optional style dependencies. Defaults to empty array.
				'in-footer'        => true, // Optional. Defaults to false.
			)
		);

		Vite\enqueue_asset(
			__DIR__ . '/dist',
			'assets/popup/index.tsx',
			array(
				'handle'           => 'healthpro-referencing-popup',
				'dependencies'     => array( 'wp-element', 'wp-components' ), // Optional script dependencies. Defaults to empty array.
				'css-dependencies' => array( 'wp-components' ), // Optional style dependencies. Defaults to empty array.
				'in-footer'        => true, // Optional. Defaults to false.
			)
		);
	}

	/**
	 * Add the Button to the toolbar.
	 *
	 * @hook mce_buttons
	 */
	public function add_mce_button( $buttons ) {
		// This must be the same as the Plugin ID
		$buttons[] = 'hp-cite';

		return $buttons;
	}

	/**
	 * Enqueue the Script for the TinyMCE Integration
	 *
	 * @hook mce_external_plugins
	 *
	 * @return array
	 */
	public function enqueue_mce_plugins( array $plugins ): array {
		require_once __DIR__ . '/includes/vite-for-wp.php';

		Vite\enqueue_asset(
			__DIR__ . '/dist',
			'assets/tinymce.ts',
			array(
				'handle'           => 'healthpro-referencing-tinymce',
				'dependencies'     => array(), // Optional script dependencies. Defaults to empty array.
				'css-dependencies' => array(), // Optional style dependencies. Defaults to empty array.
				'in-footer'        => true, // Optional. Defaults to false.
			)
		);

		// This *should* be the URL of the plugin however we use Vite,
		// that returns a module instead of CommonJS therefore we can't
		// so a little bit of trickery
		$plugins['hp-cite'] = '';

		return $plugins;
	}

	/**
	 * Add the Popup element in the footer of the post and post-new pages
	 *
	 * @hook admin_footer-post.php
	 * @hook admin_footer-post-new.php
	 */
	public function add_popup_element() {
		// Convert the properties into the values expected by the JS
		// AKA just convert the properties into a associative array
		// with the name has a property in an object
		$convert_properties = static function ( $properties ) {
			return array_map(
				static fn ( $key ) => array(
					'name' => $key,
					...$properties[ $key ],
				),
				array_keys( $properties )
			);
		};

		$citations = array(
			'Book'          => array(
				'name'   => 'Book',
				'fields' => $convert_properties( BookReference::properties() ),
			),
			'Web'           => array(
				'name'   => 'Website',
				'fields' => $convert_properties( WebReference::properties() ),
			),
			'WebArticle'    => array(
				'name'   => 'Website Article',
				'fields' => $convert_properties( WebArticleReference::properties() ),
			),
			'PrintJournal'  => array(
				'name'   => 'Print Journal',
				'fields' => $convert_properties( PrintJournalReference::properties() ),
			),
			'OnlineJournal' => array(
				'name'   => 'Online Journal',
				'fields' => $convert_properties( OnlineJournalReference::properties() ),
			),
			'default'       => array(
				'name'   => 'Generic / Other',
				'fields' => $convert_properties( Reference::properties() ),
			),
		);
		?>
		<div id="hp-cite-helper-popup" data-types="<?php echo esc_attr( wp_json_encode( $citations ) ); ?>"></div>
		<?php
	}

	public static function instance() {
		return self::$instance ??= new self();
	}
}

Plugin::instance();
