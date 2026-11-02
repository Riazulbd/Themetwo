<?php
/**
 * Narayanganj Press theme bootstrap.
 *
 * Every feature lives in its own file under /inc so a child theme can
 * override or unhook pieces without copying the whole theme.
 *
 * @package Narayanganj_Press
 */

defined( 'ABSPATH' ) || exit;

define( 'NP_VERSION', '1.0.0' );
define( 'NP_DIR', get_template_directory() );
define( 'NP_URI', get_template_directory_uri() );

$np_includes = array(
	'bangla',          // Bangla digits, dates, Bangla calendar, relative time.
	'setup',           // Theme supports, menus, image sizes, widget areas.
	'disable-comments',// Comments and pings off everywhere.
	'customizer',      // Editor/address/phones/socials/ads settings.
	'icons',           // Inline SVG icons (share + social).
	'template-tags',   // Cards, meta line, share row, breadcrumbs, most-read.
	'views',           // Cache-friendly view counter for "most read".
	'performance',     // Inline CSS, font preload, deferred JS, head cleanup.
	'seo',             // NewsArticle / BreadcrumbList JSON-LD and Open Graph.
);

foreach ( $np_includes as $np_file ) {
	require_once NP_DIR . '/inc/' . $np_file . '.php';
}
unset( $np_includes, $np_file );
