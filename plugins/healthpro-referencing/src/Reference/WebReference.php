<?php
declare(strict_types=1);

namespace HealthPro\Referencing\Reference;

use HealthPro\Referencing\Property\Property;
use HealthPro\Referencing\Property\Validation;

class WebReference extends Reference {
	#[Property( 'URL', 'The permalink for the web page' )]
	#[Validation\Required]
	#[Validation\URL]
	public string $url;

	#[Property( 'Date Accessed', 'When was the page last accessed?' )]
	public string $accessed;

	public function output_harvard(): string {
		return sprintf(
			'%s. (%s). %s. [online] Available at: %s. %s',
			isset( $this->publisher ) ? esc_html( $this->publisher ) : '<em>Unknown</em>',
			isset( $this->published ) ? esc_html( $this->published ) : '<em>Unknown</em>',
			$this->title,
			$this->url( $this->url ),
			isset( $this->accessed ) ? sprintf(
				'(Accessed %s)',
				esc_html( $this->accessed ),
			) : ''
		);
	}
}
