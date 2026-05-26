<?php
declare( strict_types=1 );

namespace HealthPro\Referencing\Reference;

use HealthPro\Referencing\Property\Property;
use HealthPro\Referencing\Property\Validation;
use HealthPro\Referencing\Property\Validation\Required;
use HealthPro\Referencing\Property\Validation\URL;
use ReflectionClass;
use ReflectionProperty;

class Reference {
	#[Property( 'Title', 'What is the title of the document?' )]
	#[Validation\Required]
	public string $title;

	#[Property( 'Publisher', 'Who published the document?' )]
	public string $publisher;

	#[Property( 'Date Published', 'When was the document published?' )]
	public string $published;

	public $error = false;
	public $error_msg;

	public function output_harvard(): string {
		return sprintf(
			'%s. (%s). %s.',
			isset( $this->publisher ) ? esc_html( $this->publisher ) : '<em>Unknown</em>',
			isset( $this->published ) ? esc_html( $this->published ) : '<em>Unknown</em>',
			$this->title,
		);
	}

	protected function url( string $url, bool $new_tab = true ): string {
		return sprintf(
			"<a href='%s' %s>%s</a>",
			esc_url( $url ),
			$new_tab ? "target='_blank'" : '',
			esc_html( $url ),
		);
	}

	public static function create( array $attrs ) {
		$attrs = array_filter( $attrs );

		$reference = new static();

		$clazz                = new ReflectionClass( static::class );
		$properties           = $clazz->getProperties( ReflectionProperty::IS_PUBLIC );
		$shortcode_properties = array_filter(
			$properties,
			static fn ( ReflectionProperty $property ) => (bool) count( $property->getAttributes( Property::class ) )
		);

		foreach ( $shortcode_properties as $property ) {
			$value = $attrs[ $property->getName() ] ?? '';
			$value = trim( $value );
			if ( ! $value && (bool) count( $property->getAttributes( Required::class ) ) ) {
				$reference->error     = true;
				$reference->error_msg = "Missing required attribute ({$property->getName()})";
				return $reference;
			}

			if ( (bool) count( $property->getAttributes( URL::class ) ) ) {
				if ( ! empty( $value ) && ! filter_var( $value, FILTER_VALIDATE_URL ) ) {
					$reference->error     = true;
					$reference->error_msg = "Attribute `{$property->getName()}` needs to be a URL";
					return $reference;
				}
			}

			if ( ! $value ) {
				continue;
			}

			$reference->{$property->getName()} = $value;
		}

		return $reference;
	}

	public static function properties() {
		$clazz                = new ReflectionClass( static::class );
		$properties           = $clazz->getProperties( ReflectionProperty::IS_PUBLIC );
		$shortcode_properties = array_filter(
			$properties,
			static fn ( ReflectionProperty $property ) => (bool) count( $property->getAttributes( Property::class ) )
		);

		return array_reduce(
			$shortcode_properties,
			static function ( $carry, ReflectionProperty $property ) {
				$carry[ $property->getName() ] = self::convert_property_into_array( $property );
				return $carry;
			},
			array()
		);
	}

	private static function convert_property_into_array( ReflectionProperty $property ) {
		$property_instance = current( $property->getAttributes( Property::class ) )->newInstance();
		$internal_type     = $property->getType()->getName();

		$type = match ( true ) {
			(bool) count( $property->getAttributes( Validation\URL::class ) ) => 'url',
			'string' === $internal_type => 'text',
			'int' === $internal_type => 'number',
			default => 'text',
		};
		$is_required = (bool) count( $property->getAttributes( Validation\Required::class ) );

		return array(
			'type'        => $type,
			'label'       => $property_instance->label,
			'description' => $property_instance->description,
			'required'    => $is_required,
		);
	}
}
