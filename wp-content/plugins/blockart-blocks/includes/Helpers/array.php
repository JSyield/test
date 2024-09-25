<?php
/**
 * Array helper functions.
 *
 * @package BlockArt
 */

/**
 * Determine whether the given value is array accessible.
 *
 * @param mixed $value Value to check.
 * @return bool
 */
function blockart_array_accessible( $value ): bool {
	return is_array( $value ) || $value instanceof ArrayAccess;
}

/**
 * Add an element to an array using "dot" notation if it doesn't exist.
 *
 * @param array  $array_value Array to add.
 * @param string $key Key.
 * @param mixed  $value Value.
 * @return array
 */
function blockart_array_add( array $array_value, string $key, $value ): array {
	if ( is_null( blockart_array_get( $array_value, $key ) ) ) {
		blockart_array_set( $array_value, $key, $value );
	}
	return $array_value;
}

/**
 * Collapse an array of arrays into a single array.
 *
 * @param iterable $array_value Array or Object.
 * @return array
 */
function blockart_array_collapse( $array_value ) {
	$results = [];
	foreach ( $array_value as $values ) {
		if ( ! is_array( $values ) ) {
			continue;
		}
		$results[] = $values;
	}
	return array_merge( [], ...$results );
}

/**
 * Cross join the given arrays, returning all possible permutations.
 *
 * @return array
 */
function blockart_array_cross_join(): array {
	$arrays  = func_get_args();
	$results = [ [] ];
	foreach ( $arrays as $index => $array ) {
		$append = [];
		foreach ( $results as $result ) {
			foreach ( $array as $item ) {
				$result[ $index ] = $item;

				$append[] = $result;
			}
		}
		$results = $append;
	}
	return $results;
}

/**
 * Divide an array into two arrays. One with keys and the other with values.
 *
 * @param array $array_value Array.
 * @return array
 */
function blockart_array_divide( array $array_value ) {
	return [ array_keys( $array_value ), array_values( $array_value ) ];
}

/**
 * Flatten a multi-dimensional associative array with dots.
 *
 * @param iterable $array_value Array or object.
 * @param string   $prepend Prepend key.
 * @return array
 */
function blockart_array_dot( $array_value, $prepend = '' ) {
	$results = [];
	foreach ( $array_value as $key => $value ) {
		if ( is_array( $value ) && ! empty( $value ) ) {
			$results = array_merge( $results, blockart_array_dot( $value, $prepend . $key . '.' ) );
		} else {
			$results[ $prepend . $key ] = $value;
		}
	}
	return $results;
}

/**
/**
 * Converts a dot notation array to nested array
 *
 * Takes an array in dot notation like 'user.name'
 * and converts it to nested array format.
 *
 * @param array $array_value The dot notation input array
 *
 * @return array The converted nested array
 */
function blockart_array_undot( $array_value ) {
	$results = [];
	foreach ( $array_value as $key => $value ) {
		blockart_array_set( $results, $key, $value );
	}
	return $results;
}

/**
 * Get all of the given array except for a specified array of keys.
 *
 * @param array        $array_value Array.
 * @param array|string $keys Keys.
 * @return array
 */
function blockart_array_except( array $array_value, $keys ): array {
	blockart_array_forget( $array_value, $keys );
	return $array_value;
}

/**
 * Determine if the given key exists in the provided array.
 *
 * @param ArrayAccess|array $array_value Array.
 * @param string|int        $key Key.
 * @return bool
 */
function blockart_array_exists( $array_value, $key ): bool {
	if ( $array_value instanceof ArrayAccess ) {
		return $array_value->offsetExists( $key );
	}
	return array_key_exists( $key, $array_value );
}

/**
 * Return the first element in an array passing a given truth test.
 *
 * @param iterable      $array_value Array.
 * @param callable|null $callback Callback.
 * @param mixed         $default_value Default.
 * @return mixed
 */
function blockart_array_first( $array_value, $callback = null, $default_value = null ) {
	if ( is_null( $callback ) ) {
		if ( empty( $array_value ) ) {
			return blockart_value( $default_value );
		}
		foreach ( $array_value as $item ) {
			return $item;
		}
	}
	foreach ( $array_value as $key => $value ) {
		if ( $callback( $value, $key ) ) {
			return $value;
		}
	}
	return blockart_value( $default_value );
}

