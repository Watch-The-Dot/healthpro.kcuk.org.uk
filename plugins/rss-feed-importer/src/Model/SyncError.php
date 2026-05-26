<?php
namespace WatchTheDot\Plugins\RSSImporter\Model;

/**
 * @property
 */
class SyncError extends BaseModel {
	protected static string $table_name = 'rss_sync_error';

	protected static string|false $updated_at = false;

	public static function create( $feed_id, $message ) {
		$instance = new self();

		$instance->feed_id = $feed_id;
		$instance->message = $message;

		return $instance->save();
	}
}
