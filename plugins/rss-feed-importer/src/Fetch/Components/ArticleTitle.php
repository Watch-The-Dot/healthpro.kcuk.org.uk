<?php

namespace WatchTheDot\Plugins\RSSImporter\Fetch\Components;

use Brick\Schema\Interfaces as Schema;
use Symfony\Component\DomCrawler\Crawler;
use WatchTheDot\Plugins\RSSImporter\Model\FeedPost;
use WatchTheDot\Plugins\RSSImporter\Support\OpenGraph;

use function WatchTheDot\Plugins\RSSImporter\if_not_null;

class ArticleTitle {
    public static function invoke( ?Schema\Article $schema_org, ?FeedPost $feed_post, OpenGraph $open_graph, Crawler $crawler ) {
        return if_not_null( $schema_org, fn () => self::from_schema_org( $schema_org ) )
            ?? self::from_open_graph( $open_graph )
            ?? if_not_null( $feed_post, fn () =>  self::from_feed_post( $feed_post ) )
            ?? self::from_title_tag( $crawler );
    }

    public static function from_schema_org( Schema\Article $article_schema ) {
        return $article_schema->headline?->getFirstNonEmptyStringValue() 
            ?? $article_schema->alternativeHeadline?->getFirstNonEmptyStringValue()
            ?? $article_schema->name?->getFirstNonEmptyStringValue();
    }

    public static function from_feed_post( FeedPost $feed_post ) {
        return $feed_post->post_title;
    }
    
    public static function from_open_graph( OpenGraph $open_graph ) {
        return $open_graph->title;
    }

    public static function from_title_tag( Crawler $crawler ) {
        $title = $crawler->filter( 'title' );
        if ( $title->count() === 0 ) {
            return null;
        }

        return $title->innerText();
    }
}