/**
 * Return the last element in an array passing a given truth test.
 *
 * @param array         $array_value Array.
 * @param callable|null $callback Callable.
 * @param mixed         $default_value Default.
 * @return mixed
 */
function blockart_array_last( array $array_value, callable $callback = null, $default_value = null ) {
	if ( is_null( $callback ) ) {
		return empty( $array_value ) ? blockart_value( $default_value ) : end( $array_value );
	}
	return blockart_array_first( array_reverse( $array_value, true ), $callback, $default_value );
}

/**
 * Flatten a multi-dimensional array into a single level.
 *
 * @param iterable $array_value Array or Object.
 * @param int      $depth Depth.
 * @return array
 */
function blockart_array_flatten( $array_value, $depth = INF ): array {
	$result = [];
	foreach ( $array_value as $item ) {
		if ( ! is_array( $item ) ) {
			$result[] = $item;
		} else {
			$values = 1 === $depth
				? array_values( $item )
				: blockart_array_flatten( $item, $depth - 1 );
			foreach ( $values as $value ) {
				$result[] = $value;
			}
		}
	}
	return $result;
}

/**
 * Remove one or many array items from a given array using "dot" notation.
 *
 * @param array        $array_value Array.
 * @param array|string $keys Keys.
 * @return void
 */
function blockart_array_forget( array &$array_value, $keys ) {
	$original = &$array_value;
	$keys     = (array) $keys;
	if ( count( $keys ) === 0 ) {
		return;
	}
	foreach ( $keys as $key ) {
		if ( blockart_array_exists( $array_value, $key ) ) {
			unset( $array_value[ $key ] );
			continue;
		}
		$parts       = explode( '.', $key );
		$array_value = &$original;
		$count       = count( $parts );
		while ( $count > 1 ) {
			$part  = array_shift( $parts );
			$count = count( $parts );
			if ( isset( $array_value[ $part ] ) && is_array( $array_value[ $part ] ) ) {
				$array_value = &$array_value[ $part ];
			} else {
				continue 2;
			}
		}
		unset( $array_value[ array_shift( $parts ) ] );
	}
}

/**
 * Get an item from an array using "dot" notation.
 *
 * @param ArrayAccess|array $array_value Array.
 * @param string|int|null   $key Key.
 * @param mixed             $default_value Default.
 * @return mixed
 */
function blockart_array_get( $array_value, $key, $default_value = null ) {
	if ( ! blockart_array_accessible( $array_value ) ) {
		return blockart_value( $default_value );
	}
	if ( is_null( $key ) ) {
		return $array_value;
	}
	if ( blockart_array_exists( $array_value, $key ) ) {
		return $array_value[ $key ];
	}
	if ( strpos( $key, '.' ) === false ) {
		return $array_value[ $key ] ?? blockart_value( $default_value );
	}
	foreach ( explode( '.', $key ) as $segment ) {
		if ( blockart_array_accessible( $array_value ) && blockart_array_exists( $array_value, $segment ) ) {
			$array_value = $array_value[ $segment ];
		} else {
			return blockart_value( $default_value );
		}
	}
	return $array_value;
}

/**
 * Check if an item or items exist in an array using "dot" notation.
 *
 * @param ArrayAccess|array $array_value Array.
 * @param string|array      $keys Keys.
 * @return bool
 */
function blockart_array_has( $array_value, $keys ): bool {
	$keys = (array) $keys;
	if ( ! $array_value || [] === $keys ) {
		return false;
	}
	foreach ( $keys as $key ) {
		$sub_key_array = $array_value;
		if ( blockart_array_exists( $array_value, $key ) ) {
			continue;
		}
		foreach ( explode( '.', $key ) as $segment ) {
			if ( blockart_array_accessible( $sub_key_array ) && blockart_array_exists( $sub_key_array, $segment ) ) {
				$sub_key_array = $sub_key_array[ $segment ];
			} else {
				return false;
			}
		}
	}
	return true;
}

/**
 * Determine if any of the keys exist in an array using "dot" notation.
 *
 * @param ArrayAccess|array $array_value Array.
 * @param string|array      $keys Keys.
 * @return bool
 */
function blockart_array_has_any( $array_value, $keys ): bool {
	if ( is_null( $keys ) ) {
		return false;
	}
	$keys = (array) $keys;
	if ( ! $array_value ) {
		return false;
	}
	if ( [] === $keys ) {
		return false;
	}
	foreach ( $keys as $key ) {
		if ( blockart_array_has( $array_value, $key ) ) {
			return true;
		}
	}
	return false;
}

