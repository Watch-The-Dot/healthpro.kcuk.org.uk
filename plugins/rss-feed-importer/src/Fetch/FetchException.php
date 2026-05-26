<?php

namespace WatchTheDot\Plugins\RSSImporter\Fetch;

use RuntimeException;
use WP_Error;

class FetchException extends RuntimeException {
    public function __construct(
        string $message,
        protected readonly ?WP_Error $wp_error = null
    ) {
        if ( ! is_null( $wp_error ) ) {
            $message .= "({$wp_error->get_error_message()})";
        }

        parent::__construct( $message );
    }
}