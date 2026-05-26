<?php

namespace WatchTheDot\Plugins\RSSImporter\Fetch\Components;

use Brick\Schema\Interfaces as Schema;
use Symfony\Component\DomCrawler\Crawler;
use WatchTheDot\Plugins\RSSImporter\Support\OpenGraph;

use function WatchTheDot\Plugins\RSSImporter\if_not_null;

class ArticleAuthors {
    const SEARCH_HTML_ORDER = [
        [ 'meta[name="citation_author"]', 'content' ],
        [ 'meta[name="dc.creator"]', 'content' ],
        // TODO
    ];

    public static function invoke( ?Schema\Article $schema, OpenGraph $open_graph, Crawler $crawler ) {
        return if_not_null( $schema, fn () => self::from_schema_org( $schema ) )
            ?? self::from_meta_tags( $crawler )
            // ?? self::from_open_graph( $open_graph )
            ;
    }

    public static function from_schema_org( Schema\Article $article_schema ) {
        $authors = $article_schema->author?->getValues() ?? [];
        if ( count( $authors ) === 0 ) {
            return null;
        }

        return array_map(
            static fn ( $author ) => is_string( $author ) ? $author : $author->name->getFirstNonEmptyStringValue(),
            $authors,
        );
    }

    public static function from_meta_tags( Crawler $crawler ) {
        foreach ( self::SEARCH_HTML_ORDER as $tag_information ) {
            [ $selector, $content_attribute ] = $tag_information;
            $tags = $crawler->filter( $selector );
            if ( $tags->count() ) {
                return $tags->each( fn ( Crawler $node ) => $node->attr( $content_attribute ) );
            }
        }

        return null;
    }
}