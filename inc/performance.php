<?php
/**
 * Front-end performance:
 * - main.css is inlined in <head> (≈ 6 KB gzipped): no render-blocking request.
 *   Filter `np_inline_css` to false to load it as a normal cached file instead.
 * - Bundled Bangla font (optional): @font-face + preload of the WOFF2 (font-display: swap).
 * - One small deferred script, no jQuery.
 * - Core bloat removed: emoji, block-library CSS on templates that don't need it,
 *   oEmbed discovery, RSD/WLW, generator.
 *
 * @package Khobor_Press
 */

defined( 'ABSPATH' ) || exit;

/** Absolute font URL (child themes can filter). */
function np_font_url( $ext = 'woff2' ) {
	return apply_filters( 'np_font_url', NP_URI . '/assets/fonts/solaimanlipi.' . $ext, $ext );
}

/** Preload the font (when the bundled one is used) and the LCP image as early as possible. */
function np_head_preloads() {
	if ( np_use_bangla_font() ) {
		printf( '<link rel="preload" href="%s" as="font" type="font/woff2" crossorigin>' . "\n", esc_url( np_font_url() ) );
	}

	$img = np_lcp_image();
	if ( $img ) {
		printf(
			'<link rel="preload" as="image" href="%s"%s imagesizes="%s" fetchpriority="high">' . "\n",
			esc_url( $img['src'] ),
			$img['srcset'] ? ' imagesrcset="' . esc_attr( $img['srcset'] ) . '"' : '',
			esc_attr( $img['sizes'] )
		);
	}
}
add_action( 'wp_head', 'np_head_preloads', 1 );

/**
 * The largest above-the-fold image: the featured image on a post, the lead story on the front page.
 *
 * @return array{src:string,srcset:string,sizes:string}|null
 */
function np_lcp_image() {
	static $img = false;
	if ( false !== $img ) {
		return $img;
	}
	$img   = null;
	$id    = 0;
	$sizes = '(max-width: 820px) calc(100vw - 32px), 760px'; // Must equal the sizes printed on the <img> itself (single.php).
	if ( is_singular() && has_post_thumbnail() ) {
		$id = get_post_thumbnail_id();
	} elseif ( is_front_page() ) {
		$lead  = np_front_lead_post();
		$id    = ( $lead && has_post_thumbnail( $lead ) ) ? get_post_thumbnail_id( $lead ) : 0;
		$sizes = '(max-width: 820px) calc(100vw - 32px), 744px'; // Must equal the sizes in front-page.php.
	}
	if ( $id ) {
		$src = wp_get_attachment_image_src( $id, 'np-lead' );
		if ( $src ) {
			$srcset = wp_get_attachment_image_srcset( $id, 'np-lead' );
			$img    = array(
				'src'    => $src[0],
				'srcset' => $srcset ? $srcset : '',
				'sizes'  => $sizes,
			);
		}
	}
	return $img;
}

/**
 * Response headers for speed behind a CDN (Cloudflare):
 * - Link: rel=preload headers. Cloudflare turns these into "Early Hints", so the browser
 *   starts fetching the font and the main image while the server is still building the page.
 * - Cache-Control with s-maxage, so a CDN set to cache HTML can serve pages without
 *   waking WordPress. Browsers still revalidate (max-age=0). Never sent for logged-in users,
 *   previews, search, 404s or password-protected posts.
 */
function np_response_headers() {
	if ( headers_sent() || is_admin() || is_feed() || is_robots() || is_trackback() ) {
		return;
	}
	$links = array();
	if ( np_use_bangla_font() ) {
		$links[] = '<' . esc_url_raw( np_font_url() ) . '>; rel=preload; as=font; type="font/woff2"; crossorigin';
	}
	$img = np_lcp_image();
	if ( $img ) {
		$link = '<' . esc_url_raw( $img['src'] ) . '>; rel=preload; as=image; fetchpriority=high';
		if ( $img['srcset'] && false === strpos( $img['srcset'], '"' ) ) {
			$link .= '; imagesrcset="' . $img['srcset'] . '"; imagesizes="' . $img['sizes'] . '"';
		}
		$links[] = $link;
	}
	foreach ( $links as $l ) {
		header( 'Link: ' . $l, false );
	}

	$ttl = (int) np_opt( 'edge_ttl' );
	if ( $ttl > 0 && ! is_user_logged_in() && ! is_preview() && ! is_search() && ! is_404() && ! post_password_required() && ! is_customize_preview() ) {
		header( 'Cache-Control: public, max-age=0, s-maxage=' . $ttl . ', stale-while-revalidate=60, stale-if-error=86400' );
	}
}
add_action( 'template_redirect', 'np_response_headers', 99 );

