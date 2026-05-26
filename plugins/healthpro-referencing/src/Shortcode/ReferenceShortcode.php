<?php

namespace HealthPro\Referencing\Shortcode;

use HealthPro\Referencing\Reference\BookReference;
use HealthPro\Referencing\Reference\OnlineJournalReference;
use HealthPro\Referencing\Reference\PrintJournalReference;
use HealthPro\Referencing\Reference\Reference;
use HealthPro\Referencing\Reference\WebArticleReference;
use HealthPro\Referencing\Reference\WebReference;

class ReferenceShortcode {
	/**
	 * @var Reference[]
	 */
	private static array $references = array();

	/**
	 * Register the two shortcodes
	 *
	 * @hook init
	 */
	public static function register() {
		add_shortcode( 'cite', array( self::class, 'cite_shortcode' ) );
		add_shortcode( 'bib', self::bib_shortcode( ... ) );
		add_shortcode( 'references', array( self::class, 'references_shortcode' ) );
	}

	/**
	 * The Citation shortcode.
	 *
	 * @shortcode [cite]
	 */
	public static function cite_shortcode( $attrs, $content ) {
		if ( ! is_array( $attrs ) ) {
			$attrs = array();
		}

		// Match the reference type to the saved options
		$type  = $attrs[0] ?? $attrs['type'] ?? '';
		$class = match ( $type ) {
			'Book' => BookReference::class,
			'Web' => WebReference::class,
			'WebArticle' => WebArticleReference::class,
			'PrintJournal' => PrintJournalReference::class,
			'OnlineJournal' => OnlineJournalReference::class,
			default => Reference::class,
		};

		/** @var Reference */
		$reference = $class::create( $attrs );

		// Save the reference to be accessed by [references]
		self::add_reference( $reference );

		return self::output_cite( $reference, $content );
	}

	/**
	 * The Biblography shortcode.
	 *
	 * @shortcode [bib]
	 */
	public static function bib_shortcode( $attrs, $content ) {
		if ( ! is_array( $attrs ) ) {
			$attrs = array();
		}

		// Match the reference type to the saved options
		$type  = $attrs[0] ?? $attrs['type'] ?? '';
		$class = match ( $type ) {
			'Book' => BookReference::class,
			'Web' => WebReference::class,
			'WebArticle' => WebArticleReference::class,
			'PrintJournal' => PrintJournalReference::class,
			'OnlineJournal' => OnlineJournalReference::class,
			default => Reference::class,
		};

		/** @var Reference */
		$reference = $class::create( $attrs );

		// Save the reference to be accessed by [references]
		self::add_reference( $reference );

		return "";
	}

	public static function add_reference( Reference $reference ) {
		self::$references[] = $reference;
	}

	private static function output_cite( Reference $reference, $content ) {
		if ( $content ) {
			$url     = $reference->url ?? '';
			$content = "<q cite='{$url}'>$content</q>";
		}
		$content .= "<sup>[<a href='#reference-" . esc_html( count( self::$references ) ) . "'>";
		$content .= esc_html( count( self::$references ) );
		if ( $reference->error ) {
			$content .= '! - ' . $reference->error_msg;
		}
		$content .= '</a>]</sup>';

		return $content;
	}

	/**
	 * The references shortcode.
	 *
	 * Output a bibliography of all the references defined in the content
	 * before this shortcode is called
	 *
	 * @shortcode [references]
	 */
	public static function references_shortcode( $attrs ) {
		if ( ! count( self::$references ) ) {
			return '';
		}

		$output = '<h2>References</h2>';
		foreach ( self::$references as $i => $reference ) {
			if ( $reference->error ) {
				$output .= sprintf(
					"<p id='reference-%d'>[%d!] <strong>%s</strong></p>",
					esc_attr( $i + 1 ),
					esc_html( $i + 1 ),
					esc_html( $reference->error_msg ),
				);
				continue;
			}

			$output .= sprintf(
				"<p id='reference-%d'>[%d] %s</p>",
				esc_attr( $i + 1 ),
				esc_html( $i + 1 ),
				$reference->output_harvard(),
			);
		}

		return $output;
	}
}
