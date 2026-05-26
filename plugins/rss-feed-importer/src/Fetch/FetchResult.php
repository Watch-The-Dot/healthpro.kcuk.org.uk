<?php

namespace WatchTheDot\Plugins\RSSImporter\Fetch;

use DateTimeImmutable;
use RuntimeException;
use WatchTheDot\Plugins\RSSImporter\Fetch\Media\FetchedMedia;

/**
 * @property null|string $article_title
 * @property null|string $article_content
 * @property null|string $article_excerpt
 * @property null|DateTimeImmutable $article_published
 * @property null|string[] $article_authors
 * @property null|string $article_publisher
 * @property null|FetchedMedia $publisher_logo
 * @property null|FetchedMedia $site_favicon
 * @property null|FetchedMedia $featured_image
 */
class FetchResult {
    private bool $sealed = false;

    protected ?string $article_title;
    protected ?string $article_content;
    protected ?string $article_excerpt;
    protected ?DateTimeImmutable $article_published;
    protected ?array $article_authors;
    protected ?string $article_publisher;
    protected ?FetchedMedia $publisher_logo;
    protected ?FetchedMedia $site_favicon;
    protected ?FetchedMedia $featured_image;

    public function seal() {
        $this->sealed = true;
    }

    public function __get(string $name) {
        if ( ! property_exists( $this, $name ) ) {
            throw new RuntimeException( "Trying to get a property `$name` that doesn't exist on class" );
        }

        return $this->{$name};
    }

    public function __set(string $name, $value) {
        if ( $this->sealed ) {
            throw new RuntimeException( "Class is sealed" );
        }
        
        if ( ! property_exists( $this, $name ) ) {
            throw new RuntimeException( "Trying to set a property `$name` that doesn't exist on class" );
        }

        $this->{$name} = $value;
    }
}