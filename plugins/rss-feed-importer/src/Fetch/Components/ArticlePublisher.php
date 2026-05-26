<?php

namespace WatchTheDot\Plugins\RSSImporter\Fetch\Components;

use Brick\Schema\Interfaces as Schema;
use WatchTheDot\Plugins\RSSImporter\Model\Feed;
use WatchTheDot\Plugins\RSSImporter\Model\FeedPost;
use WatchTheDot\Plugins\RSSImporter\Support\OpenGraph;

use function WatchTheDot\Plugins\RSSImporter\if_not_null;

class ArticlePublisher {

    public static function invoke( ?Schema\Article $schema, OpenGraph $open_graph, ?FeedPost $feed_post ) {
        return if_not_null( $schema, fn () => self::from_schema_org( $schema ) )
            ?? self::from_open_graph( $open_graph )
            ?? if_not_null( $feed_post, fn () => self::from_feed_post( $feed_post ) )
            ;
    }

    public static function from_schema_org( Schema\Article $article_schema ) {
        $publisher = $article_schema->publisher?->getFirstValue();
        if ( is_null( $publisher ) ) {
            return null;
        }

        return $publisher->name?->getFirstNonEmptyStringValue();
    }

    public static function from_open_graph( OpenGraph $open_graph ) {
        return $open_graph->site_name;
    }

    public static function from_feed_post( FeedPost $feed_post ) {
        return Feed::find( $feed_post->feed_id )?->name;
    }
}