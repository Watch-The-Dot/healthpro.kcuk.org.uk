<?php
/**
 * Plugin Name:       RSS Feed Importer
 * Plugin URI:
 * Description:       Allows importing of news articles from RSS feeds
 *
 * Version:           0.8.4
 * Requires at least: 6.0
 * Requires PHP:      8.1
 *
 * Author:            Dominic Carrington
 * Author URI:        https://www.watchthedot.com
 *
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 *
 * Text Domain:       rss-feed-importer
 * Domain Path:       /languages
 */

namespace WatchTheDot\Plugins\RSSImporter;

use WatchTheDot\Plugins\RSSImporter\Actions\CleanupPosts;
use WatchTheDot\Plugins\RSSImporter\Actions\SyncFeed;
use WatchTheDot\Plugins\RSSImporter\Model\Feed;
use WP_Post;

require_once __DIR__ . '/vendor/autoload.php';

class Plugin {
	const NAME = 'RSS Feed Importer';

	const VERSION = '0.8.4';

	private static self $instance;

	private Settings $settings;

	private function __construct() {
		$this->settings = Settings::instance();

		register_activation_hook( __FILE__, array( Database::class, 'install' ) );
		register_activation_hook( __FILE__, array( self::class, 'activate' ) );
		add_action( 'plugins_loaded', array( Database::class, 'maybe_upgrade' ) );

		add_action( 'action_scheduler_init', array( $this, 'action_scheduler_init' ) );
		add_action( 'rss-feed-importer/sync_feeds', array( $this, 'sync_feeds' ) );
		add_action( 'rss-feed-importer/sync_feed', array( SyncFeed::class, 'run' ) );
		add_action( 'rss-feed-importer/clean_up', array( CleanupPosts::class, 'run' ) );

		add_action( 'init', array( $this, 'register_post_status' ) );
		add_action( 'add_meta_boxes', array( $this, 'register_meta_box' ) );
		add_filter( 'the_author', array( $this, 'update_post_author_for_imported_posts' ) );
		add_filter( 'get_the_author_link', array( $this, 'update_author_link_for_imported_ports' ), 10, 3 );
	}

	public function action_scheduler_init() {
		if ( ! as_has_scheduled_action( 'rss-feed-importer/sync_feeds' ) ) {
			as_schedule_recurring_action( time(), HOUR_IN_SECONDS, 'rss-feed-importer/sync_feeds' );
		}

		if ( ! as_has_scheduled_action( 'rss-feed-importer/clean_up' ) ) {
			as_schedule_recurring_action( time(), WEEK_IN_SECONDS, 'rss-feed-importer/clean_up' );
		}
	}

	public function register_post_status() {
		register_post_status(
			'imported',
			array(
				'label'       => _x( 'Imported', 'post status', 'rss-feed-importer' ),
				'public'      => true,
				/* translators: %s: Number of imported posts. */
				'label_count' => _n_noop(
					'Imported <span class="count">(%s)</span>',
					'Imported <span class="count">(%s)</span>'
				),
			)
		);

		add_action( 'admin_footer-post.php', array( $this, 'add_post_status_to_dropdown' ) );
		add_action( 'admin_footer-post-new.php', array( $this, 'add_post_status_to_dropdown' ) );
		add_filter( 'display_post_states', array( $this, 'display_post_states' ) );
		add_filter( 'wp_insert_post_data', array( $this, 'keep_imported_status' ), 100, 4 );
	}

	public function add_post_status_to_dropdown() {
		global $post;
		if ( 'post' !== $post->post_type ) {
			return;
		}

		$status = get_post_status( $post->ID );
		?>
		<script>
			jQuery(function ($) {
				$("select#post_status").append("<option value='imported' <?php selected( $status, 'imported' ); ?>>Imported</option>");
				<?php if ( 'imported' === $status ) : ?>
					$("#post-status-display").text( "Imported" );
					$("#minor-publishing-actions").hide();
					$("#save-post").hide();
					$("#post-preview").hide();
					$("#publish").val("Save");
				<?php endif; ?>
			});
		</script>
		<?php
	}

	/**
	 * Displays - Imported after the post
	 */
	public function display_post_states( $statuses ) {
		global $post;
		if ( ! $post ) {
			return $statuses;
		}
		$status = get_post_status( $post->ID );

		if ( 'imported' === $status ) {
			$statuses[] = sprintf(
				"<span class='status-%s'>%s</span>",
				'imported',
				_x( 'Imported', 'post status', 'rss-feed-importer' ),
			);
		}

		return $statuses;
	}

