<?php

namespace WatchTheDot\Plugins\DivibbPress\Module;

class BBPressShortcode extends BBPressModule {
	protected string $shortcode;

	public function __construct(
		string $shortcode
	) {
		parent::__construct();

		$this->shortcode = $shortcode;
	}

	protected function create_shortcode( array $parameters = array(), ?string $content = null ) {
		$shortcode_parameters = implode(
			' ',
			array_map(
				static fn ( $key, $value ) => sprintf( '%s="%s"', $key, $value ),
				array_keys( $parameters ),
				array_values( $parameters )
			)
		);

		$shortcode_template = is_null( $content ) ? '[%1$s %2$s]' : '[%1$s %2$s]%3$s[/%1$s]';

		return sprintf(
			$shortcode_template,
			$this->shortcode,
			$shortcode_parameters,
			$content
		);
	}

	protected function do_shortcode( array $parameters = array(), ?string $content = null ) {
		return do_shortcode( $this->create_shortcode( $parameters, $content ) );
	}

	public function render( $attrs, $content, $render_slug ) {
		$background_layout = $this->props['background_layout'];
		$this->add_classname(
			array(
				"et_pb_bg_layout_{$background_layout}",
				$this->get_text_orientation_classname(),
			)
		);

		$attributes = array_filter( $this->get_shortcode_attributes(), static fn ( $value ) => ! is_null( $value ) );
		$output     = $this->do_shortcode( $attributes );

		return $this->_render_module_wrapper( $output, $render_slug );
	}

	protected function get_shortcode_attributes(): array {
		return array();
	}
}
