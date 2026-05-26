<?php

namespace WatchTheDot\Plugins\RSSImporter\Fetch\Components;

use Brick\Schema\Interfaces as Schema;
use Symfony\Component\DomCrawler\Crawler;
use WatchTheDot\Plugins\RSSImporter\Settings;
use WatchTheDot\Plugins\RSSImporter\Support\Collection;

use function WatchTheDot\Plugins\RSSImporter\if_not_null;

class ArticleContent {
    const SEARCH_HTML_ORDER = [
        'main > article :not(header):not(footer):not(aside) p',
        'article :not(header):not(footer):not(aside) p',
        // TODO
    ];

    public static function invoke( ?Schema\Article $schema_org, Crawler $crawler ) {
        $content = if_not_null( $schema_org, fn () => self::from_schema_org( $schema_org ) )
            ?? self::from_content( $crawler );

        if ( is_null( $content ) ) {
            return $content;
        }

		$settings = Settings::instance()->wpsf->get_settings();
		$max_words = intval( $settings['import_settings_max_post_content'] ) ?? 200;
        return self::restrict_word_count( $content, $max_words );
    }

    public static function from_schema_org( Schema\Article $article_schema ) {
        return $article_schema->articleBody?->getFirstNonEmptyStringValue();
    }

    public static function from_content( Crawler $crawler ) {
        $elements = self::get_elements_from_content( $crawler );
        if ( is_null( $elements ) ) {
            return null;
        }

        $paragraphs_content = $elements->each( static fn ( Crawler $node ) => $node->outerHtml() );
        return ( new Collection( $paragraphs_content ) )
            ->map( self::cleanup_paragraphs( ... ) )
            ->join( '' );
    }

    private static function restrict_word_count( string $content, int $word_count ) {
		$tokens        = array();
		$excerptOutput = '';
		$count         = 0;

		// Divide the string into tokens; HTML tags, or words, followed by any whitespace
		preg_match_all( '/(<[^>]+>|[^<>\s]+)\s*/u', $content, $tokens );

		foreach ( $tokens[0] as $token ) {
			if ( $count >= $word_count && preg_match( '/[\,\;\?\.\!]\s*$/uS', $token ) ) {
				// Limit reached, continue until , ; ? . or ! occur at the end
				$excerptOutput .= trim( $token );
				break;
			}

			// Add words to complete sentence
			++$count;

			// Append what's left of the token
			$excerptOutput .= $token;
		}

		return trim( force_balance_tags( $excerptOutput ) );
	}

    private static function cleanup_paragraphs( $paragraph ) {
        /**
         * Format:
         * tag => [attribute => true]
         */
        $allowed_html = array(
            'p'   => array(),
            'div' => array(),
            'em' => array(),
            'strong' => array(),
        );

        return wp_kses( $paragraph, $allowed_html );
    }

    private static function get_elements_from_content( Crawler $crawler ) {
        foreach ( self::SEARCH_HTML_ORDER as $search_query ) {
            if ( ( $el = $crawler->filter( $search_query ) )->count() ) {
                return $el;
            }
        }

        if ( $crawler->filter('#abstract')->count() ) {
            // Check if heading or block
            // If heading, find next block
            // If block, return content
        }

        return null;
    }
}