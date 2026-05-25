<?php
declare(strict_types=1);

namespace WatchTheDot\Plugins\DivibbPress\Settings;


class Fields {
	public static function yes_no_button( string $label, ?string $true_key = null, ?string $false_key = null, bool $default = false ) {
		return array(
			'label'   => $label,
			'type'    => 'yes_no_button',
			'options' => array(
				'off' => $false_key ?? __( 'No', 'divi-bbpress' ),
				'on'  => $true_key ?? __( 'Yes', 'divi-bbpress' ),
			),
			'default' => $default ? 'on' : 'off',
		);
	}

	public static function select( string $label, array $options, ?string $default = null ) {
		if ( is_null( $default ) ) {
			$default = array_keys( $options )[0];
		}

		return array(
			'label'   => $label,
			'type'    => 'select',
			'options' => $options,
			'default' => $default,
		);
	}
}
