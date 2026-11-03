<?php
/**
 * Lightweight SEO output: Open Graph / Twitter tags and NewsArticle +
 * BreadcrumbList JSON-LD. Skipped automatically when Yoast SEO, Rank Math,
 * SEOPress or All in One SEO is active (they output their own).
 *
 * @package Khobor_Press
 */

defined( 'ABSPATH' ) || exit;

/** Is a dedicated SEO plugin active? */
function np_seo_plugin_active() {
	$active = defined( 'WPSEO_VERSION' ) || class_exists( 'RankMath' ) || defined( 'RANK_MATH_VERSION' ) || defined( 'SEOPRESS_VERSION' ) || defined( 'AIOSEO_VERSION' );
	return (bool) apply_filters( 'np_seo_plugin_active', $active );
}

/** Representative image for the current view. */
function np_seo_image() {
	if ( is_singular() && has_post_thumbnail() ) {
		$img = wp_get_attachment_image_src( get_post_thumbnail_id(), 'np-lead' );
		if ( $img ) {
			return array( 'url' => $img[0], 'width' => $img[1], 'height' => $img[2] );
		}
	}
	if ( has_custom_logo() ) {
		$img = wp_get_attachment_image_src( get_theme_mod( 'custom_logo' ), 'full' );
		if ( $img ) {
			return array( 'url' => $img[0], 'width' => $img[1], 'height' => $img[2] );
		}
	}
	return null;
}

/** Description for the current view. */
function np_seo_description() {
	if ( is_singular() ) {
		$post = get_queried_object();
		$text = has_excerpt( $post ) ? get_the_excerpt( $post ) : wp_trim_words( wp_strip_all_tags( strip_shortcodes( $post->post_content ) ), 30, '…' );
	} elseif ( is_category() || is_tag() ) {
		$text = term_description() ? term_description() : single_term_title( '', false ) . ' — ' . get_bloginfo( 'name' );
	} else {
		$text = get_bloginfo( 'description' );
	}
	return trim( wp_strip_all_tags( (string) $text ) );
}

/** Open Graph + Twitter + description. */
function np_meta_tags() {
	if ( np_seo_plugin_active() || is_404() ) {
		return;
	}
	$desc  = np_seo_description();
	$title = wp_get_document_title();
	$url   = is_singular() ? get_permalink() : ( is_front_page() ? home_url( '/' ) : '' );
	$img   = np_seo_image();
	$tags  = array(
		'og:site_name' => get_bloginfo( 'name' ),
		'og:locale'    => get_locale(),
		'og:type'      => is_single() ? 'article' : 'website',
		'og:title'     => is_singular() ? single_post_title( '', false ) : $title,
		'og:url'       => $url,
		'og:description' => $desc,
		'twitter:card' => $img ? 'summary_large_image' : 'summary',
	);
	if ( $desc ) {
		echo '<meta name="description" content="' . esc_attr( $desc ) . '">' . "\n";
	}
	if ( $img ) {
		$tags['og:image']        = $img['url'];
		$tags['og:image:width']  = $img['width'];
		$tags['og:image:height'] = $img['height'];
	}
	if ( is_single() ) {
		$tags['article:published_time'] = get_the_date( 'c' );
		$tags['article:modified_time']  = get_the_modified_date( 'c' );
		$top                            = np_post_top_cat();
		if ( $top ) {
			$tags['article:section'] = $top->name;
		}
	}
	foreach ( $tags as $prop => $val ) {
		if ( '' === (string) $val ) {
			continue;
		}
		$attr = 0 === strpos( $prop, 'twitter:' ) ? 'name' : 'property';
		printf( '<meta %s="%s" content="%s">' . "\n", esc_attr( $attr ), esc_attr( $prop ), esc_attr( $val ) );
	}
}
add_action( 'wp_head', 'np_meta_tags', 5 );

/** Organisation node reused as author and publisher (no personal bylines). */
function np_schema_org() {
	$org = array(
		'@type' => 'NewsMediaOrganization',
		'@id'   => home_url( '/#organization' ),
		'name'  => get_bloginfo( 'name' ),
		'url'   => home_url( '/' ),
	);
	if ( has_custom_logo() ) {
		$logo = wp_get_attachment_image_src( get_theme_mod( 'custom_logo' ), 'full' );
		if ( $logo ) {
			$org['logo'] = array( '@type' => 'ImageObject', 'url' => $logo[0], 'width' => $logo[1], 'height' => $logo[2] );
		}
	}
	return $org;
}

/** JSON-LD graph. */
function np_schema() {
	if ( np_seo_plugin_active() ) {
		return;
	}
	$graph = array();
	if ( is_front_page() ) {
		$graph[] = np_schema_org();
		$graph[] = array(
			'@type'           => 'WebSite',
			'@id'             => home_url( '/#website' ),
			'url'             => home_url( '/' ),
			'name'            => get_bloginfo( 'name' ),
			'inLanguage'      => get_bloginfo( 'language' ),
			'publisher'       => array( '@id' => home_url( '/#organization' ) ),
			'potentialAction' => array(
				'@type'       => 'SearchAction',
				'target'      => home_url( '/?s={search_term_string}' ),
				'query-input' => 'required name=search_term_string',
			),
		);
	}
	if ( is_single() ) {
		$post    = get_queried_object();
		$img     = np_seo_image();
		$article = array(
			'@type'            => 'NewsArticle',
			'@id'              => get_permalink( $post ) . '#article',
			'mainEntityOfPage' => get_permalink( $post ),
			'headline'         => wp_strip_all_tags( get_the_title( $post ) ),
			'description'      => np_seo_description(),
			'datePublished'    => get_the_date( 'c', $post ),
			'dateModified'     => get_the_modified_date( 'c', $post ),
			'inLanguage'       => get_bloginfo( 'language' ),
			'author'           => np_schema_org(),
			'publisher'        => np_schema_org(),
		);
		$top = np_post_top_cat( $post );
		if ( $top ) {
			$article['articleSection'] = $top->name;
		}
		if ( $img ) {
			$article['image'] = array( $img['url'] );
		}
		$graph[] = $article;
	}
	if ( ! is_front_page() && ! is_404() ) {
		$list = array();
		foreach ( np_breadcrumb_items() as $i => $it ) {
			if ( empty( $it['url'] ) ) {
				continue;
			}
			$list[] = array( '@type' => 'ListItem', 'position' => count( $list ) + 1, 'name' => $it['name'], 'item' => $it['url'] );
		}
		if ( is_single() ) {
			$list[] = array( '@type' => 'ListItem', 'position' => count( $list ) + 1, 'name' => wp_strip_all_tags( get_the_title() ) );
		}
		if ( count( $list ) > 1 ) {
			$graph[] = array( '@type' => 'BreadcrumbList', 'itemListElement' => $list );
		}
	}
	$graph = apply_filters( 'np_schema_graph', $graph );
	if ( $graph ) {
		echo '<script type="application/ld+json">' . wp_json_encode( array( '@context' => 'https://schema.org', '@graph' => $graph ), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) . '</script>' . "\n";
	}
}
add_action( 'wp_head', 'np_schema', 20 );
