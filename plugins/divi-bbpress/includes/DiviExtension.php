<?php
declare(strict_types=1);

namespace WatchTheDot\Plugins\DivibbPress;

defined( 'ABSPATH' ) || exit;

class DiviExtension extends \DiviExtension {

	/**
	 * The gettext domain for the extension's translations.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	public $gettext_domain = 'divi-bbpress';

	/**
	 * The extension's WP Plugin name.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	public $name = 'divi-bbpress';

	/**
	 * The extension's version
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	public $version = Plugin::VERSION;

	/**
	 * {@inheritDoc}
	 */
	public function __construct( string $plugin_file ) {
		$this->plugin_dir     = plugin_dir_path( $plugin_file );
		$this->plugin_dir_url = plugin_dir_url( $plugin_file );

		parent::__construct( $this->name, array() );
	}

	/**
	 * {@inheritDoc}
	 */
	public function hook_et_builder_ready() {
		Plugin::instance()->get_modules()->load();
	}
}
