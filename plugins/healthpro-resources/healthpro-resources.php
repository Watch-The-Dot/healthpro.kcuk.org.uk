<?php

namespace HealthPro\Resources;

/*
 * Plugin Name:       [HealthPro] Resources
 * Plugin URI:
 * Description:
 * Version:           0.3.2
 *
 * Requires at least: 5.2
 * Requires PHP:      8.1
 *
 * Author:            Watch The Dot
 * Author URI:        https://www.watchthedot.com/
 *
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 *
 * Text Domain:       healthpro-resources
 * Domain Path:       /languages
 */

require_once __DIR__ . '/vendor/autoload.php';

use HealthPro\Referencing\ThirdParty\WooCommerce\Client;
use HealthPro\Resources\Actions\SyncProduct;
use HealthPro\Resources\Shortcodes\OrderResource;
use HealthPro\Resources\Actions\OrderResource as OrderResourceAction;
use HealthPro\Resources\PostTypes\Resources;
use WordPressSettingsFramework;
use WP_Error;
use WP_Post;

class Plugin {
	private static self $instance;

	private WordPressSettingsFramework $wpsf;

	private function __construct() {
		PostTypes\Resources::init();

		add_action( 'init', array( $this, 'register_shortcodes' ) );
		add_action( 'template_redirect', $this->handle_order_submission( ... ) );
		add_action( 'template_redirect', $this->add_success_notice_for_order_success( ... ) );
		add_action( 'template_redirect', $this->handle_manual_sync_request( ... ) );

		add_action( 'action_scheduler_init', array( $this, 'register_cron' ) );
		add_action( 'healthpro-resources/sync_all', array( $this, 'sync_resources' ) );
		add_action( 'healthpro-resources/sync', array( SyncProduct::class, 'run' ) );

		add_action( 'rest_api_init', array( $this, 'register_rest_endpoints' ) );

		add_action( 'admin_notices', array( $this, 'add_constant_notices' ) );
		add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );

		$this->wpsf = new WordPressSettingsFramework( __DIR__ . '/settings/settings-general.php', 'hp_resources' );

		// Add admin menu
		add_action( 'admin_menu', array( $this, 'add_settings_page' ), 20 );

