<?php
/**
 * Comments and pingbacks are disabled site-wide: front end, admin, feeds,
 * REST and XML-RPC. Filter `np_disable_comments` to false to turn this off.
 *
 * @package Narayanganj_Press
 */

defined( 'ABSPATH' ) || exit;

if ( ! apply_filters( 'np_disable_comments', true ) ) {
	return;
}

/** Remove comment/trackback support from every post type. */
function np_remove_comment_support() {
	foreach ( get_post_types() as $type ) {
		if ( post_type_supports( $type, 'comments' ) ) {
			remove_post_type_support( $type, 'comments' );
		}
		if ( post_type_supports( $type, 'trackbacks' ) ) {
			remove_post_type_support( $type, 'trackbacks' );
		}
	}
}
add_action( 'init', 'np_remove_comment_support', 100 );

// Closed everywhere, nothing listed, zero counts.
add_filter( 'comments_open', '__return_false', 20 );
add_filter( 'pings_open', '__return_false', 20 );
add_filter( 'comments_array', '__return_empty_array', 20 );
add_filter( 'get_comments_number', '__return_zero', 20 );
add_filter( 'feed_links_show_comments_feed', '__return_false' );
add_filter( 'comments_template', function () {
	return NP_DIR . '/inc/no-comments.php';
}, 20 );

// No pingback header / XML-RPC pingbacks.
add_filter( 'wp_headers', function ( $headers ) {
	unset( $headers['X-Pingback'] );
	return $headers;
} );
add_filter( 'xmlrpc_methods', function ( $methods ) {
	unset( $methods['pingback.ping'], $methods['pingback.extensions.getPingbacks'] );
	return $methods;
} );

// Block comment creation through REST.
add_filter( 'rest_endpoints', function ( $endpoints ) {
	unset( $endpoints['/wp/v2/comments'], $endpoints['/wp/v2/comments/(?P<id>[\d]+)'] );
	return $endpoints;
} );
add_filter( 'rest_allow_anonymous_comments', '__return_false' );

// Comment feeds 404 -> home.
add_action( 'template_redirect', function () {
	if ( is_comment_feed() ) {
		wp_safe_redirect( home_url( '/' ), 301 );
		exit;
	}
} );

// Default new posts to closed.
add_filter( 'pre_option_default_comment_status', function () {
	return 'closed';
} );
add_filter( 'pre_option_default_ping_status', function () {
	return 'closed';
} );

// Admin: hide menus, dashboard widget, admin-bar node; redirect the comments screen.
add_action( 'admin_menu', function () {
	remove_menu_page( 'edit-comments.php' );
	remove_submenu_page( 'options-general.php', 'options-discussion.php' );
} );
add_action( 'admin_init', function () {
	global $pagenow;
	if ( in_array( $pagenow, array( 'edit-comments.php', 'options-discussion.php' ), true ) ) {
		wp_safe_redirect( admin_url() );
		exit;
	}
	remove_meta_box( 'dashboard_recent_comments', 'dashboard', 'normal' );
} );
add_action( 'admin_bar_menu', function ( $bar ) {
	$bar->remove_node( 'comments' );
}, 999 );
add_action( 'widgets_init', function () {
	unregister_widget( 'WP_Widget_Recent_Comments' );
}, 20 );
// Front end: never render comment blocks, even if old content or a block widget still contains them.
add_filter( 'render_block', function ( $html, $block ) {
	$name = isset( $block['blockName'] ) ? (string) $block['blockName'] : '';
	if ( 0 === strpos( $name, 'core/comment' ) || 'core/latest-comments' === $name || 'core/post-comments-form' === $name ) {
		return '';
	}
	return $html;
}, 10, 2 );
// Block editor: remove the Latest Comments block and the Discussion panel.
add_filter( 'allowed_block_types_all', function ( $allowed ) {
	if ( true === $allowed ) {
		$all     = array_keys( WP_Block_Type_Registry::get_instance()->get_all_registered() );
		$allowed = array_values( array_filter( $all, function ( $name ) {
			return 0 !== strpos( $name, 'core/comment' ) && 'core/latest-comments' !== $name && 'core/post-comments-form' !== $name;
		} ) );
	}
	return $allowed;
} );
