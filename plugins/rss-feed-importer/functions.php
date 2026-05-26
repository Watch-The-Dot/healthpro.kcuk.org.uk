<?php

namespace WatchTheDot\Plugins\RSSImporter;

function array_kmap( callable $callback, array $array ) {
	return array_combine(
		array_keys( $array ),
		array_map(
			$callback,
			array_keys( $array ),
			array_values( $array )
		)
	);
}

/**
 * @template T
 * @param T $value
 * @param callable(T): T $callable
 * @return T
 */
function tap( $value, callable $callback ) {
	$callback( $value );
	return $value;
}

function join_with_last( array $array, string $joiner, ?string $last = null ) {
	if ( is_null( $last ) ) {
		return implode( $joiner, $array );
	}

	if ( count( $array ) === 1 ) {
		return $array[0];
	}

	$last_element = array_pop( $array );
	return implode( $joiner, $array ) . $last . $last_element;
}

/**
 * @template TReturn
 * @param mixed $value
 * @param \Closure(): TReturn $callable
 * @return ($value is null ? null : TReturn)
 */
function if_not_null( $value, \Closure $callable ) {
	if ( is_null( $value ) ) {
		return null;
	}

	return $callable();
}