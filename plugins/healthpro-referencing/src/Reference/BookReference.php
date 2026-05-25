<?php
declare(strict_types=1);

namespace HealthPro\Referencing\Reference;

use HealthPro\Referencing\Property\Property;
use HealthPro\Referencing\Property\Validation;

class BookReference extends Reference {
	#[Property( 'Title', 'What is the title of the book?' )]
	#[Validation\Required]
	public string $title;

	#[Property( 'Publisher' )]
	#[Validation\Required]
	public string $publisher;

	#[Property( 'Year Published' )]
	#[Validation\Required]
	public string $published;

	#[Property( 'Author(s)' )]
	#[Validation\Required]
	public string $author;

	#[Property( 'Publisher City' )]
	#[Validation\Required]
	public string $city;

	public function output_harvard(): string {
		return sprintf(
            "%s. (%s) %s. %s: %s",
			esc_html( $this->author ),
            esc_html( $this->published ),
            esc_html( $this->title ),
            esc_html( $this->city ),
            esc_html( $this->publisher ),
		);
	}
}
