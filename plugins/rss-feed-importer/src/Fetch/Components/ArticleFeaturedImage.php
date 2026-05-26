<?php

namespace WatchTheDot\Plugins\RSSImporter\Fetch\Components;

use Brick\Schema\Interfaces as Schema;
use WatchTheDot\Plugins\RSSImporter\Fetch\Media\FetchedMedia;
use WatchTheDot\Plugins\RSSImporter\Support\OpenGraph;

use function WatchTheDot\Plugins\RSSImporter\if_not_null;

class ArticleFeaturedImage {
    public static function invoke( ?Schema\Article $schema_org, OpenGraph $open_graph, string $title ) {
        $image = if_not_null( $schema_org, fn () => self::from_schema_org( $schema_org ) )
            ?? self::from_open_graph( $open_graph );

        if ( is_null( $image ) ) {
            return null;
        }

        return new FetchedMedia( $image, sanitize_key( $title ) . "-featured" );
    }

    public static function from_schema_org( Schema\Article $article_schema ) {
        $thumbnail = $article_schema->thumbnailUrl?->getFirstNonEmptyStringValue()
            ?? $article_schema->thumbnail?->getFirstValue()?->url?->getFirstNonEmptyStringValue();
        if ( ! is_null( $thumbnail ) ) {
            return $thumbnail;
        }

        $thumbnail = $article_schema->image?->getFirstValue();
        if ( is_string( $thumbnail ) ) {
            return $thumbnail;
        }

        return $thumbnail?->url?->getFirstNonEmptyStringValue();
    }

    public static function from_open_graph( OpenGraph $open_graph ) {
        return $open_graph?->image ?? null;
    }
}