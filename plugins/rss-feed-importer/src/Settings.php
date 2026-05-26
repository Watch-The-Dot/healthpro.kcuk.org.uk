<?php

namespace WatchTheDot\Plugins\RSSImporter;

use WordPressSettingsFramework;
use WatchTheDot\Plugins\RSSImporter\ThirdParty\Vite;

defined( 'ABSPATH' ) || exit;


if ( ! class_exists( '\\WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

class Settings {
	private static self $instance;

	public readonly WordPressSettingsFramework $wpsf;

	private function __construct() {
		$this->wpsf = new WordPressSettingsFramework(__DIR__ . "/../settings/settings-general.php", 'rss-feed-importer');
		$this->wpsf->settings_page['capability'] = 'manage_options';

		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
		add_action( 'admin_menu', array( $this, 'admin_menu' ) );

		add_action( 'load-posts_page_rss-feed-importer', array( $this, 'screen_options' ) );
		add_filter('set-screen-option', function($status, $option, $value) {
			if ('items_per_page' === $option) {
				return $value;
			}
			return $status;
		}, 10, 3);
	}

	public function screen_options() {
		$screen = get_current_screen();
		if ( ! is_object( $screen ) || $screen->id !== 'posts_page_rss-feed-importer' ) {
			return;
		}

		[$_, $subpage] = $this->get_subpage();
		if ( ! ( $subpage['options'] ?? false ) ) {
			return;
		}

		$args = [
			'label'   => __('Items per page', 'rss-feed-importer'),
			'default' => 20,
			'option'  => 'items_per_page'
		];
		add_screen_option('per_page', $args);
	}

	public function admin_enqueue_scripts( string $prefix ) {
		if ( 'posts_page_rss-feed-importer' !== $prefix ) {
			return;
		}

		require_once __DIR__ . "/../includes/vite-for-wp.php";

		if ( $this->get_subpage()[0] === "feeds" ) {
			Vite\enqueue_asset(
				__DIR__ . "/../dist",
				"assets/admin/feeds/index.tsx",
				[
					'handle' => 'rss-feed-importer-admin-feeds',
					'dependencies' => [ 'wp-element', 'wp-components' ], // Optional script dependencies. Defaults to empty array.
					'css-dependencies' => [ 'wp-components' ],
					'in-footer' => true, // Optional. Defaults to false.
				]
			);
		} else if ( $this->get_subpage()[0] === "list" ) {
			// Vite\enqueue_asset(
			// 	__DIR__ . "/../dist",
			// 	"assets/admin/posts/index.tsx",
			// 	[
			// 		'handle' => 'rss-feed-importer-admin-posts',
			// 		'dependencies' => [ 'wp-element', 'wp-components' ], // Optional script dependencies. Defaults to empty array.
			// 		'css-dependencies' => [ 'wp-components' ],
			// 		'in-footer' => true, // Optional. Defaults to false.
			// 	]
			// );
		}
	}

	public function admin_menu() {
		add_posts_page(
			__( 'RSS Feed Import', 'rss-feed-importer' ),
			__( 'RSS Feed Import', 'rss-feed-importer' ),
			'publish_posts',
			'rss-feed-importer',
			array( $this, 'render_admin_page' )
		);
	}

	public function render_admin_page() {
		[$subpage_key, $subpage] = $this->get_subpage();
		$base_url = admin_url( 'edit.php' );
		$base_url = add_query_arg( 'page', $GLOBALS['plugin_page'], $base_url );
		?>
		<div class="wrap">
			<h2 class="nav-tab-wrapper">
				<?php foreach ( $this->get_page_tabs() as $key => $data ) : ?>
					<a
						href='<?php echo esc_url( add_query_arg( 'subpage', $key, $base_url ) ); ?>' 
						class='nav-tab<?php echo $subpage_key === $key ? ' nav-tab-active' : ''; ?>'
					>
						<?php echo esc_html( $data['label'] ); ?>
					</a>
				<?php endforeach; ?>
			</h2>
			<?php call_user_func( $subpage['callable'] ); ?>
		</div>
		<?php
	}

	public function render_list_page() {
		include __DIR__ . '/../template/admin/posts.php';
	}

	public function render_feeds_page() {
		include __DIR__ . '/../template/admin/feeds.php';
	}

	public function render_errors_page() {
		include __DIR__ . '/../template/admin/errors.php';
	}

	public function render_changelog_page() {
		?>
		<div class="wrap">
			<?php include __DIR__ . '/../changelog.html'; ?>
		</div>
		<?php
	}

	private function get_subpage() {
		$subpage = sanitize_key( $_POST['subpage'] ?? $_GET['subpage'] ?? 'list' );
		$tabs = $this->get_page_tabs();
		if ( ! isset( $tabs[ $subpage ] ) ) {
			wp_redirect( remove_query_arg( 'subpage' ) );
			die();
		}

		return [ $subpage, $tabs[ $subpage ] ];
	}

	private function get_page_tabs() {
		return array(
			'list'   => array(
				'label'    => __( 'Import', 'rss-feed-importer' ),
				'callable' => array( $this, 'render_list_page' ),
				'options'  => true,
			),
			'feeds'  => array(
				'label'    => __( 'Feeds', 'rss-feed-importer' ),
				'callable' => array( $this, 'render_feeds_page' ),
				'options'  => false,
			),
			'errors' => array(
				'label'    => __( 'Sync errors', 'rss-feed-importer' ),
				'callable' => array( $this, 'render_errors_page' ),
				'options'  => false,
			),
			'settings' => array(
				'label' => __( 'Settings', 'rss-feed-importer' ),
				'callable' => array( $this->wpsf, 'settings_page_content' ),
				'options' => false,
			),
			'changelog' => array(
				'label' => __( 'Changelog', 'rss-feed-importer' ),
				'callable' => $this->render_changelog_page(...),
				'options' => false,
			)
		);
	}

	public static function instance() {
		return self::$instance ??= new self();
	}
}