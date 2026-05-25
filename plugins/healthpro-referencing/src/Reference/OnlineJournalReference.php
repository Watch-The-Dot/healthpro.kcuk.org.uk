<?php
declare(strict_types=1);

namespace HealthPro\Referencing\Reference;

use HealthPro\Referencing\Property\Property;
use HealthPro\Referencing\Property\Validation;

class OnlineJournalReference extends PrintJournalReference {
	#[Property( 'Document Object Identifier (DOI)', 'ISO standard unique string identifier for a digital object' )]
	public string $doi;

	#[Property( 'URL', 'If DOI is not defined, the URL of the paper' )]
	#[Validation\URL]
	public string $url;

	#[Property( 'Date Accessed', 'If DOI is not defined, when the paper was accessed' )]
	public string $accessed;

	public function output_harvard(): string {
		$output = parent::output_harvard();

		if ( isset( $this->doi ) ) {
			$doi_link = 'https://www.doi.org/' . $this->doi;
			$output  .= sprintf(
				". doi: <a href='%s' target='_blank'>%s</a>",
				esc_url( $doi_link ),
				esc_html( $this->doi ),
			);
		} elseif ( isset( $this->url ) ) {
			$output .= sprintf(
				'. Available at: %s %s',
				$this->url( $this->url ),
				isset( $this->accessed ) ? sprintf(
					'(Accessed: %s)',
					esc_html( $this->accessed )
				) : '',
			);
		}

		return $output;
	}
}