/**
 * Determines if an array is associative.
 *
 * An array is "associative" if it doesn't have sequential numerical keys beginning with zero.
 *
 * @param array $array_value Array.
 * @return bool
 */
function blockart_array_is_assoc( array $array_value ): bool {
	$keys = array_keys( $array_value );
	return array_keys( $keys ) !== $keys;
}

/**
 * Get a subset of the items from the given array.
 *
 * @param array        $array_value Array.
 * @param array|string $keys Keys.
 *
 * @return array
 */
function blockart_array_only( $array_value, $keys ) {
	return array_intersect_key( $array_value, array_flip( (array) $keys ) );
}

/**
 * Push an item onto the beginning of an array.
 *
 * @param array $array_value Array.
 * @param mixed $value Value.
 * @param mixed $key Key.
 * @return array
 */
function blockart_array_prepend( $array_value, $value, $key = null ) {
	if ( func_num_args() === 2 ) {
		array_unshift( $array_value, $value );
	} else {
		$array_value = [ $key => $value ] + $array_value;
	}
	return $array_value;
}

/**
 * Get a value from the array, and remove it.
 *
 * @param array  $array_value Array.
 * @param string $key Key.
 * @param mixed  $default_value Default.
 * @return mixed
 */
function blockart_array_pull( array &$array_value, string $key, $default_value = null ) {
	$value = blockart_array_get( $array_value, $key, $default_value );
	blockart_array_forget( $array_value, $key );
	return $value;
}

/**
 * Convert the array into a query string.
 *
 * @param array $array_value Array.
 * @return string
 */
function blockart_array_query( array $array_value ): string {
	return http_build_query( $array_value, '', '&', PHP_QUERY_RFC3986 );
}

/**
 * Get one or a specified number of random values from an array.
 *
 * @param array      $array_value Array.
 * @param bool|false $preserve_keys Preserve keys.
 * @param int|null   $number Number.
 * @return mixed
 * @throws InvalidArgumentException Invalid argument exception.
 */
function blockart_array_random( $array_value, $preserve_keys, $number = null ) {
	$requested = is_null( $number ) ? 1 : $number;
	$count     = count( $array_value );

	if ( $requested > $count ) {
		throw new InvalidArgumentException(
			esc_html( "You requested {$requested} items, but there are only {$count} items available." )
		);
	}
	if ( is_null( $number ) ) {
		return $array_value[ array_rand( $array_value ) ];
	}
	if ( 0 === (int) $number ) {
		return [];
	}
	$keys    = array_rand( $array_value, $number );
	$results = [];
	if ( $preserve_keys ) {
		foreach ( (array) $keys as $key ) {
			$results[ $key ] = $array_value[ $key ];
		}
	} else {
		foreach ( (array) $keys as $key ) {
			$results[] = $array_value[ $key ];
		}
	}
	return $results;
}

/**
 * Set an array item to a given value using "dot" notation.
 *
 * If no key is given to the method, the entire array will be replaced.
 *
 * @param array       $array_value Array.
 * @param string|null $key Key.
 * @param mixed       $value Value.
 * @return array
 */
function blockart_array_set( &$array_value, $key, $value ): array {
	if ( is_null( $key ) ) {
		$array_value = $value;
		return $array_value;
	}
	$keys = explode( '.', $key );
	foreach ( $keys as $i => $key ) {
		if ( count( $keys ) === 1 ) {
			break;
		}
		unset( $keys[ $i ] );
		if ( ! isset( $array_value[ $key ] ) || ! is_array( $array_value[ $key ] ) ) {
			$array_value[ $key ] = [];
		}
		$array_value = &$array_value[ $key ];
	}
	$array_value[ array_shift( $keys ) ] = $value;
	return $array_value;
}

/**
 * Shuffle the given array and return the result.
 *
 * @param array    $array_value Array.
 * @param int|null $seed Seed.
 * @return array
 */
function blockart_array_shuffle( array $array_value, int $seed = null ): array {
	if ( is_null( $seed ) ) {
		shuffle( $array_value );
	} else {
		wp_rand( $seed );
		shuffle( $array_value );
		wp_rand();
	}

	return $array_value;
}

/**
 * Recursively sort an array by keys and values.
 *
 * @param array $array_value Array.
 * @param int   $options Options.
 * @param bool  $descending Descending.
 *
 * @return array
 */
