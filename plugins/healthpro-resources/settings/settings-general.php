<?php
add_filter(
	'wpsf_register_settings_hp_resources',
	static function ( $wpsf_settings ) {
		// General Settings section.
		$wpsf_settings[] = array(
			'section_id'          => 'order_form',
			'section_title'       => 'Order Form Settings',
			'section_description' => 'Settings relating to the order form',
			'section_order'       => 5,
			'fields'              => array(
				array(
					'id'              => 'delivery_information',
					'title'           => 'Delivery Information',
					'desc'            => 'This is a description.',
					'type'            => 'editor',
					'default'         => '',
					'editor_settings' => array(
						'teeny' => false,
					),
				),
				array(
					'id'        => 'quantity_options',
					'title'     => 'Quantity Options',
					'desc'      => '',
					'type'      => 'group',
					'subfields' => array(
						array(
							'id'          => 'quantity',
							'title'       => 'Quantity',
							'desc'        => '',
							'placeholder' => '',
							'type'        => 'number',
							'default'     => 50,
						),
					),
				),
			),
		);

		return $wpsf_settings;
	}
);
