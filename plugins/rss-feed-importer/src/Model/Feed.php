<?php
namespace WatchTheDot\Plugins\RSSImporter\Model;

use DateTimeImmutable;

/**
 * @property int $id
 * @property string $name
 * @property string $url
 * @property DateTimeImmutable $created_at
 * @property DateTimeImmutable $updated_at
 * @property ?DateTimeImmutable $synced_at
 */
class Feed extends BaseModel {
	protected static string $table_name = 'rss_feeds';

	protected static array $casts = array(
		'synced_at' => DateTimeImmutable::class,
	);
}
