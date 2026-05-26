<?php

namespace WatchTheDot\Plugins\DivibbPress\Module\TopicTags;

use WatchTheDot\Plugins\DivibbPress\Module\BBPressShortcode;

class TopicTags extends BBPressShortcode {

	public $slug       = 'divi_bbpress_bbp-topic-tags';
	public $vb_support = 'partial';

	public function __construct() {
		parent::__construct( 'bbp-topic-tags' );
	}

	public function init() {
		parent::init();

		$this->name = 'Topic Tags Word Cloud';
	}
}

return new TopicTags();
