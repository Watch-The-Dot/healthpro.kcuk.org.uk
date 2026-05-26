<?php

namespace WatchTheDot\Plugins\RSSImporter;

use WatchTheDot\Plugins\RSSImporter\Model\Feed;
use WatchTheDot\Plugins\RSSImporter\Model\FeedPost;

defined( 'ABSPATH' ) || exit;

//phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.DirectDatabaseQuery.NoCaching

/**
 * There are caveats to how we can define the SQL.
 *
 * @see https://codex.wordpress.org/Creating_Tables_with_Plugins#Creating_or_Updating_the_Table
 */
class Database {
	const OPTION_NAME = 'rss-feed-importer_db_version';
	const DB_VERSION  = 9;

	private static int $current_version;

	public static function install() {
		if ( ! function_exists( 'dbDelta' ) ) {
			require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		}

		self::install_feeds_table();
		self::install_feed_posts_table();
		self::install_sync_errors_table();

		if ( self::get_db_version() < 7 ) {
			self::migrate_meta_data_to_hidden_values();
		}

		if ( self::get_db_version() < 8 ) {
			self::add_synced_at_time_for_posts();
		}

		self::$current_version = self::DB_VERSION;
		update_option( self::OPTION_NAME, self::DB_VERSION );
	}

	private static function install_feeds_table() {
		global $wpdb;
		$charset_collate = $wpdb->get_charset_collate();

		$table_name = Feed::table_name();
		$sql        = "CREATE TABLE {$table_name} (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            name tinytext NOT NULL,
            url varchar(100) NOT NULL,
            created_at datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
            updated_at datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
            synced_at datetime,
            PRIMARY KEY  (id)
        ) {$charset_collate};";

		dbDelta( $sql );
	}

	private static function install_feed_posts_table() {
		global $wpdb;
		$charset_collate = $wpdb->get_charset_collate();

		$table_name = FeedPost::table_name();
		$sql        = "CREATE TABLE {$table_name} (
            guid varchar(255) NOT NULL,
			hash varchar(32) NOT NULL,
            feed_id mediumint(9) NOT NULL,
            post_title tinytext NOT NULL,
			site_title tinytext NOT NULL,
            link tinytext NOT NULL,
            preview mediumtext NOT NULL,
            published_at datetime NOT NULL,
            status varchar(10) DEFAULT 'pending' NOT NULL,
            synced_at datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
            PRIMARY KEY  (guid)
        ) {$charset_collate};";

		dbDelta( $sql );

		//phpcs:ignore SlevomatCodingStandard.ControlStructures.EarlyExit.EarlyExitNotUsed
		if ( self::get_db_version() < 5 ) {
			$wpdb->query(
				$wpdb->prepare(
					'CREATE UNIQUE INDEX `rss_feed_posts_hash` ON %i (`hash`)',
					$table_name
				)
			);
		}
	}

	private static function install_sync_errors_table() {
		global $wpdb;
		$charset_collate = $wpdb->get_charset_collate();

		$table_name = $wpdb->prefix . 'rss_sync_errors';
		$sql        = "CREATE TABLE {$table_name} (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
			feed_id mediumint(9) NOT NULL,
			error mediumtext NOT NULL,
            created_at datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
            PRIMARY KEY  (id)
        ) {$charset_collate};";

		dbDelta( $sql );
	}

	private static function migrate_meta_data_to_hidden_values() {
		global $wpdb;

		$wpdb->query(
			"UPDATE {$wpdb->postmeta}
			SET `meta_key` = CONCAT('_', `meta_key`)
			WHERE `meta_key` LIKE 'rss-feed-importer%'"
		);
	}

	private static function add_synced_at_time_for_posts() {
		global $wpdb;

		$wpdb->query(
			$wpdb->prepare(
				"UPDATE %i
				SET `synced_at` = NOW()
				WHERE `synced_at` = 0",
				FeedPost::table_name(),
			)
		);
	}

	public static function maybe_upgrade() {
		if ( self::get_db_version() >= self::DB_VERSION ) {
			return;
		}

		static::install();
	}

	public static function uninstall() {
		global $wpdb;

		$drop_tables = array(
			Feed::table_name(),
			FeedPost::table_name(),
			$wpdb->prefix . 'rss_sync_errors',
		);
		foreach ( $drop_tables as $table ) {
			$table_name = "{$wpdb->prefix}{$table}";
			$wpdb->query(
				$wpdb->prepare(
					'DROP TABLE IS EXISTS %i',
					$table_name
				)
			);
		}

		$drop_options = array( self::OPTION_NAME );
		foreach ( $drop_options as $drop_option ) {
			delete_option( $drop_option );
		}
	}

	public static function get_db_version() {
		if ( ! isset( self::$current_version ) ) {
			$db_version            = get_option( self::OPTION_NAME, 0 );
			self::$current_version = is_numeric( $db_version ) ? intval( $db_version ) : 0;
		}

		return self::$current_version;
	}
}
