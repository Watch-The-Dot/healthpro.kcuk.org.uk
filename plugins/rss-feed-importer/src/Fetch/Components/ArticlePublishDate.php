<?php

namespace WatchTheDot\Plugins\RSSImporter\Fetch\Components;

use Brick\Schema\Interfaces as Schema;
use WatchTheDot\Plugins\RSSImporter\Model\FeedPost;

use function WatchTheDot\Plugins\RSSImporter\if_not_null;

class ArticlePublishDate {
    public static function invoke( ?Schema\Article $schema_org, ?FeedPost $feed_post ) {
        return if_not_null( $schema_org, fn () => self::from_schema_org( $schema_org ) )
            ?? if_not_null( $feed_post, fn () => self::from_feed_post( $feed_post ) );
    }

    public static function from_schema_org( Schema\Article $article_schema ) {
        $dt = $article_schema->datePublished->getFirstNonEmptyStringValue();
        if ( ! $dt ) {
            return null;
        }

        return date_create_immutable( $dt ) ?: null;
    }

    public static function from_feed_post( FeedPost $feed_post ) {
        return $feed_post->published_at;
    }
}