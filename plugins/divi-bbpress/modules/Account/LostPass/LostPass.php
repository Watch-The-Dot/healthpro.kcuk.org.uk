<?php

namespace WatchTheDot\Plugins\DivibbPress\Module\Account;

use WatchTheDot\Plugins\DivibbPress\Module\BBPressShortcode;

class LostPass extends BBPressShortcode {

	public $slug       = 'divi_bbpress_bbp-lost-pass';
	public $vb_support = 'partial';

	public function __construct() {
		parent::__construct( 'bbp-lost-pass' );
	}

	public function init() {
		parent::init();

		$this->name = 'Lost Password Form';
	}
	
	protected function do_shortcode( array $parameters = array(), ?string $content = null ) {
	    ob_start();
	    ?>
	    <div id='bbpress-forums'>
	        <?php do_action( 'bbp_template_notices' ); ?>
	        <?php echo do_shortcode( $this->create_shortcode( $parameters, $content ) ); ?>
        </div>
	    <?php
	    return ob_get_clean();
	}
}

return new LostPass();