	/**
	 * @hook wp_insert_post_data
	 *
	 * @param array $data                An array of slashed, sanitized, and processed post data.
	 * @param array $postarr             An array of sanitized (and slashed) but otherwise unmodified post data.
	 * @param array $unsanitized_postarr An array of slashed yet *unsanitized* and unprocessed post data as
	 *                                   originally passed to wp_insert_post().
	 * @param bool  $update              Whether this is an existing post being updated.
	 */
	public function keep_imported_status( $data, $postarr, $unsanitized_postarr, $update ) {
		if ( ! $update ) { // If we aren't updating, we shouldn't be caring
			return $data;
		}

		$post_id = $postarr['ID'] ?? 0;
		if ( ! $post_id ) {
			return $data;
		}

		$current_post_status = get_post_status( $post_id );
		if ( 'imported' !== $current_post_status ) {
			return $data;
		}

		if ( 'publish' !== $data['post_status'] ) {
			return $data;
		}

		$data['post_status'] = 'imported';

		return $data;
	}

	public function register_meta_box() {
		if ( 'imported' !== get_post_status() ) {
			return;
		}

		add_meta_box(
			'rss-feed-importer_import_information',
			'Import Information',
			array( $this, 'render_metabox' ),
			'post',
			'advanced',
			'default'
		);
	}

	public function render_metabox( WP_Post $post ) {
		$import_meta = get_post_meta( $post->ID, 'rss-feed-importer_import_meta', true );
		?>
		<div>
			<strong>Feed Information:</strong><br>
			<?php if ( isset( $import_meta['feed_name'] ) ) : ?>
				<em>Feed: </em><a href='#'><?php echo esc_html( $import_meta['feed_name'] ); ?></a><br>
			<?php endif; ?>
			<em>Imported On: </em><?php echo esc_html( $import_meta['imported_at'] ); ?>
		</div>

		<?php if ( isset( $import_meta['authors'] ) ) : ?>
			<div>
				<strong>Article Information:</strong><br>
				<em>Link: </em>
				<a href='<?php echo esc_url( $import_meta['link'] ); ?>' target="_blank">
					<?php if ( isset( $import_meta['site_favicon'] ) ) : ?>
						<img src="<?php echo wp_get_attachment_image_url( $import_meta['site_favicon'] ); ?>" style="width: 1em; height: 1em">
					<?php endif; ?>
					Read Full Article
				</a>
				<br>
				<em>Authors: </em> <?php echo join_with_last( $import_meta['authors'], ', ', ' & ' ); ?>
				<br>
				
				<?php if ( isset( $import_meta['publisher'] ) ) : ?>
					<em>Publisher:</em>
					<?php if ( isset( $import_meta['site_logo'] ) ) : ?>
						<img src="<?php echo wp_get_attachment_image_url( $import_meta['site_logo'] ); ?>" style="width: 1em; height: 1em">
					<?php endif; ?>
					<span><?php echo esc_html( $import_meta['publisher'] ); ?></span>
				<?php endif; ?>
			</div>
		<?php endif; ?>
		<?php
	}

	public function update_post_author_for_imported_posts( $author_name ) {
		$post_status = get_post_status();
		if ( $post_status !== 'imported' ) {
			return $author_name;
		}

		$import_meta = get_post_meta( get_the_ID(), 'rss-feed-importer_import_meta', true );
		if ( isset( $import_meta['authors'] ) ) {
			return join_with_last( $import_meta['authors'], ', ', ' & ' );
		} 

		if ( isset( $import_meta['publisher'] ) ) {
			return $import_meta['publisher'];
		}

		return '';
	}

	public function update_author_link_for_imported_ports( string $value, int $user_id, int|false $original_user_id ) {
		$post_status = get_post_status();
		if ( $post_status !== 'imported' ) {
			return $value;
		}

		return '#';
	}

	public function sync_feeds() {
		$feeds = Feed::all();

		foreach ( $feeds as $feed ) {
			as_enqueue_async_action( 'rss-feed-importer/sync_feed', array( $feed->id ) );
		}
	}

	public function get_file() {
		return __FILE__;
	}

	public static function instance() {
		return self::$instance ??= new self();
	}

	public static function uninstall() {
		if ( ! function_exists( 'as_unschedule_all_actions' ) ) {
			return;
		}

		as_unschedule_all_actions( 'rss-feed-importer/sync_feeds' );
		as_unschedule_all_actions( 'rss-feed-importer/sync_feed' );
		as_unschedule_all_actions( 'rss-feed-importer/clean_up' );
	}

	public static function activate() {
		register_uninstall_hook( __FILE__, array( Database::class, 'uninstall' ) );
		register_uninstall_hook( __FILE__, array( self::class, 'uninstall' ) );
	}
}

Plugin::instance();
