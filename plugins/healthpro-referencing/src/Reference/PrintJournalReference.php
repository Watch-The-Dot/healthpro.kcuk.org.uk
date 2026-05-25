<?php
declare(strict_types=1);

namespace HealthPro\Referencing\Reference;

use HealthPro\Referencing\Property\Property;
use HealthPro\Referencing\Property\Validation;

class PrintJournalReference extends Reference {
	#[Property( 'Date Published', 'When was the paper published?' )]
	#[Validation\Required]
	public string $published;

	#[Property( 'Author(s)', 'The author(s) of the paper' )]
	#[Validation\Required]
	public string $author;

	#[Property( 'Journal', 'The journal the paper was published in' )]
	#[Validation\Required]
	public string $journal;

	#[Property( 'Volume', 'The volume the paper appears in' )]
	#[Validation\Required]
	public string $volume;

	#[Property( 'Page Range', 'The page range of the paper' )]
	#[Validation\Required]
	public string $page;

	public function output_harvard(): string {
		return sprintf(
			"%s. (%s) '%s', %s, %s, pp. %s",
			esc_html( $this->author ),
			esc_html( $this->published ),
			esc_html( $this->title ),
			esc_html( $this->journal ),
			esc_html( $this->volume ),
			esc_html( $this->page )
		);
	}
}
