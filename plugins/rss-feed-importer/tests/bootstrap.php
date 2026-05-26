<?php

// Passing "null" makes the function return it's first argument.
Brain\Monkey\Functions\stubs(
    [
        'get_bloginfo'         => static function ( $show ) {
            switch ( $show ) {
                case 'charset':
                    return 'UTF-8';
                case 'language':
                    return 'English';
            }

            return $show;
        },
        'is_multisite'         => static function () {
            if ( \defined( 'WP_TESTS_MULTISITE' ) ) {
                return (bool) \WP_TESTS_MULTISITE;
            }

            return false;
        },
        'mysql2date'           => static function ( $format, $date ) {
            return $date;
        },
        'number_format_i18n'   => null,
        'sanitize_text_field'  => null,
        'site_url'             => 'https://www.example.org',
        'wp_kses'         => null,
        'wp_kses_post'         => null,
        'wp_parse_args'        => static function ( $args, $defaults ) {
            return \array_merge( $defaults, $args );
        },
        'wp_strip_all_tags'    => static function ( $text, $remove_breaks = false ) {
            $text = \preg_replace( '@<(script|style)[^>]*?>.*?</\\1>@si', '', $text );
            $text = \strip_tags( $text );

            if ( $remove_breaks ) {
                $text = \preg_replace( '/[\r\n\t ]+/', ' ', $text );
            }

            return \trim( $text );
        },
        'wp_slash'             => null,
        'wp_unslash'           => static function ( $value ) {
            return \is_string( $value ) ? \stripslashes( $value ) : $value;
        },
        'wp_parse_url' => static function (...$args) {
            return parse_url( ...$args );
        },
        'add_query_arg' => static function ( ...$args ) {
            if ( is_array( $args[0] ) ) {
                if ( count( $args ) < 2 || false === $args[1] ) {
                    $uri = $_SERVER['REQUEST_URI'];
                } else {
                    $uri = $args[1];
                }
            } else {
                if ( count( $args ) < 3 || false === $args[2] ) {
                    $uri = $_SERVER['REQUEST_URI'];
                } else {
                    $uri = $args[2];
                }
            }
        
            $frag = strstr( $uri, '#' );
            if ( $frag ) {
                $uri = substr( $uri, 0, -strlen( $frag ) );
            } else {
                $frag = '';
            }
        
            if ( 0 === stripos( $uri, 'http://' ) ) {
                $protocol = 'http://';
                $uri      = substr( $uri, 7 );
            } elseif ( 0 === stripos( $uri, 'https://' ) ) {
                $protocol = 'https://';
                $uri      = substr( $uri, 8 );
            } else {
                $protocol = '';
            }
        
            if ( str_contains( $uri, '?' ) ) {
                list( $base, $query ) = explode( '?', $uri, 2 );
                $base                .= '?';
            } elseif ( $protocol || ! str_contains( $uri, '=' ) ) {
                $base  = $uri . '?';
                $query = '';
            } else {
                $base  = '';
                $query = $uri;
            }
        
            wp_parse_str( $query, $qs );
            $qs = urlencode_deep( $qs ); // This re-URL-encodes things that were already in the query string.
            if ( is_array( $args[0] ) ) {
                foreach ( $args[0] as $k => $v ) {
                    $qs[ $k ] = $v;
                }
            } else {
                $qs[ $args[0] ] = $args[1];
            }
        
            foreach ( $qs as $k => $v ) {
                if ( false === $v ) {
                    unset( $qs[ $k ] );
                }
            }
        
            $ret = build_query( $qs );
            $ret = trim( $ret, '?' );
            $ret = preg_replace( '#=(&|$)#', '$1', $ret );
            $ret = $protocol . $base . $ret . $frag;
            $ret = rtrim( $ret, '?' );
            $ret = str_replace( '?#', '#', $ret );
            return $ret;
        },
        'wp_parse_str' => static fn ( ...$args ) => parse_str( ...$args ),
        'urlencode_deep' => static fn ( $value ) => array_map( 'urldecode', $value ?? [] ),
        'build_query' => static function ( $data ) {
            $ret = [];
            foreach ( $data as $k => $v ) {
                $ret[] = "$k=$v";
            }
            return implode( '&', $ret );
        },
        'sanitize_key' => static function ($key) {
            $sanitized_key = '';

        	if ( is_scalar( $key ) ) {
                $sanitized_key = strtolower( $key );
                $sanitized_key = preg_replace( '/[^a-z0-9_\-]/', '', $sanitized_key );
            }
            
            return $sanitized_key;
        }
    ]
);