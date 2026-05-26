<?php

namespace HealthPro\Publications;

/*
 * Plugin Name:       [HealthPro] Publications
 * Plugin URI:
 * Description:
 * Version:           0.1.8
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
 * Text Domain:       healthpro-publications
 * Domain Path:       /languages
 */

require_once __DIR__ . '/vendor/autoload.php';

use HealthPro\Publications\Actions\SyncPublications;
use HealthPro\Publications\PostTypes\Publications;

class Plugin {
	private static self $instance;

	private function __construct() {
		Publications::init();
		add_action( 'action_scheduler_init', array( $this, 'register_cron' ) );
		add_action( 'healthpro-publications/sync', SyncPublications::run( ... ) );

		add_action( 'admin_menu', $this->add_admin_menu( ... ) );
	}

	/**
	 * @hook action_scheduler_init
	 */
	public function register_cron() {
		if ( as_has_scheduled_action( 'healthpro-publications/sync' ) ) {
			return;
		}

		as_schedule_recurring_action( time(), DAY_IN_SECONDS, 'healthpro-publications/sync' );
	}

	/**
	 * @hook admin_menu
	 */
	public function add_admin_menu() {
		add_menu_page(
			'Publications',
			'KCUK Publications',
			'manage_options',
			'healthpro-publication',
			$this->output_admin_menu( ... ),
			'dashicons-book',
			26
		);
	}

	public function output_admin_menu() {
		$terms = get_terms(
			array(
				'taxonomy' => Publications::CATEGORY_NAME,
				'hide_empty' => false,
			)
		);
		?>
		<div class="wrap">
			<h1>KCUK Publications</h1>
			<p>This content is synced every day.</p>
			<?php foreach ( $terms as $term ) : ?>
				<?php
					$posts = get_posts(
						array(
							'post_type' => Publications::POST_NAME,
							'numberposts' => -1,
							'tax_query' => array(
								'relation' => 'AND',
								array(
									'taxonomy' => Publications::CATEGORY_NAME,
									'terms'    => array( $term->term_id ),
								),
							),
						)
					);
				?>
				<h2><?php echo $term->name; ?> (<?php echo count( $posts ); ?>)</h2>
				<ul>
					<?php foreach ( $posts as $post ) : ?>
						<li>
							<?php echo get_the_title( $post ); ?>
							(<a href="<?php echo esc_url( get_post_meta( $post->ID, '_kcuk_link', true ) ); ?>" target="_blank">View</a>)
						</li>
					<?php endforeach; ?>
				</ul>
			<?php endforeach; ?>
		</div>
		<?php
	}

	public static function instance() {
		return self::$instance ??= new self();
	}
}

Plugin::instance();