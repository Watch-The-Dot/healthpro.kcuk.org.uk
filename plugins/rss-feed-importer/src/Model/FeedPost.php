<?php
namespace WatchTheDot\Plugins\RSSImporter\Model;

use Brick\Schema\Interfaces\Thing;
use Brick\Schema\SchemaReader;
use DateTimeImmutable;
use InvalidArgumentException;
use WatchTheDot\Plugins\RSSImporter\Support\OpenGraph;

/**
 * @property string $guid
 * @property string $hash
 * @property int $feed_id
 * @property string $link
 * @property string $post_title
 * @property string $preview
 * @property DateTimeImmutable $published_at
 * @property string $status
 * @property DateTimeImmutable $synced_at
 */
class FeedPost extends BaseModel {
	protected static string $table_name = 'rss_feed_posts';

	protected static $primary_key = array( 'hash', false );

	protected static string|false $created_at = 'synced_at';

	protected static string|false $updated_at = false;

	protected static array $casts = array(
		'published_at' => DateTimeImmutable::class,
	);

	public function set_guid( $value ) {
		$this->attributes['guid'] = $value;
		$this->attributes['hash'] = md5( $value );
	}

	public function set_status( $value ) {
		if ( ! in_array( $value, array( 'pending', 'rejected', 'imported' ), true ) ) {
			throw new InvalidArgumentException( 'Invalid status.' );
		}

		$this->attributes['status'] = $value;
	}

	public function favicon( $size = 64 ) {
		$host     = wp_parse_url( $this->link, PHP_URL_HOST );
		$link_uri = "https://{$host}";
		return add_query_arg(
			array(
				'client'        => 'SOCIAL',
				'type'          => 'FAVICON',
				'fallback_opts' => 'TYPE,SIZE,URL',
				'url'           => $link_uri,
				'size'          => $size,
			),
			'https://t2.gstatic.com/faviconV2'
		);
	}

	private string $link_body;
	public function body() {
		if ( ! isset( $this->link_body ) ) {
			$body = wp_remote_retrieve_body( wp_remote_get( $this->link ) );
			if ( ! $body ) {
				return null;
			}

			$this->link_body = $body;
		}

		return $this->link_body;
	}

	private OpenGraph $openGraph;
	public function openGraph() {
		if ( ! isset( $this->openGraph ) ) {
			$body = $this->body();
			if ( is_null( $body ) ) {
				return null;
			}

			$this->openGraph = OpenGraph::parse( $body );
		}

		return $this->openGraph;
	}

	private array $schemas;
	/**
	 * @return Thing[]
	 */
	public function schemaOrg() {
		if ( ! isset( $this->schemas ) ) {
			$body = $this->body();
			if ( is_null( $body ) ) {
				return array();
			}

			$reader        = SchemaReader::forAllFormats();
			$this->schemas = $reader->readHtml( $body, $this->link );
		}

		return $this->schemas;
	}
}