/**
 * Instant page transitions: the browser prepares the next article while the reader hovers or
 * presses a link, so the click opens it almost immediately (Chrome, Edge and other Chromium
 * browsers; others simply load normally). WordPress 6.8+ prints the rules itself; we only
 * ask it to prerender, and print our own rules on older versions.
 */
add_filter( 'wp_speculation_rules_configuration', function () {
	return array(
		'mode'      => 'prerender',
		'eagerness' => 'moderate',
	);
} );
add_action( 'wp_footer', function () {
	if ( function_exists( 'wp_get_speculation_rules_configuration' ) || is_user_logged_in() ) {
		return;
	}
	$rules = array(
		'prerender' => array( array(
			'where'     => array( 'and' => array(
				array( 'href_matches' => '/*' ),
				array( 'not' => array( 'href_matches' => array( '/wp-*.php', '/wp-admin/*', '/wp-content/*', '/*\\?(.+)' ) ) ),
				array( 'not' => array( 'selector_matches' => 'a[rel~=nofollow]' ) ),
			) ),
			'eagerness' => 'moderate',
		) ),
	);
	echo '<script type="speculationrules">' . wp_json_encode( $rules ) . '</script>' . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput
}, 20 );

/** The front-page lead story (sticky first, else newest). Cached per request. */
function np_front_lead_post() {
	static $lead = null;
	if ( null === $lead ) {
		$sticky = get_option( 'sticky_posts' );
		$posts  = $sticky ? get_posts( array( 'post__in' => $sticky, 'posts_per_page' => 1, 'ignore_sticky_posts' => true ) ) : array();
		if ( ! $posts ) {
			$posts = get_posts( array( 'posts_per_page' => 1, 'ignore_sticky_posts' => true ) );
		}
		$lead = $posts ? $posts[0] : false;
	}
	return $lead;
}

/** @font-face + tokens + layout: inline (default) or enqueued. */
function np_styles() {
	$file = NP_DIR . '/assets/css/main.css';
	$ver  = NP_VERSION . '.' . ( file_exists( $file ) ? filemtime( $file ) : '0' );

	$face = '';
	if ( np_use_bangla_font() ) {
		$face = sprintf(
		'@font-face{font-family:"SolaimanLipi";src:url("%1$s") format("woff2"),url("%2$s") format("woff"),url("%3$s") format("truetype");font-weight:400;font-style:normal;font-display:swap}'
		/* Metric-matched local fallback shown until the web font arrives, so line breaks barely move on swap (CLS). */
		. '@font-face{font-family:"NP Fallback";src:local("Nirmala UI"),local("Noto Sans Bengali"),local("NotoSansBengali-Regular"),local("Vrinda");size-adjust:89%%}',
		esc_url( np_font_url( 'woff2' ) ),
		esc_url( np_font_url( 'woff' ) ),
		esc_url( np_font_url( 'ttf' ) )
		);
	}

	if ( apply_filters( 'np_inline_css', true ) && is_readable( $file ) ) {
		wp_register_style( 'np-main', false, array(), $ver );
		wp_enqueue_style( 'np-main' );
		wp_add_inline_style( 'np-main', $face . file_get_contents( $file ) ); // phpcs:ignore WordPress.WP.AlternativeFunctions
	} else {
		wp_enqueue_style( 'np-main', NP_URI . '/assets/css/main.css', array(), $ver );
		if ( $face ) {
			wp_add_inline_style( 'np-main', $face );
		}
	}
	// Theme Panel colour overrides (only values that differ from the defaults).
	$colours = np_colour_css();
	if ( $colours ) {
		wp_add_inline_style( 'np-main', $colours );
	}

	// Child theme stylesheet, if any.
	if ( is_child_theme() ) {
		wp_enqueue_style( 'np-child', get_stylesheet_uri(), array( 'np-main' ), wp_get_theme()->get( 'Version' ) );
	}

	// Block-library CSS only where post content is rendered.
	if ( ! is_singular() ) {
		wp_dequeue_style( 'wp-block-library' );
		wp_dequeue_style( 'wp-block-library-theme' );
		wp_dequeue_style( 'global-styles' );
		wp_dequeue_style( 'classic-theme-styles' );
	}
}
add_action( 'wp_enqueue_scripts', 'np_styles', 20 );

