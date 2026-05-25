<?php

namespace WatchTheDot\Plugins\DivibbPress\Module\Others;

use WatchTheDot\Plugins\DivibbPress\Module\BBPressShortcode;

class Stats extends BBPressShortcode {

	public $slug       = 'divi_bbpress_bbp-stats';
	public $vb_support = 'partial';

	public function __construct() {
		parent::__construct( 'bbp-stats' );
	}

	public function init() {
		parent::init();

		$this->name = 'Forum Statistics';
	}
}

return new Stats();
