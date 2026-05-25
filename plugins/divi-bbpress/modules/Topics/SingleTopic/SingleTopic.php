<?php

namespace WatchTheDot\Plugins\DivibbPress\Module\Topics;

use WatchTheDot\Plugins\DivibbPress\Module\BBPressShortcode;
use WatchTheDot\Plugins\DivibbPress\Settings\Fields;
use WatchTheDot\Plugins\DivibbPress\Settings\SettingsBuilder;

class SingleForm extends BBPressShortcode {

	public $slug       = 'divi_bbpress_bbp-single-topic';
	public $vb_support = 'partial';

	public function __construct() {
		parent::__construct( 'bbp-single-topic' );
	}

	public function init() {
		parent::init();

		$this->name = 'Topic';
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
							'topic_id' => array_merge(
								Fields::select(
									__( 'Topic', 'divi-bbpress' ),
									$this->get_topic_options(),
								),
								[],
							),
						)
					)
			);

		return $builder->build();
	}

	private function get_topic_options() {
		$topics = get_posts(
			array(
				'post_type'   => 'topic',
				'numberposts' => -1,
			)
		);

		return array_column( $topics, 'post_title', 'ID' );
	}

	protected function get_shortcode_attributes(): array {
		$id = null;

		if ( ! et_builder_is_frontend() && 'on' === $this->props['use_current_template'] ) {
			// We are in the visual builder and use current is on, we will just use the first forum
			$id = array_keys( $this->get_topic_options() )[0];
		} elseif ( 'on' === $this->props['use_current_template'] ) {
			$id = get_queried_object_id();
		} else {
			$id = $this->parse_integer_property( 'topic_id' );
		}

		return array(
			'id' => $id,
		);
	}
}

return new SingleForm();
