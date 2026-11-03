<?php
/**
 * Inline SVG icons (hand-drawn 18×18 glyphs from the design file). Inline SVG
 * means zero extra requests and no icon font.
 *
 * @package Khobor_Press
 */

defined( 'ABSPATH' ) || exit;

/**
 * Return an inline SVG icon.
 *
 * @param string $name Icon key.
 * @param int    $size Rendered size in px.
 * @return string Safe SVG markup (static, theme-controlled).
 */
function np_icon( $name, $size = 18 ) {
	$fb = 'M10 17V10h2.4l.4-2.8H10V5.4c0-.8.2-1.4 1.4-1.4H13V1.5c-.3 0-1.2-.1-2.2-.1-2.2 0-3.7 1.3-3.7 3.8v2H4.7V10h2.4v7z';
	$wa = '<path fill="#fff" d="M9 1a8 8 0 0 0-6.9 12L1 17l4.1-1.1A8 8 0 1 0 9 1z"/><path fill="#25D366" d="M6.2 5.2c.3-.3.8-.3 1 0l.9 1.3c.2.3.2.7-.1 1l-.5.5c.5 1 1.3 1.8 2.3 2.3l.5-.5c.3-.3.7-.3 1-.1l1.3.9c.3.2.3.7 0 1l-.7.7c-.6.6-1.5.7-2.2.3a8.6 8.6 0 0 1-3.7-3.7c-.4-.7-.3-1.6.3-2.2z"/>';

	$icons = array(
		'facebook'  => '<path fill="#fff" d="' . $fb . '"/>',
		'whatsapp'  => $wa,
		'messenger' => '<path fill="#fff" d="M9 1C4.6 1 1 4.3 1 8.4c0 2.3 1.1 4.4 2.9 5.7V17l2.7-1.5c.8.2 1.6.3 2.4.3 4.4 0 8-3.3 8-7.4S13.4 1 9 1z"/><path fill="#A033FF" d="M4.4 10.6L7.6 7l1.8 1.5L13.6 6.3 10.4 9.9 8.6 8.4z"/>',
		'youtube'   => '<path fill="#fff" d="M6.5 4.8v8.4L13.8 9z"/>',
		'x'         => '<path fill="#fff" d="M2.6 2.5h4.2l8.6 13H11.2z"/><path fill="#fff" d="M13.6 2.5h1.9L10.1 8.7 9.1 7.3z"/><path fill="#fff" d="M7.9 10.3l1 1.4-4.6 3.8H2.4z"/>',
		'instagram' => '<path fill="none" stroke="#fff" stroke-width="1.6" d="M5.5 2.5h7a3 3 0 0 1 3 3v7a3 3 0 0 1-3 3h-7a3 3 0 0 1-3-3v-7a3 3 0 0 1 3-3z"/><path fill="none" stroke="#fff" stroke-width="1.6" d="M9 5.9a3.1 3.1 0 1 1 0 6.2a3.1 3.1 0 1 1 0-6.2z"/><path fill="#fff" d="M13 4.1a.9.9 0 1 1 0 1.8a.9.9 0 1 1 0-1.8z"/>',
		'tiktok'    => '<path fill="#fff" d="M9.6 1.8h2.5c.2 1.7 1.3 2.9 3.1 3.1v2.5c-1.2 0-2.2-.3-3.1-.9v5.4A4.3 4.3 0 1 1 7.8 7.6v2.6a1.8 1.8 0 1 0 1.8 1.7z"/>',
		'gnews'     => '<path fill="#4285F4" d="M2 3h10a1 1 0 0 1 1 1v3H2z"/><path fill="#EA4335" d="M5 6h11a1 1 0 0 1 1 1v2.5H5z"/><path fill="#34A853" d="M2 9.5h14v2.5H2z"/><path fill="#FBBC04" d="M2 12h14v2a1 1 0 0 1-1 1H3a1 1 0 0 1-1-1z"/>',
		'copy'      => '<rect x="6" y="6" width="9.5" height="9.5" rx="2" fill="none" stroke="currentColor" stroke-width="1.6"/><path fill="none" stroke="currentColor" stroke-width="1.6" d="M12 4.2V4a1.5 1.5 0 0 0-1.5-1.5h-6A1.5 1.5 0 0 0 3 4v6a1.5 1.5 0 0 0 1.5 1.5h.3"/>',
		'search'    => '<circle cx="8" cy="8" r="5.5" fill="none" stroke="currentColor" stroke-width="1.8"/><path stroke="currentColor" stroke-width="1.8" stroke-linecap="round" d="M12.2 12.2l3.8 3.8"/>',
		'menu'      => '<path stroke="currentColor" stroke-width="1.8" stroke-linecap="round" d="M3 5h12M3 9h12M3 13h12"/>',
		'close'     => '<path stroke="currentColor" stroke-width="1.8" stroke-linecap="round" d="M4 4l10 10M14 4L4 14"/>',
		'sun'       => '<circle cx="9" cy="9" r="3.4" fill="none" stroke="currentColor" stroke-width="1.7"/><path stroke="currentColor" stroke-width="1.7" stroke-linecap="round" d="M9 1.6v1.7M9 14.7v1.7M1.6 9h1.7M14.7 9h1.7M3.8 3.8l1.2 1.2M13 13l1.2 1.2M14.2 3.8L13 5M5 13l-1.2 1.2"/>',
		'moon'      => '<path fill="currentColor" d="M15.2 11.2A6.6 6.6 0 0 1 6.8 2.8a.6.6 0 0 0-.8-.7A7.2 7.2 0 1 0 15.9 12a.6.6 0 0 0-.7-.8z"/>',
		'play'      => '<path fill="currentColor" d="M6 3.5v11l9-5.5z"/>',
		'arrow'     => '<path fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" d="M3 9h12M10 4l5 5-5 5"/>',
	);

	if ( ! isset( $icons[ $name ] ) ) {
		return '';
	}
	return sprintf(
		'<svg class="ic ic-%1$s" width="%2$d" height="%2$d" viewBox="0 0 18 18" aria-hidden="true" focusable="false">%3$s</svg>',
		esc_attr( $name ),
		(int) $size,
		$icons[ $name ]
	);
}
