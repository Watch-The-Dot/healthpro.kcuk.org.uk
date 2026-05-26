<?php

namespace WatchTheDot\Plugins\RSSImporter\Fetch\Components;

use WatchTheDot\Plugins\RSSImporter\Model\FeedPost;
use WatchTheDot\Plugins\RSSImporter\Support\OpenGraph;

use function WatchTheDot\Plugins\RSSImporter\if_not_null;

class ArticleExcerpt {
    public static function invoke( ?FeedPost $feed_post, OpenGraph $open_graph ) {
        return if_not_null($feed_post, fn () => self::from_feed_post( $feed_post ) )
            ?? self::from_open_graph( $open_graph );
    }

    public static function from_feed_post( FeedPost $feed_post ) {
        return $feed_post->preview;
    }

    public static function from_open_graph( OpenGraph $open_graph ) {
        return $open_graph->description;
    }
}