<?php

namespace WatchTheDot\Plugins\DivibbPress\Module\Topics;

use WatchTheDot\Plugins\DivibbPress\Module\BBPressShortcode;

class TopicIndex extends BBPressShortcode {

	public $slug       = 'divi_bbpress_bbp-topic-index';
	public $vb_support = 'partial';

	public function __construct() {
		parent::__construct( 'bbp-topic-index' );
	}

	public function init() {
		parent::init();

		$this->name = 'Topics';
	}
}

return new TopicIndex();
