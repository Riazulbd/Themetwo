<?php
/**
 * Theme supports, menus, image sizes and widget areas.
 *
 * @package Khobor_Press
 */

defined( 'ABSPATH' ) || exit;

if ( ! function_exists( 'np_setup' ) ) {
	/** Register theme features. */
	function np_setup() {
		load_theme_textdomain( 'khobor-press', NP_DIR . '/languages' );

		add_theme_support( 'title-tag' );
		add_theme_support( 'post-thumbnails' );
		add_theme_support( 'automatic-feed-links' );
		add_theme_support( 'responsive-embeds' );
		add_theme_support( 'align-wide' );
		add_theme_support( 'wp-block-styles' );
		add_theme_support( 'editor-styles' );
		add_theme_support( 'customize-selective-refresh-widgets' );
		add_theme_support( 'html5', array( 'search-form', 'gallery', 'caption', 'style', 'script', 'navigation-widgets' ) );
		add_theme_support( 'custom-logo', array(
			'height'      => 80,
			'width'       => 320,
			'flex-height' => true,
			'flex-width'  => true,
		) );
		add_theme_support( 'woocommerce' );

		add_editor_style( 'assets/css/editor.css' );

		// Card / thumbnail crops. Content width matches the article column.
		add_image_size( 'np-lead', 1200, 675, true );   // 16:9 lead + single featured.
		add_image_size( 'np-card', 640, 384, true );    // 5:3 grid cards.
		add_image_size( 'np-thumb', 240, 160, true );   // 3:2 compact rows.

		register_nav_menus( array(
			'primary'      => __( 'Category navigation (pills)', 'khobor-press' ),
			'footer-1'     => __( 'Footer column 1', 'khobor-press' ),
			'footer-2'     => __( 'Footer column 2', 'khobor-press' ),
		) );
	}
}
add_action( 'after_setup_theme', 'np_setup' );

/** Content width for embeds. */
function np_content_width() {
	$GLOBALS['content_width'] = apply_filters( 'np_content_width', 760 );
}
add_action( 'after_setup_theme', 'np_content_width', 0 );

/** Widget areas: ad slots + optional sidebar extras. */
function np_widgets_init() {
	$common = array(
		'before_widget' => '<div id="%1$s" class="widget %2$s">',
		'after_widget'  => '</div>',
		'before_title'  => '<h2 class="widget-title">',
		'after_title'   => '</h2>',
	);
	$areas = array(
		'ad-header'        => array( __( 'Ad: below header (728×90)', 'khobor-press' ), __( 'Shown under the header on home and archives.', 'khobor-press' ) ),
		'ad-home'          => array( __( 'Ad: home between sections (728×90)', 'khobor-press' ), __( 'Between the top-stories grid and the featured section.', 'khobor-press' ) ),
		'ad-single-inline' => array( __( 'Ad: in article (after 3rd paragraph)', 'khobor-press' ), __( '728×90 on desktop, 300×250 on mobile.', 'khobor-press' ) ),
		'ad-sidebar'       => array( __( 'Ad: sidebar (300×250)', 'khobor-press' ), __( 'Bottom of the article sidebar.', 'khobor-press' ) ),
		'sidebar-extra'    => array( __( 'Sidebar: extra widgets', 'khobor-press' ), __( 'Added below "latest news" in the article sidebar.', 'khobor-press' ) ),
	);
	foreach ( $areas as $id => $a ) {
		register_sidebar( array_merge( $common, array( 'id' => $id, 'name' => $a[0], 'description' => $a[1] ) ) );
	}
}
add_action( 'widgets_init', 'np_widgets_init' );

/** Wider-than-default image sizes attribute for the article column. */
function np_content_image_sizes( $sizes, $size ) {
	if ( is_singular() && ! is_admin() ) {
		return '(max-width: 820px) calc(100vw - 32px), 760px';
	}
	return $sizes;
}
add_filter( 'wp_calculate_image_sizes', 'np_content_image_sizes', 10, 2 );

/** Remove author archives entirely: this theme shows no author UI. */
function np_redirect_author_archives() {
	if ( is_author() ) {
		wp_safe_redirect( home_url( '/' ), 301 );
		exit;
	}
}
add_action( 'template_redirect', 'np_redirect_author_archives' );

/** Hide author from REST user listing for anonymous visitors (avoids exposing usernames). */
function np_rest_hide_users( $endpoints ) {
	if ( ! is_user_logged_in() ) {
		unset( $endpoints['/wp/v2/users'], $endpoints['/wp/v2/users/(?P<id>[\d]+)'] );
	}
	return $endpoints;
}
add_filter( 'rest_endpoints', 'np_rest_hide_users' );

/** No author name in oEmbed responses. */
add_filter( 'oembed_response_data', function ( $data ) {
	unset( $data['author_name'], $data['author_url'] );
	return $data;
} );

/** Excerpt "more" and length. */
add_filter( 'excerpt_more', function () {
	return '…';
} );
add_filter( 'excerpt_length', function () {
	return 30;
}, 20 );

/** Body classes for templates. */
add_filter( 'body_class', function ( $classes ) {
	$classes[] = 'np';
	return $classes;
} );

/**
 * Never ask wordpress.org about updates for this theme.
 *
 * WordPress matches themes to wordpress.org by folder name. If a different theme
 * with the same name exists there, "Update" would replace this theme with that one.
 * Removing our theme from the update-check request makes that impossible.
 */
function np_exclude_from_org_updates( $args, $url ) {
	if ( false === strpos( $url, '://api.wordpress.org/themes/update-check' ) || empty( $args['body']['themes'] ) ) {
		return $args;
	}
	$data = json_decode( $args['body']['themes'], true );
	if ( ! is_array( $data ) || empty( $data['themes'] ) ) {
		return $args;
	}
	unset( $data['themes'][ get_template() ], $data['themes'][ get_stylesheet() ] );
	$args['body']['themes'] = wp_json_encode( $data );
	return $args;
}
add_filter( 'http_request_args', 'np_exclude_from_org_updates', 10, 2 );
