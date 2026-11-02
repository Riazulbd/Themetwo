<?php
/**
 * Cache-friendly view counter.
 *
 * Pages stay fully cacheable (Cloudflare / page cache): the count is sent by a
 * tiny deferred JS beacon to a REST endpoint, once per post per browser session.
 * Stores a lifetime total (_np_views) and today's count (_np_day + _np_day_views).
 * "Most read" is cached in a transient for 10 minutes.
 *
 * Disable with: add_filter( 'np_enable_view_counter', '__return_false' );
 *
 * @package Narayanganj_Press
 */

defined( 'ABSPATH' ) || exit;

/** Is the counter enabled? (Off automatically when Jetpack Stats / WP Statistics handle it via filter.) */
function np_views_enabled() {
	return (bool) apply_filters( 'np_enable_view_counter', true );
}

add_action( 'rest_api_init', function () {
	if ( ! np_views_enabled() ) {
		return;
	}
	register_rest_route( 'np/v1', '/view', array(
		'methods'             => 'POST',
		'permission_callback' => '__return_true', // Public by design; input is validated below.
		'args'                => array(
			'id' => array(
				'required'          => true,
				'sanitize_callback' => 'absint',
			),
		),
		'callback'            => 'np_views_record',
	) );
} );

/**
 * Record one view.
 *
 * @param WP_REST_Request $req Request.
 * @return WP_REST_Response
 */
function np_views_record( $req ) {
	$id = (int) $req['id'];
	if ( ! $id || 'post' !== get_post_type( $id ) || 'publish' !== get_post_status( $id ) ) {
		return new WP_REST_Response( array( 'ok' => false ), 400 );
	}
	$ua = isset( $_SERVER['HTTP_USER_AGENT'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) ) : '';
	if ( '' === $ua || preg_match( '/bot|crawl|spider|slurp|lighthouse|headless/i', $ua ) ) {
		return new WP_REST_Response( array( 'ok' => true ), 200 );
	}
	$today = wp_date( 'Ymd' );
	update_post_meta( $id, '_np_views', (int) get_post_meta( $id, '_np_views', true ) + 1 );
	if ( get_post_meta( $id, '_np_day', true ) !== $today ) {
		update_post_meta( $id, '_np_day', $today );
		update_post_meta( $id, '_np_day_views', 1 );
	} else {
		update_post_meta( $id, '_np_day_views', (int) get_post_meta( $id, '_np_day_views', true ) + 1 );
	}
	$res = new WP_REST_Response( array( 'ok' => true ), 200 );
	$res->header( 'Cache-Control', 'no-store' );
	return $res;
}

if ( ! function_exists( 'np_most_read_posts' ) ) {
	/**
	 * Today's most-read posts, topped up with the last 3 days' totals, then latest.
	 *
	 * @param int $count Number of posts.
	 * @return WP_Post[]
	 */
	function np_most_read_posts( $count = 5 ) {
		$key = 'np_most_read_' . $count . '_' . wp_date( 'Ymd' );
		$ids = get_transient( $key );
		if ( false === $ids ) {
			$base = array(
				'post_type'           => 'post',
				'posts_per_page'      => $count,
				'fields'              => 'ids',
				'no_found_rows'       => true,
				'ignore_sticky_posts' => true,
				'orderby'             => 'meta_value_num',
				'order'               => 'DESC',
			);
			$ids = get_posts( $base + array(
				'meta_key'   => '_np_day_views', // phpcs:ignore WordPress.DB.SlowDBQuery
				'meta_query' => array( array( 'key' => '_np_day', 'value' => wp_date( 'Ymd' ) ) ), // phpcs:ignore WordPress.DB.SlowDBQuery
			) );
			if ( count( $ids ) < $count ) {
				$ids = array_merge( $ids, get_posts( array_merge( $base, array(
					'posts_per_page' => $count - count( $ids ),
					'meta_key'       => '_np_views', // phpcs:ignore WordPress.DB.SlowDBQuery
					'post__not_in'   => $ids,
					'date_query'     => array( array( 'after' => '3 days ago' ) ),
				) ) ) );
			}
			if ( count( $ids ) < $count ) {
				$ids = array_merge( $ids, get_posts( array(
					'posts_per_page'      => $count - count( $ids ),
					'fields'              => 'ids',
					'post__not_in'        => $ids,
					'no_found_rows'       => true,
					'ignore_sticky_posts' => true,
				) ) );
			}
			$ids = array_map( 'intval', $ids );
			set_transient( $key, $ids, 10 * MINUTE_IN_SECONDS );
		}
		return $ids ? array_values( array_filter( array_map( 'get_post', $ids ) ) ) : array();
	}
}

/** Bust the cache when a post is published/updated. */
add_action( 'save_post_post', function () {
	foreach ( array( 5, 10 ) as $n ) {
		delete_transient( 'np_most_read_' . $n . '_' . wp_date( 'Ymd' ) );
	}
} );
