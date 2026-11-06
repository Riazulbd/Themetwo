<?php
/**
 * Photo cards: a branded picture (photo, headline, date, logo) that readers can download or share
 * from an article. It is drawn in the reader's browser on a canvas (assets/js/photocard.js), so the
 * server prints only data attributes: the page stays cacheable and no image is stored.
 * The script and the font load on articles only, and nothing is fetched or drawn until a reader
 * reaches for one of the buttons.
 *
 * Adapted from the Sokal Narayanganj theme. Set it up in Theme Panel → Photo card.
 *
 * @package Khobor_Press
 */

defined( 'ABSPATH' ) || exit;

/**
 * Card layouts (key => admin label).
 *
 * @return array<string,string>
 */
function np_photocard_styles() {
	return array(
		'card'       => __( 'Framed photo, headline, date and link (square)', 'khobor-press' ),
		'panel'      => __( 'Photo with a headline panel (square)', 'khobor-press' ),
		'photostrip' => __( 'Photo and logo strip (wide)', 'khobor-press' ),
		'slogan'     => __( 'Photo with a slogan band (wide)', 'khobor-press' ),
		'classic'    => __( 'Classic (square)', 'khobor-press' ),
		'dark'       => __( 'Dark (square)', 'khobor-press' ),
		'light'      => __( 'Light (square)', 'khobor-press' ),
		'band'       => __( 'Colour band (square)', 'khobor-press' ),
		'strip'      => __( 'Wide strip', 'khobor-press' ),
	);
}

/** Are photo cards switched on, and are we on an article? */
function np_photocard_active() {
	return np_opt( 'card_on' ) && is_singular( 'post' );
}

/** Load the script (articles only) with its font and messages. */
function np_photocard_scripts() {
	if ( ! np_photocard_active() ) {
		return;
	}
	$file = NP_DIR . '/assets/js/photocard.js';
	wp_enqueue_script( 'np-photocard', NP_URI . '/assets/js/photocard.js', array(), NP_VERSION . '.' . ( file_exists( $file ) ? filemtime( $file ) : '0' ), array(
		'strategy'  => 'defer',
		'in_footer' => true,
	) );
	$cfg = array(
		'font' => esc_url_raw( np_font_url( 'woff2' ) ),
		'msg'  => array(
			'working'     => __( 'Making the photo card…', 'khobor-press' ),
			'noPhoto'     => __( 'The original photo could not be added, so the card was made without it.', 'khobor-press' ),
			'failed'      => __( 'The photo card could not be made. Please try again.', 'khobor-press' ),
			'shareFailed' => __( 'Sharing did not work, so the card was downloaded instead.', 'khobor-press' ),
		),
	);
	wp_add_inline_script( 'np-photocard', 'window.NPCardCfg=' . wp_json_encode( $cfg ) . ';', 'before' );
}
add_action( 'wp_enqueue_scripts', 'np_photocard_scripts', 20 );

/** The logo drawn on the card: the card's own, else the header logo, else the Site Identity logo. */
function np_photocard_logo_url() {
	foreach ( array( (int) np_opt( 'card_logo' ), (int) np_opt( 'header_logo' ), (int) get_theme_mod( 'custom_logo' ) ) as $id ) {
		if ( $id && wp_attachment_is_image( $id ) ) {
			$url = wp_get_attachment_image_url( $id, 'large' );
			if ( $url ) {
				return $url;
			}
		}
	}
	return '';
}

/** A hex colour from the Theme Panel, or the fallback. */
function np_photocard_hex( $key, $fallback ) {
	$c = sanitize_hex_color( np_opt( $key ) );
	return $c ? $c : $fallback;
}

/**
 * Print the "download photo card" and "share photo card" buttons.
 *
 * @param int|WP_Post|null $post Post (defaults to the current one).
 */
function np_photocard_buttons( $post = null ) {
	if ( ! np_opt( 'card_on' ) ) {
		return;
	}
	$post = get_post( $post );
	if ( ! $post ) {
		return;
	}
	$styles = np_photocard_styles();
	$style  = np_opt( 'card_style' );
	$style  = isset( $styles[ $style ] ) ? $style : 'card';

	$image = '';
	$thumb = get_post_thumbnail_id( $post );
	if ( $thumb ) {
		$src   = wp_get_attachment_image_src( $thumb, 'large' );
		$image = $src ? $src[0] : '';
	}
	$slug = sanitize_file_name( urldecode( (string) $post->post_name ) );
	$bottom = np_opt( 'card_bottom_line' );

	$attrs = array(
		'data-image'       => esc_url_raw( $image ),
		'data-title'       => wp_strip_all_tags( html_entity_decode( get_the_title( $post ), ENT_QUOTES, 'UTF-8' ) ),
		'data-date'        => np_bn_date( get_post_timestamp( $post ), true ),
		'data-logo'        => esc_url_raw( np_photocard_logo_url() ),
		'data-site-name'   => get_bloginfo( 'name' ),
		'data-style'       => $style,
		'data-bg'          => np_photocard_hex( 'card_bg', '#ffffff' ),
		'data-text'        => np_photocard_hex( 'card_text', '#111111' ),
		'data-accent'      => np_photocard_hex( 'card_accent', np_photocard_hex( 'color_brand', '#c4161c' ) ),
		'data-bottom-line' => $bottom,
		'data-site-url'    => (string) wp_parse_url( home_url( '/' ), PHP_URL_HOST ),
		'data-tagline'     => (string) get_bloginfo( 'description' ),
		'data-slug'        => '' !== $slug ? $slug : 'news-' . $post->ID,
	);

	echo '<div class="np-sp"';
	foreach ( $attrs as $k => $v ) {
		echo ' ' . esc_attr( $k ) . '="' . esc_attr( $v ) . '"';
	}
	echo '>';
	$icons = array(
		'download' => 'M12 3a1 1 0 0 1 1 1v8.6l2.3-2.3a1 1 0 1 1 1.4 1.4l-4 4a1 1 0 0 1-1.4 0l-4-4a1 1 0 1 1 1.4-1.4l2.3 2.3V4a1 1 0 0 1 1-1zM5 18a1 1 0 0 1 1 1v1h12v-1a1 1 0 1 1 2 0v2a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1v-2a1 1 0 0 1 1-1z',
		'share'    => 'M18 16a3 3 0 0 0-2.4 1.2l-6.7-3.4a3 3 0 0 0 0-1.6l6.7-3.4A3 3 0 1 0 15 7c0 .3 0 .5.1.8L8.4 11.2a3 3 0 1 0 0 3.6l6.7 3.4A3 3 0 1 0 18 16z',
	);
	$svg = '<svg width="18" height="18" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path fill="currentColor" d="%s"/></svg>';
	printf(
		'<button type="button" class="np-sp__btn np-sp__btn--download" data-np-sp="download">' . $svg . '<span class="b">%s</span></button>', // phpcs:ignore WordPress.Security.EscapeOutput -- static SVG, label escaped.
		esc_attr( $icons['download'] ),
		esc_html__( 'Download photo card', 'khobor-press' )
	);
	printf(
		'<button type="button" class="np-sp__btn np-sp__btn--share" data-np-sp="share" hidden>' . $svg . '<span class="b">%s</span></button>', // phpcs:ignore WordPress.Security.EscapeOutput -- static SVG, label escaped.
		esc_attr( $icons['share'] ),
		esc_html__( 'Share photo card', 'khobor-press' )
	);
	echo '<span class="np-sp__status" role="status" aria-live="polite"></span>';
	echo '</div>';
}
