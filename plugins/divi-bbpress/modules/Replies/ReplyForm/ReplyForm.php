<?php

namespace WatchTheDot\Plugins\DivibbPress\Module\Replies;

use WatchTheDot\Plugins\DivibbPress\Module\BBPressShortcode;

class ReplyForm extends BBPressShortcode {

	public $slug       = 'divi_bbpress_bbp-reply-form';
	public $vb_support = 'partial';

	public function __construct() {
		parent::__construct( 'bbp-reply-form' );
	}

	public function init() {
		parent::init();

		$this->name = 'Reply Form';
	}
}

return new ReplyForm();