/** One deferred script for menu, search, copy-link and the view beacon. */
function np_scripts() {
	$file = NP_DIR . '/assets/js/main.js';
	wp_enqueue_script( 'np-main', NP_URI . '/assets/js/main.js', array(), NP_VERSION . '.' . ( file_exists( $file ) ? filemtime( $file ) : '0' ), array(
		'strategy'  => 'defer',
		'in_footer' => true,
	) );
	$data = array(
		'copied' => __( 'Link copied', 'khobor-press' ),
		'failed' => __( 'Copy failed', 'khobor-press' ),
	);
	if ( is_singular( 'post' ) && np_views_enabled() ) {
		$data['view']   = esc_url_raw( add_query_arg( 'np_view', '1', home_url( '/' ) ) ); // Answered early, see inc/views.php.
		$data['id']     = get_queried_object_id();
		$data['sample'] = max( 1, (int) np_opt( 'views_sample' ) );
	}
	wp_add_inline_script( 'np-main', 'window.NP=' . wp_json_encode( $data ) . ';', 'before' );
}
add_action( 'wp_enqueue_scripts', 'np_scripts' );

/**
 * Load core block CSS per block (only for blocks actually on the page) instead of one big
 * stylesheet for everything.
 */
add_filter( 'should_load_separate_core_block_assets', '__return_true' );

/** Head cleanup. */
function np_cleanup() {
	remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
	remove_action( 'wp_print_styles', 'print_emoji_styles' );
	remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
	remove_action( 'admin_print_styles', 'print_emoji_styles' );
	remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
	remove_filter( 'comment_text_rss', 'wp_staticize_emoji' );
	remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );
	add_filter( 'emoji_svg_url', '__return_false' );
	remove_action( 'wp_head', 'rsd_link' );
	remove_action( 'wp_head', 'wlwmanifest_link' );
	remove_action( 'wp_head', 'wp_generator' );
	remove_action( 'wp_head', 'wp_shortlink_wp_head' );
	remove_action( 'wp_head', 'wp_oembed_add_discovery_links' );
	remove_action( 'wp_head', 'feed_links_extra', 3 );
}
add_action( 'init', 'np_cleanup' );

/** Don't load the wp-embed / speculative extras we don't use. */
add_action( 'wp_footer', function () {
	wp_dequeue_script( 'wp-embed' );
} );

/** Theme colour for mobile browser chrome. */
add_action( 'wp_head', function () {
	echo '<meta name="theme-color" content="' . esc_attr( np_theme_colour() ) . '">' . "\n";
}, 2 );

/** `np-sys` on <body> switches the stylesheet to the system font with real bold. */
add_filter( 'body_class', function ( $classes ) {
	if ( ! np_use_bangla_font() ) {
		$classes[] = 'np-sys';
	}
	return $classes;
} );

/** Block editor: use the bundled Bangla font only when the front end does. */
add_filter( 'block_editor_settings_all', function ( $settings ) {
	if ( np_use_bangla_font() ) {
		$settings['styles'][] = array(
			'css' => sprintf(
				'@font-face{font-family:"SolaimanLipi";src:url("%s") format("woff2"),url("%s") format("woff");font-weight:400;font-style:normal;font-display:swap}'
				. 'body{font-family:"SolaimanLipi","Noto Sans Bengali",sans-serif}'
				. 'h1,h2,h3,h4,h5,h6,strong,b{font-weight:400;-webkit-text-stroke:max(.55px,.04em) currentColor;paint-order:stroke fill}',
				esc_url( np_font_url( 'woff2' ) ),
				esc_url( np_font_url( 'woff' ) )
			),
		);
	}
	return $settings;
} );
