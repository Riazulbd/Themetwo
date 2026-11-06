<?php
/**
 * Khobor Press theme bootstrap.
 *
 * Every feature lives in its own file under /inc so a child theme can
 * override or unhook pieces without copying the whole theme.
 *
 * @package Khobor_Press
 */

defined( 'ABSPATH' ) || exit;

define( 'NP_VERSION', '1.2.1' );
define( 'NP_DIR', get_template_directory() );
define( 'NP_URI', get_template_directory_uri() );

$np_includes = array(
	'options',         // Theme Panel schema, defaults, reading and sanitising options.
	'cache',           // Stale-while-revalidate helper for transients.
	'bangla',          // Dates, digits and relative time (Bangla or the site's own format).
	'setup',           // Theme supports, menus, image sizes, widget areas.
	'disable-comments',// Comments and pings off everywhere.
	'icons',           // Inline SVG icons (share + social).
	'template-tags',   // Cards, meta line, share row, breadcrumbs, most-read.
	'views',           // Cache-friendly view counter for "most read".
	'color-mode',      // Light/dark mode by time of day, with a visitor switch.
	'performance',     // Inline CSS, font preload, deferred JS, head cleanup.
	'login',           // Branded wp-login.php screen.
	'seo',             // NewsArticle / BreadcrumbList JSON-LD and Open Graph.
);

if ( is_admin() ) {
	$np_includes[] = 'admin-panel'; // The dedicated Theme Panel screen (not part of the Customizer).
}

foreach ( $np_includes as $np_file ) {
	require_once NP_DIR . '/inc/' . $np_file . '.php';
}
unset( $np_includes, $np_file );