function blockart_array_sort_recursive( array $array_value, int $options = SORT_REGULAR, bool $descending = true ): array {
	foreach ( $array_value as &$value ) {
		if ( is_array( $value ) ) {
			$value = blockart_array_sort_recursive( $value, $options, $descending );
		}
	}
	if ( blockart_array_is_assoc( $array_value ) ) {
		$descending
			? krsort( $array_value, $options )
			: ksort( $array_value, $options );
	} else {
		$descending
			? rsort( $array_value, $options )
			: sort( $array_value, $options );
	}
	return $array_value;
}

/**
 * Conditionally compile classes from an array into a CSS class list.
 *
 * @param array $array_value Array.
 * @return string
 */
function blockart_array_to_css_classes( $array_value ) {
	$class_list = blockart_array_wrap( $array_value );
	$classes    = [];
	foreach ( $class_list as $class => $constraint ) {
		if ( is_numeric( $class ) ) {
			$classes[] = $constraint;
		} elseif ( $constraint ) {
			$classes[] = $class;
		}
	}
	return implode( ' ', $classes );
}

/**
 * Filter the array using the given callback.
 *
 * @param array    $array_value Array.
 * @param callable $callback Callable.
 * @return array
 */
function blockart_array_where( $array_value, $callback ): array {
	return array_filter( $array_value, $callback, ARRAY_FILTER_USE_BOTH );
}

/**
 * If the given value is not an array and not null, wrap it in one.
 *
 * @param mixed $value Array to wrap.
 * @return array
 */
function blockart_array_wrap( $value ): array {
	if ( is_null( $value ) ) {
		return [];
	}
	return is_array( $value ) ? $value : [ $value ];
}

/**
 * Get the first element of an array. Useful for method chaining.
 *
 * @param array $array_value Array.
 * @return mixed
 */
function blockart_head( $array_value ) {
	return reset( $array_value );
}

/**
 * Get the last element from an array.
 *
 * @param array $array_value Array.
 * @return mixed
 */
function blockart_last( array $array_value ) {
	return end( $array_value );
}

/**
 * Convert Json into Array.
 *
 * @param string $json JSON.
 * @return array
 */
function blockart_to_array( $json ) {
	return (array) json_decode( $json, true );
}

/**
 * Return the default value of the given value.
 *
 * @param mixed $value Value.
 * @return mixed
 */
function blockart_value( $value ) {
	return $value instanceof Closure ? $value() : $value;
}

/**
 * Combine keys with same value.
 *
 * @param array  $array_value Array of data.
 * @param string $separator Separator.
 * @return array
 */
function blockart_array_combine_keys( array $array_value, string $separator = ',' ): array {
	$result = array();

	foreach ( $array_value as $key => $value ) {
		if ( ! is_array( $value ) ) {
			continue;
		}

		$found = false;

		foreach ( $result as $k => $v ) {
			if ( empty( array_diff_assoc( $v, $value ) ) && empty( array_diff_assoc( $value, $v ) ) ) {
				$result[ "$key$separator$k" ] = $value;
				unset( $result[ $k ] );
				$found = true;
				break;
			}
		}

		if ( ! $found ) {
			$result[ $key ] = $value;
		}
	}

	return $result;
}

/**
 * Parse args recursively.
 *
 * @param array $a Default args.
 * @param array $b Args.
 *
 * @return array
 */
function blockart_parse_args( &$a, $b ) {
	$a      = (array) $a;
	$b      = (array) $b;
	$result = $b;

	foreach ( $a as $k => &$v ) {
		if ( is_array( $v ) && isset( $result[ $k ] ) ) {
			$result[ $k ] = blockart_parse_args( $v, $result[ $k ] );
		} else {
			$result[ $k ] = $v;
		}
	}

	return $result;
}


/**
 * Convert array to html attribute string.
 *
 * @param mixed $array_value Array to convert.
 *
 * @return string
 */
function blockart_array_to_html_attributes( $array_value ) {
	$attributes = [];

	foreach ( $array_value as $key => $value ) {
		if ( is_null( $value ) ) {
			continue;
		}
		$key          = htmlentities( $key, ENT_QUOTES, 'UTF-8' );
		$value        = htmlentities( $value, ENT_QUOTES, 'UTF-8' );
		$attributes[] = "$key=\"$value\"";
	}

	return implode( ' ', $attributes );
}
