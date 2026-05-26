<?php

namespace HealthPro\Resources;

use HealthPro\Resources\ThirdParty\Kucrut\Vite;
use HealthPro\Resources\PostTypes\Resources;

class AdminScreen {
	private static self $instance;

	private function __construct() {
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
	}

	public function enqueue_scripts( string $hook_suffix ) {
		if ( 'toplevel_page_healthpro-resources' !== $hook_suffix ) {
			return;
		}

		Vite\enqueue_asset(
			__DIR__ . '/../dist',
			'assets/admin/index.tsx',
			array(
				'handle'           => 'healthpro-resources-admin',
				'dependencies'     => array( 'react', 'react-dom', 'wp-i18n', 'wp-api-fetch', 'wp-url', 'wp-components' ), // Optional script dependencies. Defaults to empty array.
				'css-dependencies' => array( 'wp-components' ), // Optional style dependencies. Defaults to empty array.
				'in-footer'        => true, // Optional. Defaults to false.
			)
		);
	}

	public function render() {
		if ( ! Plugin::instance()->are_constants_set() ) {
			return;
		}

		$resources = get_posts(
			array(
				'post_type'   => 'resource',
				'numberposts' => -1,
			)
		);

		$resources = array_map( array( Resources::class, 'convert_to_array' ), $resources );

		$js_resources = wp_json_encode( $resources );
		?>
		<div class="wrap">
			<h1>Resources</h1>

			<div id="app" data-resources="<?php echo esc_attr( $js_resources ); ?>"></div>
		</div>
		<?php
	}

	public static function instance() {
		return self::$instance ??= new self();
	}
}

if ( is_admin() ) {
	AdminScreen::instance();
}