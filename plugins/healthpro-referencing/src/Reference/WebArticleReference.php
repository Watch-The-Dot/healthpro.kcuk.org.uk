<?php
declare(strict_types=1);

namespace HealthPro\Referencing\Reference;

use HealthPro\Referencing\Property\Property;
use HealthPro\Referencing\Property\Validation;

class WebArticleReference extends WebReference {
	#[Property( 'Author(s)', 'Who is the author of the article?' )]
	#[Validation\Required]
	public string $author;

	#[Property( 'Publisher', 'The name of the website where the article is published' )]
	#[Validation\Required]
	public string $publisher;

	public function output_harvard(): string {
		return sprintf(
			"%s. (%s). '%s', %s. [online] Available at: %s. %s",
			esc_html( $this->author ),
			isset( $this->published ) ? esc_html( $this->published ) : '<em>Unknown</em>',
			$this->title,
			esc_html( $this->publisher ),
			$this->url( $this->url ),
			isset( $this->accessed ) ? sprintf(
				'(Accessed %s)',
				esc_html( $this->accessed ),
			) : ''
		);
	}
}
