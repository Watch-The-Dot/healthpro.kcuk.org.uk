<?php

namespace HealthPro\Referencing\Property;

use Attribute;

#[Attribute( Attribute::TARGET_PROPERTY )]
class Property {
	public function __construct(
		public readonly string $label,
		public readonly ?string $description = null
	) {
	}
}
