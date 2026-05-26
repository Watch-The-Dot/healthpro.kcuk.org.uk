<?php

namespace WatchTheDot\Plugins\DivibbPress\Module\Forums;

use WatchTheDot\Plugins\DivibbPress\Module\BBPressShortcode;
use WatchTheDot\Plugins\DivibbPress\Settings\Fields;
use WatchTheDot\Plugins\DivibbPress\Settings\SettingsBuilder;

class SingleForm extends BBPressShortcode {

	public $slug       = 'divi_bbpress_bbp-single-forum';
	public $vb_support = 'partial';

	public function __construct() {
		parent::__construct( 'bbp-single-forum' );
	}

	public function init() {
		parent::init();

		$this->name = 'Forum';
	}

	public function get_fields() {
		$builder = ( new SettingsBuilder() )
			->add(
				array(
					'use_current_template' => array_merge(
						Fields::yes_no_button( __( 'Use Current Page', 'divi-bbpress' ), null, null, true, ),
						[]
					),
				)
			)
			->tap(
				fn ( SettingsBuilder $b ) =>
				$b->if( array( 'use_current_template' => 'off' ) )
					->add(
						array(
							'forum_id' => array_merge(
								Fields::select(
									__( 'Forum', 'divi-bbpress' ),
									$this->get_forum_options(),
								),
								[]
							),
						)
					)
			);

		return $builder->build();
	}

	private function get_forum_options() {
		$forums = get_posts(
			array(
				'post_type'   => 'forum',
				'numberposts' => -1,
			)
		);

		return array_column( $forums, 'post_title', 'ID' );
	}

	protected function get_shortcode_attributes(): array {
		$id = null;

		if ( ! et_builder_is_frontend() && 'on' === $this->props['use_current_template'] ) {
			// We are in the visual builder and use current is on, we will just use the first forum
			$id = array_keys( $this->get_forum_options() )[0];
		} elseif ( 'on' === $this->props['use_current_template'] ) {
			$id = get_queried_object_id();
		} else {
			$id = $this->parse_integer_property( 'forum_id' );
		}

		return array(
			'id' => $id,
		);
	}
}

return new SingleForm();
