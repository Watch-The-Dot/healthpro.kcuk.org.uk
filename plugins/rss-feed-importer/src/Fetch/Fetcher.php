<?php

namespace WatchTheDot\Plugins\RSSImporter\Fetch;

use Brick\Schema\Interfaces as Schema;
use Brick\Schema\SchemaReader;
use InvalidArgumentException;
use ReflectionMethod;
use Symfony\Component\DomCrawler\Crawler;
use WatchTheDot\Plugins\RSSImporter\Model\FeedPost;
use WatchTheDot\Plugins\RSSImporter\Support\OpenGraph;

/**
 * Builds FetchResult
 */
class Fetcher {
    protected string $url;
    
    protected FeedPost $feed_post;

    protected string $response_body;

    protected ?OpenGraph $open_graph;

    protected ?Schema\Article $schema_org;

    protected Crawler $crawler;

    public function get_result() {
        $result = new FetchResult();
        
        $result->article_title = $this->fetch_component(Components\ArticleTitle::class);
        $result->article_published = $this->fetch_component(Components\ArticlePublishDate::class);
        $result->article_excerpt = $this->fetch_component(Components\ArticleExcerpt::class);
        $result->article_content = $this->fetch_component(Components\ArticleContent::class);
        $result->article_authors = $this->fetch_component(Components\ArticleAuthors::class);
        $result->article_publisher = $this->fetch_component(Components\ArticlePublisher::class);

        $result->site_favicon = $this->fetch_component(Components\SiteFavicon::class, $this->url ?? '');
        $result->publisher_logo = $this->fetch_component(Components\PublisherLogo::class, $result->article_publisher);
        $result->featured_image = $this->fetch_component(Components\ArticleFeaturedImage::class, $result->article_title);

        $result->seal();
        return $result;
    }

    /**
     * @param class-string $component_class
     */
    protected function fetch_component( string $component_class, ...$args ) {
        $invoke_method = new ReflectionMethod( $component_class, "invoke" );
        $method_parameters = $invoke_method->getParameters();
        $parameters = [];
        foreach ( $method_parameters as $parameter ) {
            if ( $parameter->getType()->isBuiltin() ) {
                $parameters[] = array_shift($args);
            } else {
                $parameters[] = match ( $parameter->getType()->getName() ) {
                    FeedPost::class => $this->feed_post ?? null,
                    Schema\Article::class => $this->schema_org ??= $this->get_schema_org(),
                    OpenGraph::class => $this->open_graph ??= OpenGraph::parse( $this->response_body ),
                    Crawler::class => $this->crawler ??= new Crawler( $this->response_body ),
                    default => throw new InvalidArgumentException( "Cannot inject dependency" )
                };
            }
        }

        return $invoke_method->invoke( null, ...$parameters );
    }

    private function get_schema_org() {
        // This include all schemes found on the page
        $schemas = SchemaReader::forAllFormats()->readHtml( $this->response_body, $this->url ?? '' );

        // 1. Check for a WebPage main entity and if the mainEntity is an article
        /** @var Schema\WebPage[] */
        $web_page = array_filter( $schemas, fn ( Schema\Thing $schema ) => $schema instanceof Schema\WebPage );
        if ( count( $web_page ) >= 1 ) {
            $main_entity = current( $web_page )->mainEntity;
            if ( $main_entity instanceof Schema\Article ) {
                return $main_entity;
            }
        }

        // 2. Just search for an article
        /** @var Schema\Article[] */
        $article = array_filter( $schemas, fn ( Schema\Thing $schema ) => $schema instanceof Schema\Article );
        if ( count( $article ) >= 1 ) {
            return current( $article );
        }

        return null;
    }

    private function read_body() {
        $response = wp_remote_get( $this->url );
        if ( is_wp_error( $response ) ) {
            throw new FetchException( "Could not access URL.", $response );
        }

        $body = wp_remote_retrieve_body( $response );
        if ( ! $body ) {
            throw new FetchException( "Received an empty body." );
        }

        $this->response_body = $body;
    }

    public static function from_feed_post( FeedPost $post ) {
        $fetcher = new self();
        $fetcher->feed_post = $post;
        $fetcher->url = $post->link;

        $fetcher->read_body();

        return $fetcher;
    }

    public static function from_url( string $url, ?FeedPost $post = null ) {
        $fetcher = new self();
        $fetcher->url = $url;
        if ( ! is_null( $post ) ) {
            $fetcher->feed_post = $post;
        }

        $fetcher->read_body();
        return $fetcher;
    }

    public static function from_html( string $html, ?FeedPost $post = null ) {
        $fetcher = new self();
        $fetcher->response_body = $html;
        if ( ! is_null( $post ) ) {
            $fetcher->feed_post = $post;
        }
        return $fetcher;
    }
}