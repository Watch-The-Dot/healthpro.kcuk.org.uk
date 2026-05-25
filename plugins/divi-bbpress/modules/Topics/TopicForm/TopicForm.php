<?php

namespace WatchTheDot\Plugins\DivibbPress\Module\Topics;

use WatchTheDot\Plugins\DivibbPress\Module\BBPressShortcode;

class TopicForm extends BBPressShortcode {

	public $slug       = 'divi_bbpress_bbp-topic-form';
	public $vb_support = 'partial';

	public function __construct() {
		parent::__construct( 'bbp-topic-form' );
	}

	public function init() {
		parent::init();

		$this->name = '[Topic] Form';
	}
}

return new TopicForm();
