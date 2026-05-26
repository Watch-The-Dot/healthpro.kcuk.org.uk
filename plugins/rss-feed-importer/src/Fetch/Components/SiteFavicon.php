<?php

namespace WatchTheDot\Plugins\RSSImporter\Fetch\Components;

use WatchTheDot\Plugins\RSSImporter\Fetch\Media\FetchedMedia;

class SiteFavicon {
    public static function invoke( string $url ) {
		if ( empty( $url ) ) {
			return null;
		}

        $host     = wp_parse_url( $url, PHP_URL_HOST );
		$link_uri = "https://{$host}";

		$favicon_url = add_query_arg(
			array(
				'client'        => 'SOCIAL',
				'type'          => 'FAVICON',
				'fallback_opts' => 'TYPE,SIZE,URL',
				'url'           => $link_uri,
				'size'          => 64,
			),
			'https://t2.gstatic.com/faviconV2'
		);

        return new FetchedMedia( $favicon_url, sanitize_key( $host ) . "-favicon" );
    }
}