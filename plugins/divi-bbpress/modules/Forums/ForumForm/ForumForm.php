<?php

namespace WatchTheDot\Plugins\DivibbPress\Module\Forums;

use WatchTheDot\Plugins\DivibbPress\Module\BBPressShortcode;

class ForumForm extends BBPressShortcode {

	public $slug       = 'divi_bbpress_bbp-forum-form';
	public $vb_support = 'partial';

	public function __construct() {
		parent::__construct( 'bbp-forum-form' );
	}

	public function init() {
		parent::init();

		$this->name = '[Forum] Form';
	}
}

return new ForumForm();
