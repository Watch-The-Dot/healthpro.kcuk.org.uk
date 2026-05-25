<?php

namespace WatchTheDot\Plugins\DivibbPress\Module\Replies;

use WatchTheDot\Plugins\DivibbPress\Module\BBPressShortcode;
use WatchTheDot\Plugins\DivibbPress\Settings\Fields;
use WatchTheDot\Plugins\DivibbPress\Settings\SettingsBuilder;

class SingleReply extends BBPressShortcode {

	public $slug       = 'divi_bbpress_bbp-single-reply';
	public $vb_support = 'partial';

	public function __construct() {
		parent::__construct( 'bbp-single-reply' );
	}

	public function init() {
		parent::init();

		$this->name = 'REply';
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
							'reply_id' => array_merge(
								Fields::select(
									__( 'Reply', 'divi-bbpress' ),
									$this->get_reply_options(),
								),
								[]
							),
						)
					)
			);

		return $builder->build();
	}

	private function get_reply_options() {
		$topics = get_posts(
			array(
				'post_type'   => 'reply',
				'numberposts' => -1,
			)
		);

		return array_column( $topics, 'post_title', 'ID' );
	}

	protected function get_shortcode_attributes(): array {
		$id = null;

		if ( ! et_builder_is_frontend() && 'on' === $this->props['use_current_template'] ) {
			// We are in the visual builder and use current is on, we will just use the first forum
			$id = array_keys( $this->get_reply_options() )[0];
		} elseif ( 'on' === $this->props['use_current_template'] ) {
			$id = get_queried_object_id();
		} else {
			$id = $this->parse_integer_property( 'reply_id' );
		}

		return array(
			'id' => $id,
		);
	}
}

return new SingleReply();
