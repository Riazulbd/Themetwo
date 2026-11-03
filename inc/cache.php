<?php
/**
 * Small cache helpers.
 *
 * np_swr() is "stale while revalidate" for transients: when a cached value expires, one
 * request rebuilds it while every other request keeps getting the previous value. This
 * stops a burst of visitors from all running the same expensive query at the same moment.
 *
 * @package Khobor_Press
 */

defined( 'ABSPATH' ) || exit;

/**
 * Try to take a short-lived lock (best effort, 30 seconds).
 *
 * @param string $key Lock name.
 * @return bool True when this request now holds the lock.
 */
function np_lock( $key ) {
	$name = 'np_lock_' . md5( $key );
	if ( wp_using_ext_object_cache() ) {
		return wp_cache_add( $name, 1, 'khobor-press', 30 ); // Atomic in Redis / Memcached.
	}
	if ( add_option( $name, time(), '', 'no' ) ) {
		return true;
	}
	$taken = (int) get_option( $name );
	if ( $taken && $taken < time() - 30 ) { // A crashed request left it behind.
		update_option( $name, time(), false );
		return true;
	}
	return false;
}

/** Release a lock taken with np_lock(). */
function np_unlock( $key ) {
	$name = 'np_lock_' . md5( $key );
	if ( wp_using_ext_object_cache() ) {
		wp_cache_delete( $name, 'khobor-press' );
	} else {
		delete_option( $name );
	}
}

/**
 * Cached value with stale-while-revalidate behaviour.
 *
 * @param string   $key   Transient name.
 * @param int      $ttl   Seconds the value counts as fresh.
 * @param callable $build Builds the value.
 * @return mixed
 */
function np_swr( $key, $ttl, $build ) {
	$c     = get_transient( $key );
	$known = is_array( $c ) && array_key_exists( 'v', $c ) && isset( $c['t'] );
	if ( $known && $c['t'] + $ttl > time() ) {
		return $c['v'];
	}
	$locked = np_lock( $key );
	if ( $known && ! $locked ) {
		return $c['v']; // Someone else is rebuilding: serve the old value.
	}
	$v = call_user_func( $build );
	set_transient( $key, array( 'v' => $v, 't' => time() ), DAY_IN_SECONDS );
	if ( $locked ) {
		np_unlock( $key );
	}
	return $v;
}

/** Mark a np_swr() value as stale so the next request refreshes it (others keep the old value meanwhile). */
function np_swr_expire( $key ) {
	$c = get_transient( $key );
	if ( is_array( $c ) && isset( $c['t'] ) ) {
		$c['t'] = 0;
		set_transient( $key, $c, DAY_IN_SECONDS );
	}
}
