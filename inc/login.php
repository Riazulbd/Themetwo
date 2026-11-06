<?php
/**
 * Branded login page (wp-login.php): your logo, a background colour you choose, the accent
 * colour on the button, and the site's own link and name instead of the WordPress ones.
 * Everything is set in Theme Panel → Login page. CSS is printed only on the login screen.
 *
 * @package Khobor_Press
 */

defined( 'ABSPATH' ) || exit;

/** Is the custom login page on? */
function np_login_on() {
	return (bool) np_opt( 'login_custom' );
}

/** The logo for the login page: its own, else the footer logo (made for dark backgrounds), else the header logo. */
function np_login_logo_url() {
	foreach ( array( 'login_logo', 'footer_logo', 'header_logo' ) as $key ) {
		$id = (int) np_opt( $key );
		if ( $id && wp_attachment_is_image( $id ) ) {
			$url = wp_get_attachment_image_url( $id, 'medium' );
			if ( $url ) {
				return $url;
			}
		}
	}
	return '';
}

/** Print the login page styles. */
function np_login_styles() {
	if ( ! np_login_on() ) {
		return;
	}
	$brand = sanitize_hex_color( np_opt( 'color_brand' ) );
	$brand = $brand ? $brand : '#B30F0F';
	$bg    = sanitize_hex_color( np_opt( 'login_bg' ) );
	$bg    = $bg ? $bg : ( sanitize_hex_color( np_opt( 'color_navy' ) ) ? sanitize_hex_color( np_opt( 'color_navy' ) ) : '#01284F' );
	$logo  = np_login_logo_url();

	$css  = 'body.login{background:' . $bg . ';font-family:system-ui,-apple-system,"Segoe UI",Roboto,"Noto Sans Bengali","Noto Sans",Arial,sans-serif}';
	$css .= '.login h1 a{display:block;width:100%;height:auto;margin:0 auto 24px;text-indent:0;overflow:visible;font-size:28px;line-height:1.3;font-weight:700;color:#fff;text-align:center;';
	if ( $logo ) {
		$css .= 'height:80px;font-size:0;background:url("' . esc_url( $logo ) . '") center/contain no-repeat;';
	} else {
		$css .= 'background:none;';
	}
	$css .= '}';
	$css .= '.login form{border:0;border-radius:16px;box-shadow:0 10px 30px rgba(0,0,0,.25);padding:26px 24px}';
	$css .= '.login label{font-size:14px}.login form .input,.login input[type=text],.login input[type=password]{border-radius:10px;padding:6px 10px;font-size:16px}';
	$css .= '.login input:focus{border-color:' . $brand . ';box-shadow:0 0 0 1px ' . $brand . '}';
	$css .= '.wp-core-ui .button-primary{background:' . $brand . ';border-color:' . $brand . ';border-radius:999px;box-shadow:none;text-shadow:none;min-height:40px;padding:0 22px}';
	$css .= '.wp-core-ui .button-primary:hover,.wp-core-ui .button-primary:focus{background:' . $brand . ';border-color:' . $brand . ';filter:brightness(1.1)}';
	$css .= '.login .button.wp-hide-pw{color:' . $brand . '}.login form .forgetmenot label{font-size:14px}';
	$css .= '.login #nav a,.login #backtoblog a,.login .privacy-policy-page-link a{color:rgba(255,255,255,.88)}';
	$css .= '.login #nav a:hover,.login #backtoblog a:hover{color:#fff;text-decoration:underline}';
	$css .= '.login .message,.login .success,.login #login_error{border-radius:10px;border-left-color:' . $brand . '}';
	$css .= '.login .np-login-note{margin:0 0 16px;text-align:center;color:rgba(255,255,255,.9);font-size:15px;line-height:1.6}';
	wp_add_inline_style( 'login', $css );
}
add_action( 'login_enqueue_scripts', 'np_login_styles' );

/** The logo links to the site, not wordpress.org, and carries the site name. */
add_filter( 'login_headerurl', function ( $url ) {
	return np_login_on() ? home_url( '/' ) : $url;
} );
add_filter( 'login_headertext', function ( $text ) {
	return np_login_on() ? get_bloginfo( 'name' ) : $text;
} );

/** Optional welcome line above the form. */
add_filter( 'login_message', function ( $message ) {
	$note = np_login_on() ? trim( np_opt( 'login_message' ) ) : '';
	return $note ? '<p class="np-login-note">' . esc_html( $note ) . '</p>' . $message : $message;
} );

/** The interface is Bengali, so the language picker would only confuse. */
add_filter( 'login_display_language_dropdown', function ( $show ) {
	return ( np_login_on() && 'bn' === np_ui_language() ) ? false : $show;
} );
