<?php

namespace WatchTheDot\Plugins\RSSImporter\Fetch\Components;

use Brick\Schema\Interfaces as Schema;
use WatchTheDot\Plugins\RSSImporter\Fetch\Media\FetchedMedia;

class PublisherLogo {
    public static function invoke( ?Schema\Article $schema, ?string $publisher ) {
        if ( is_null( $schema ) ) {
            return null;
        }
        
        $url = self::from_schema_org( $schema )
            ;

        if ( is_null( $url ) ) {
            return null;
        }

        if ( ! is_null( $publisher ) ) {
            $publisher = sanitize_key( $publisher );
        } else {
            $publisher = wp_generate_uuid4();
        }

        return new FetchedMedia( $url, $publisher ); 
    }

    public static function from_schema_org( Schema\Article $article_schema ) {
        /**
         * @var Schema\Organization|Schema\Person
         */
        $publisher = $article_schema->publisher?->getFirstValue();
        if ( is_null( $publisher ) ) {
            return null;
        }

        /**
         * @var Schema\ImageObject|string|null
         */
        $image_schema = $publisher->logo?->getFirstValue()
            ?? $publisher->image?->getFirstValue();
        if ( is_null( $image_schema ) ) {
            return null;
        }
        if ( is_string( $image_schema ) ) {
            return $image_schema;
        }

        return $image_schema->url?->getFirstNonEmptyStringValue()
            ?? $image_schema->contentUrl?->getFirstNonEmptyStringValue();
    }
}