		add_action( 'admin_bar_menu', $this->add_sync_button_to_admin_menu( ... ), 100 );
	}

	/**
	 * @hook init
	 */
	public function register_shortcodes() {
		add_shortcode( 'hp_resources_order_form', array( OrderResource::class, 'render' ) );
	}

	/**
	 * @hook template_redirect
	 */
	public function handle_order_submission() {
		if ( ( $_SERVER['REQUEST_METHOD'] ?? '' ) !== 'POST' ) {
			return;
		}

		if ( ! is_singular( Resources::POST_TYPE ) ) {
			return;
		}

		/**
		 * @var WP_Post
		 */
		$queried_object = get_queried_object();

		$post_variables = wp_unslash( $_POST );
		$action         = sanitize_text_field( $post_variables['action'] ?? '' );
		if ( $action !== 'order-resource' ) {
			return;
		}

		$result = OrderResourceAction::handle_form_submission( $queried_object, $post_variables );

		if ( is_wp_error( $result ) ) {
			add_action(
				'healthpro-resources/hp_resources_order_form/notices',
				static function () use ( $result ) {
					?>
					<div class="message message-errors">
						<h2>
							<?php if ( count( $result->get_error_messages() ) > 1 ) : ?>
								There was an error
							<?php else : ?>
								There were some errors
							<?php endif; ?>
						</h2>
						<ul>
							<?php foreach ( $result->get_error_messages() as $message ) : ?>
								<li><?php echo esc_html( $message ); ?></li>
							<?php endforeach; ?>
						</ul>
					</div>
					<?php
				}
			);

			return;
		}

		$link = get_permalink();
		$link = add_query_arg( 'order', 'success', $link );
		wp_safe_redirect( $link );
		exit;
	}

	public function register_cron() {
		if ( as_has_scheduled_action( 'healthpro-resources/sync_all' ) ) {
			return;
		}

		as_schedule_recurring_action( time(), DAY_IN_SECONDS, 'healthpro-resources/sync_all' );
	}

	public function sync_resources() {
		$resources = get_posts(
			array(
				'post_type'   => 'resource',
				'fields'      => 'ids',
				'numberposts' => -1,
			)
		);

		foreach ( $resources as $resource ) {
			as_enqueue_async_action( 'healthpro-resources/sync', array( $resource ) );
		}
	}

	/**
	 * @hook rest_api_init
	 */
	public function register_rest_endpoints() {
		RESTEndpoints::init();
	}

	/**
	 * @hook admin_menu
	 */
	public function add_settings_page() {
		$this->wpsf->add_settings_page(
			array(
				'parent_slug' => 'healthpro-resources',
				'page_title'  => __( 'Resources Settings', 'healthpro-resources' ),
				'menu_title'  => __( 'Settings', 'healthpro-resources' ),
				'capability'  => 'manage_options',
			)
		);
	}

	/**
	 * @hook template_redirect
	 */
	public function add_success_notice_for_order_success() {
		if ( ! is_singular( Resources::POST_TYPE ) ) {
			return;
		}

		if ( ( $_GET['order'] ?? '' ) !== 'success' ) {
			return;
		}

		add_action(
			'healthpro-resources/hp_resources_order_form/notices',
			static function () {
				?>
				<div class="message message-success">
					<h2>Order Successful</h2>
					<p>Check your email for confirmation</p>
				</div>
				<?php
			}
		);
	}

	/**
	 * @hook admin_bar_menu
	 */
	public function add_sync_button_to_admin_menu( \WP_Admin_Bar $admin_bar ) {
		if ( is_admin() ) {
			return;
		}

		if ( ! is_singular( Resources::POST_TYPE ) ) {
			return;
		}

		$link = get_permalink();
		$link = wp_nonce_url( $link, 'hp_sync_resource', '_sync' );

		$admin_bar->add_menu(
			array(
				'id'    => 'healthpro-resources',
				'title' => 'Sync Resource',
				'href'  => $link,
			)
		);
	}

	/**
	 * @hook template_redirect
	 */
	public function handle_manual_sync_request() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		if ( ! is_singular( Resources::POST_TYPE ) ) {
			return;
		}

		if ( ! isset( $_GET['_sync'] ) ) {
			return;
		}

		if ( ! wp_verify_nonce( $_GET['_sync'], 'hp_sync_resource' ) ) {
			return;
		}

		do_action( 'healthpro-resources/sync', get_the_ID() );

		wp_safe_redirect( get_permalink() );
		exit;
	}

	public function are_constants_set() {
		return defined( 'KCUK_WOOCOMMERCE_API_KEY' ) && defined( 'KCUK_WOOCOMMERCE_API_SECRET' );
	}

	/**
	 * Add admin notice for administrators if the API key constant is not defined
	 *
	 * @hook admin_notices
	 */
	public function add_constant_notices() {
		if ( $this->are_constants_set() ) {
			return;
		}
		?>
		<div class="notice notice-warning">
			<p><strong>[HealthPro] Resources</strong> requires the constant <code>KCUK_WOOCOMMERCE_API_KEY</code> and <code>KCUK_WOOCOMMERCE_API_SECRET</code> to be defined in wp-config.php</p>
		</div>
		<?php
	}

	public function add_admin_menu() {
		add_menu_page(
			__( 'Resources', 'healthpro-resources' ),
			__( 'Resources', 'healthpro-resources' ),
			'manage_options',
			'healthpro-resources',
			array( AdminScreen::instance(), 'render' ),
			'dashicons-media-document',
			25
		);
	}

	private Client $woocommerce_client;
	public function get_woo_client(): Client|WP_Error {
		if ( ! $this->are_constants_set() ) {
			return null;
		}

		try {
			return $this->woocommerce_client ??= new Client(
				'https://www.kcuk.org.uk/',
				KCUK_WOOCOMMERCE_API_KEY,
				KCUK_WOOCOMMERCE_API_SECRET,
				array()
			);
		} catch ( Exception $e ) {
			return new WP_Error( '', $e->getMessage() );
		}
	}

	public function settings() {
		return $this->wpsf;
	}

	public static function instance() {
		return self::$instance ??= new self();
	}
}

Plugin::instance();