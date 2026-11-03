<?php
/**
 * Cache-friendly view counter.
 *
 * Pages stay fully cacheable (Cloudflare / page cache): the count is sent by a tiny
 * deferred JS beacon, once per post per browser session, and never touches the page itself.
 *
 * The beacon posts to the site root (`/?np_view=1`) and is answered very early in the
 * request, right after the theme loads and before WordPress builds its query, post types,
 * widgets or REST server. That makes each count a fraction of the cost of a normal request.
 * The REST route below is kept for clients that already use it.
 *
 * Counts are plain atomic `meta_value + N` updates (no read-then-write race). On a very busy
 * site the Theme Panel can count 1 view in N and add N each time, which keeps totals right on
 * average while cutting the work N-fold.
 *
 * Stores a lifetime total (_np_views) and today's count (_np_day + _np_day_views).
 * "Most read" is cached with stale-while-revalidate (see inc/cache.php).
 *
 * Disable with the Theme Panel, or: add_filter( 'np_enable_view_counter', '__return_false' );
 *
 * @package Khobor_Press
 */

defined( 'ABSPATH' ) || exit;

/** Is the counter enabled? (Turn off in the Theme Panel, or via filter when a stats plugin handles it.) */
function np_views_enabled() {
	return np_opt( 'views_on' ) && (bool) apply_filters( 'np_enable_view_counter', true );
}

/** Today's date as Ymd in the site timezone (does not need the locale object, so it works very early). */
function np_views_today() {
	return ( new DateTimeImmutable( 'now', wp_timezone() ) )->format( 'Ymd' );
}

/** Add $n to a numeric post meta value atomically, creating the row when it is missing. */
function np_views_add( $id, $key, $n ) {
	global $wpdb;
	$done = $wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->postmeta} SET meta_value = meta_value + %d WHERE post_id = %d AND meta_key = %s", $n, $id, $key ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
	if ( ! $done ) {
		$wpdb->insert( $wpdb->postmeta, array( 'post_id' => $id, 'meta_key' => $key, 'meta_value' => $n ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery, WordPress.DB.SlowDBQuery
	}
}

/** Set a post meta value, creating the row when it is missing. */
function np_views_set( $id, $key, $value ) {
	global $wpdb;
	$mid = $wpdb->get_var( $wpdb->prepare( "SELECT meta_id FROM {$wpdb->postmeta} WHERE post_id = %d AND meta_key = %s LIMIT 1", $id, $key ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
	if ( $mid ) {
		$wpdb->update( $wpdb->postmeta, array( 'meta_value' => $value ), array( 'meta_id' => (int) $mid ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
	} else {
		$wpdb->insert( $wpdb->postmeta, array( 'post_id' => $id, 'meta_key' => $key, 'meta_value' => $value ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery, WordPress.DB.SlowDBQuery
	}
}

/**
 * Record views for one post.
 *
 * @param int $id     Post ID.
 * @param int $weight How many views this request stands for (sampling); capped by the Theme Panel setting.
 * @return bool True when counted.
 */
function np_views_count( $id, $weight = 1 ) {
	global $wpdb;
	$id = (int) $id;
	$ua = isset( $_SERVER['HTTP_USER_AGENT'] ) ? (string) $_SERVER['HTTP_USER_AGENT'] : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
	if ( ! $id || '' === $ua || preg_match( '/bot|crawl|spider|slurp|lighthouse|headless|pagespeed/i', $ua ) ) {
		return false; // No database work at all for crawlers.
	}
	$w   = min( max( 1, (int) $weight ), max( 1, (int) np_opt( 'views_sample' ) ) );
	$row = $wpdb->get_row( $wpdb->prepare( "SELECT post_type, post_status FROM {$wpdb->posts} WHERE ID = %d", $id ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
	if ( ! $row || 'post' !== $row->post_type || 'publish' !== $row->post_status ) {
		return false;
	}
	$today = np_views_today();
	np_views_add( $id, '_np_views', $w );
	$day = $wpdb->get_var( $wpdb->prepare( "SELECT meta_value FROM {$wpdb->postmeta} WHERE post_id = %d AND meta_key = '_np_day' LIMIT 1", $id ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
	if ( $day === $today ) {
		np_views_add( $id, '_np_day_views', $w );
	} else {
		np_views_set( $id, '_np_day', $today );
		np_views_set( $id, '_np_day_views', $w );
	}
	wp_cache_delete( $id, 'post_meta' );
	return true;
}

/** The early endpoint: POST /?np_view=1 with id (and w). Answers before WordPress builds the page. */
function np_views_early_endpoint() {
	if ( ! isset( $_GET['np_view'] ) || ! isset( $_SERVER['REQUEST_METHOD'] ) || 'POST' !== $_SERVER['REQUEST_METHOD'] ) { // phpcs:ignore WordPress.Security.NonceVerification
		return;
	}
	if ( np_views_enabled() ) {
		$id = isset( $_POST['id'] ) ? absint( $_POST['id'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification
		$w  = isset( $_POST['w'] ) ? absint( $_POST['w'] ) : 1; // phpcs:ignore WordPress.Security.NonceVerification
		np_views_count( $id, $w );
	}
	header( 'Cache-Control: no-store' );
	status_header( 204 );
	exit;
}
add_action( 'after_setup_theme', 'np_views_early_endpoint', 20 );

add_action( 'rest_api_init', function () {
	if ( ! np_views_enabled() ) {
		return;
	}
	register_rest_route( 'np/v1', '/view', array(
		'methods'             => 'POST',
		'permission_callback' => '__return_true', // Public by design; input is validated in np_views_count().
		'args'                => array(
			'id' => array(
				'required'          => true,
				'sanitize_callback' => 'absint',
			),
		),
		'callback'            => function ( $req ) {
			$ok  = np_views_count( (int) $req['id'], 1 );
			$res = new WP_REST_Response( array( 'ok' => $ok ), 200 );
			$res->header( 'Cache-Control', 'no-store' );
			return $res;
		},
	) );
} );

/**
 * IDs of today's most-read posts, topped up with the last 3 days' totals, then latest.
 *
 * @param int $count Number of posts.
 * @return int[]
 */
function np_most_read_ids( $count ) {
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
		'meta_query' => array( array( 'key' => '_np_day', 'value' => np_views_today() ) ), // phpcs:ignore WordPress.DB.SlowDBQuery
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
	return array_map( 'intval', $ids );
}

if ( ! function_exists( 'np_most_read_posts' ) ) {
	/**
	 * Today's most-read posts.
	 *
	 * @param int $count Number of posts.
	 * @return WP_Post[]
	 */
	function np_most_read_posts( $count = 5 ) {
		$count = (int) $count;
		$ids   = np_swr( 'np_most_read_' . $count . '_' . np_views_today(), 10 * MINUTE_IN_SECONDS, function () use ( $count ) {
			return np_most_read_ids( $count );
		} );
		if ( ! $ids ) {
			return array();
		}
		_prime_post_caches( $ids, false, false ); // One query for all of them instead of one each.
		return array_values( array_filter( array_map( 'get_post', $ids ) ) );
	}
}

/** When a post is published or updated, mark the list stale; the next visitor refreshes it while others keep the old one. */
add_action( 'save_post_post', function () {
	foreach ( array( 5, 10 ) as $n ) {
		np_swr_expire( 'np_most_read_' . $n . '_' . np_views_today() );
	}
} );
