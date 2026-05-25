<?php

namespace WatchTheDot\Plugins\DivibbPress\Module;

use ET_Builder_Module;

class BBPressModule extends ET_Builder_Module {
	public function init() {
		$this->folder_name      = 'bbpress';
		$this->main_css_element = '%%order_class%%';
	}

	public function get_settings_modal_toggles() {
		return array(
			'general'  => array(
				'toggles' => array(
					'content'     => esc_html__( 'Content', 'divi-bbpress' ),
					'background'  => esc_html__( 'Background', 'divi-bbpress' ),
					'admin_label' => esc_html__( 'Admin Label', 'divi-bbpress' ),
				),
			),
			'advanced' => array(
				'toggles' => array(
					'icon' => esc_html__( 'Icons', 'divi-bbpress' ),
				),
			),
		);
	}

	public function get_advanced_fields_config() {
		return array(
			'background'     => array(
				'use_background_image' => true,
				'use_background_video' => true,
			),
			'text'           => array(
				'use_background_layout' => true,
				'options'               => array( 'background_layout' => array( 'default' => 'light' ) ),
				'css'                   => array(
					'text_orientation'  => '%%order_class%%',
					'background_layout' => '%%order_class%%',
				),
			),
			'borders'        => array( 'default' => array( 'css' => array( 'main' => '%%order_class%%' ) ) ),
			'box_shadow'     => array( 'default' => array( 'css' => array( 'main' => '%%order_class%%' ) ) ),
			'button'         => array(),
			'filters'        => array(),
			'fonts'          => array(),
			'margin_padding' => array(),
			'max_width'      => array(),
			'animation'      => array(),
		);
	}

	protected function parse_true_false_property( string $property_key, string $true_value = 'true', string $false_value = '' ) {
		return 'on' === $this->props[ $property_key ] ? $true_value : $false_value;
	}

	protected function parse_integer_property( string $property_key, int $default_value = 0 ) {
		$value = $this->props[ $property_key ];

		if ( ! is_numeric( $value ) ) {
			return $default_value;
		}

		return intval( $value );
	}
}
