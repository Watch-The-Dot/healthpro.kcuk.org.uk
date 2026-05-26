<?php

namespace WatchTheDot\Plugins\DivibbPress\Module\Forums;

use WatchTheDot\Plugins\DivibbPress\Module\BBPressShortcode;

class ForumIndex extends BBPressShortcode {

	public $slug       = 'divi_bbpress_bbp-forum-index';
	public $vb_support = 'partial';

	public function __construct() {
		parent::__construct( 'bbp-forum-index' );
	}

	public function init() {
		parent::init();

		$this->name = 'Forum List';
	}
}

return new ForumIndex();
