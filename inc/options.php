<?php
/**
 * Theme options.
 *
 * One schema (np_panel_tabs) drives everything: the defaults, the sanitising and
 * the form on the dedicated Theme Panel screen (inc/admin-panel.php). All values
 * live in a single option, `np_options`. Nothing here depends on one particular
 * site: every default is neutral, and anything language specific is translatable.
 *
 * @package News_Press
 */

defined( 'ABSPATH' ) || exit;

/**
 * Social networks shown in the footer, in display order.
 *
 * @return array<string,string> key => label.
 */
function np_social_networks() {
	return apply_filters( 'np_social_networks', array(
		'facebook'  => 'Facebook',
		'youtube'   => 'YouTube',
		'whatsapp'  => 'WhatsApp',
		'x'         => 'X',
		'instagram' => 'Instagram',
		'tiktok'    => 'TikTok',
		'gnews'     => 'Google News',
	) );
}

/**
 * Home page sections that take a category and/or a custom title.
 *
 * 'cat'    default category slug ('' = none, null = no category control).
 * 'latest' true: with no category chosen the section shows the latest posts;
 *          false: it stays hidden until a category is chosen.
 *
 * @return array<string,array{label:string,title:string,cat:?string,latest:bool}>
 */
function np_home_sections() {
	return apply_filters( 'np_home_sections', array(
		'side'  => array( 'label' => __( 'Latest (list beside the lead story)', 'news-press' ), 'title' => __( 'Latest', 'news-press' ), 'cat' => '', 'latest' => true ),
		'top'   => array( 'label' => __( 'Top stories (4-card grid)', 'news-press' ), 'title' => __( 'Top stories', 'news-press' ), 'cat' => '', 'latest' => true ),
		'local' => array( 'label' => __( 'Featured section (its sub-categories become pills)', 'news-press' ), 'title' => '', 'cat' => '', 'latest' => false ),
		'most'  => array( 'label' => __( 'Most read card', 'news-press' ), 'title' => __( 'Most read', 'news-press' ), 'cat' => null, 'latest' => false ),
		'col1'  => array( 'label' => __( 'Column 1', 'news-press' ), 'title' => '', 'cat' => '', 'latest' => false ),
		'col2'  => array( 'label' => __( 'Column 2', 'news-press' ), 'title' => '', 'cat' => '', 'latest' => false ),
		'col3'  => array( 'label' => __( 'Column 3', 'news-press' ), 'title' => '', 'cat' => '', 'latest' => false ),
		'video' => array( 'label' => __( 'Video band', 'news-press' ), 'title' => '', 'cat' => '', 'latest' => false ),
	) );
}

/**
 * Colour settings: key => [label, CSS custom property, default].
 *
 * @return array<string,array{0:string,1:string,2:string}>
 */
function np_colour_settings() {
	return array(
		'color_brand'       => array( __( 'Accent colour (buttons, badges, links)', 'news-press' ), '--brand', '#B30F0F' ),
		'color_navy'        => array( __( 'Dark colour (most-read card, quotes)', 'news-press' ), '--brand-dark', '#01284F' ),
		'color_topbar'      => array( __( 'Top bar background', 'news-press' ), '--topbar-bg', '#01284F' ),
		'color_topbar_text' => array( __( 'Top bar text', 'news-press' ), '--topbar-text', '#FFFFFF' ),
		'color_footer'      => array( __( 'Footer background', 'news-press' ), '--footer-bg', '#01284F' ),
		'color_footer_text' => array( __( 'Footer text', 'news-press' ), '--footer-text', '#FFFFFF' ),
	);
}

/**
 * The Theme Panel definition: tabs, each with an ordered list of fields.
 *
 * Field keys: type (heading|text|textarea|url|email|number|checkbox|select|color|image|category),
 * label, default, desc, placeholder, choices (select), min/max (number), text (checkbox caption),
 * latest (category: empty choice means "latest posts"), sanitize (optional callable override).
 *
 * @return array<string,array{label:string,fields:array<string,array>}>
 */
function np_panel_tabs() {
	/* General. */
	$general = array(
		'h_brand'      => array( 'type' => 'heading', 'label' => __( 'Branding', 'news-press' ), 'desc' => __( 'The category pills under the header come from the menu assigned to "Category navigation (pills)" under Appearance → Menus.', 'news-press' ) ),
		'header_logo'  => array( 'type' => 'image', 'label' => __( 'Header logo', 'news-press' ), 'default' => '', 'desc' => __( 'Blank = the logo from Site Identity, then the site name as a wordmark. Recommended: transparent PNG/WebP, about 320×80.', 'news-press' ) ),
		'footer_logo'  => array( 'type' => 'image', 'label' => __( 'Footer logo', 'news-press' ), 'default' => '', 'desc' => __( 'Shown on the footer background, so use a version that suits it. Blank = wordmark.', 'news-press' ) ),
		'tagline'      => array( 'type' => 'text', 'label' => __( 'Logo tagline', 'news-press' ), 'default' => '', 'desc' => __( 'Short line shown under the header wordmark. Blank = none.', 'news-press' ) ),
		'h_lang'       => array( 'type' => 'heading', 'label' => __( 'Language & dates', 'news-press' ) ),
		'bn_mode'      => array(
			'type'    => 'select',
			'label'   => __( 'Bangla digits, dates & calendar', 'news-press' ),
			'default' => 'auto',
			'choices' => array(
				'auto' => __( 'Automatic (when the site language is Bangla)', 'news-press' ),
				'on'   => __( 'Always on', 'news-press' ),
				'off'  => __( 'Always off (use the WordPress date and time format)', 'news-press' ),
			),
		),
		'topbar_date'     => array( 'type' => 'checkbox', 'label' => __( 'Top bar', 'news-press' ), 'text' => __( 'Show the top bar with today\'s date', 'news-press' ), 'default' => '1' ),
		'topbar_calendar' => array( 'type' => 'checkbox', 'label' => __( 'Bangla calendar date', 'news-press' ), 'text' => __( 'Also show the Bangla calendar date in the top bar (only when Bangla dates are on)', 'news-press' ), 'default' => '1' ),
		'h_type'       => array( 'type' => 'heading', 'label' => __( 'Typography', 'news-press' ) ),
		'font'         => array(
			'type'    => 'select',
			'label'   => __( 'Font', 'news-press' ),
			'default' => 'auto',
			'choices' => array(
				'auto'   => __( 'Automatic (bundled Bangla font when Bangla dates are on, otherwise the system font)', 'news-press' ),
				'bangla' => __( 'Bundled Bangla font (SolaimanLipi)', 'news-press' ),
				'system' => __( 'System font (fastest, nothing to download)', 'news-press' ),
			),
		),
	);

	/* Colours. */
	$colours = array();
	foreach ( np_colour_settings() as $key => $c ) {
		$colours[ $key ] = array( 'type' => 'color', 'label' => $c[0], 'default' => $c[2] );
	}

	/* Footer. */
	$footer = array(
		'h_about'        => array( 'type' => 'heading', 'label' => __( 'About & imprint', 'news-press' ) ),
		'footer_about'   => array( 'type' => 'textarea', 'label' => __( 'Footer description', 'news-press' ), 'default' => '' ),
		'copyright'      => array( 'type' => 'text', 'label' => __( 'Copyright holder', 'news-press' ), 'default' => '', 'desc' => __( 'Blank = the site name.', 'news-press' ) ),
		'editor_label'   => array( 'type' => 'text', 'label' => __( 'Editor label', 'news-press' ), 'default' => __( 'Editor & Publisher', 'news-press' ), 'desc' => __( 'Shown before the editor name, e.g. "Editor in chief".', 'news-press' ) ),
		'editor_name'    => array( 'type' => 'text', 'label' => __( 'Editor name', 'news-press' ), 'default' => '', 'desc' => __( 'Blank hides the editor line.', 'news-press' ) ),
		'publisher_line' => array( 'type' => 'text', 'label' => __( 'Published-from line', 'news-press' ), 'default' => '' ),
		'office_address' => array( 'type' => 'text', 'label' => __( 'Office address', 'news-press' ), 'default' => '' ),
	);
	$departments = array(
		'news' => __( 'Newsroom', 'news-press' ),
		'ad'   => __( 'Advertising', 'news-press' ),
		'info' => __( 'General enquiries', 'news-press' ),
	);
	foreach ( $departments as $k => $label ) {
		/* translators: %s: contact block name. */
		$footer[ 'h_' . $k ]     = array( 'type' => 'heading', 'label' => sprintf( __( 'Contact block: %s', 'news-press' ), $label ) );
		$footer[ $k . '_label' ] = array( 'type' => 'text', 'label' => __( 'Heading', 'news-press' ), 'default' => $label );
		$footer[ $k . '_phone' ] = array( 'type' => 'text', 'label' => __( 'Phone', 'news-press' ), 'default' => '' );
		$footer[ $k . '_email' ] = array( 'type' => 'email', 'label' => __( 'Email', 'news-press' ), 'default' => '' );
	}

	/* Social. */
	$social = array(
		'h_social' => array( 'type' => 'heading', 'label' => __( 'Social links', 'news-press' ), 'desc' => __( 'Switch a network on and add its link. Networks without a link are not shown.', 'news-press' ) ),
	);
	foreach ( np_social_networks() as $k => $label ) {
		$social[ 'social_' . $k . '_on' ] = array(
			'type'    => 'checkbox',
			'label'   => $label,
			'text'    => __( 'Show in the footer', 'news-press' ),
			'default' => '1',
		);
		$social[ 'social_' . $k ] = array( 'type' => 'url', 'label' => __( 'Link', 'news-press' ), 'default' => '', 'placeholder' => 'https://' );
	}

	/* Home page. */
	$home = array(
		'h_ticker'     => array( 'type' => 'heading', 'label' => __( 'Breaking ticker', 'news-press' ) ),
		'ticker_on'    => array( 'type' => 'checkbox', 'label' => __( 'Ticker', 'news-press' ), 'text' => __( 'Show the ticker under the header on the home page', 'news-press' ), 'default' => '1' ),
		'ticker_label' => array( 'type' => 'text', 'label' => __( 'Ticker label', 'news-press' ), 'default' => __( 'Breaking', 'news-press' ) ),
		'ticker_count' => array( 'type' => 'number', 'label' => __( 'Number of headlines', 'news-press' ), 'default' => '3', 'min' => 1, 'max' => 10 ),
		'ticker_tag'   => array( 'type' => 'text', 'label' => __( 'Tag that feeds the ticker', 'news-press' ), 'default' => 'breaking', 'desc' => __( 'Tag slug. Posts with this tag appear first; if no post has it, the newest headlines are used.', 'news-press' ) ),
		'h_sections'   => array( 'type' => 'heading', 'label' => __( 'Home page sections', 'news-press' ), 'desc' => __( 'Pick the category each section shows and the title printed on the site. A blank title uses the default title, or the category name.', 'news-press' ) ),
	);
	foreach ( np_home_sections() as $k => $s ) {
		if ( null !== $s['cat'] ) {
			$home[ 'home_' . $k . '_cat' ] = array(
				'type'    => 'category',
				/* translators: %s: section name. */
				'label'   => sprintf( __( '%s: category', 'news-press' ), $s['label'] ),
				'default' => '',
				'latest'  => ! empty( $s['latest'] ),
				'slug'    => (string) $s['cat'],
			);
		}
		$home[ 'home_' . $k . '_title' ] = array(
			'type'        => 'text',
			/* translators: %s: section name. */
			'label'       => sprintf( __( '%s: title', 'news-press' ), $s['label'] ),
			'default'     => '',
			'placeholder' => $s['title'] ? $s['title'] : __( 'Blank = category name', 'news-press' ),
		);
	}

	/* Sharing, newsletter & ads. */
	$engage = array(
		'h_fb'              => array( 'type' => 'heading', 'label' => __( 'Facebook Page box', 'news-press' ), 'desc' => __( 'Shown in the sidebar after the "Most read" card on posts and archives. It is a lazy-loaded iframe, so nothing from Facebook loads until the reader scrolls near it.', 'news-press' ) ),
		'fb_page_url'       => array( 'type' => 'url', 'label' => __( 'Facebook Page URL', 'news-press' ), 'default' => '', 'sanitize' => 'np_sanitize_fb_url', 'placeholder' => 'https://www.facebook.com/YourPage', 'desc' => __( 'Blank hides the box.', 'news-press' ) ),
		'fb_page_title'     => array( 'type' => 'text', 'label' => __( 'Box title', 'news-press' ), 'default' => __( 'Follow us on Facebook', 'news-press' ) ),
		'h_share'           => array( 'type' => 'heading', 'label' => __( 'Sharing & newsletter', 'news-press' ) ),
		'fb_app_id'         => array( 'type' => 'text', 'label' => __( 'Facebook App ID', 'news-press' ), 'default' => '', 'sanitize' => 'np_sanitize_digits', 'desc' => __( 'Optional. Enables the Messenger share button on desktop.', 'news-press' ) ),
		'newsletter_action' => array( 'type' => 'url', 'label' => __( 'Newsletter form action URL', 'news-press' ), 'default' => '', 'desc' => __( 'The form posts an "EMAIL" field to this address (Mailchimp, MailerLite and similar). Blank hides the form.', 'news-press' ) ),
		'h_ads'             => array( 'type' => 'heading', 'label' => __( 'Advertising', 'news-press' ), 'desc' => __( 'Ad slots are widget areas: Appearance → Widgets.', 'news-press' ) ),
		'ad_placeholders'   => array( 'type' => 'checkbox', 'label' => __( 'Reserved space', 'news-press' ), 'text' => __( 'Show a reserved ad box when an ad widget area is empty', 'news-press' ), 'default' => '' ),
	);

	return apply_filters( 'np_panel_tabs', array(
		'general' => array( 'label' => __( 'General', 'news-press' ), 'fields' => $general ),
		'colours' => array( 'label' => __( 'Colours', 'news-press' ), 'fields' => $colours ),
		'footer'  => array( 'label' => __( 'Footer & contact', 'news-press' ), 'fields' => $footer ),
		'social'  => array( 'label' => __( 'Social', 'news-press' ), 'fields' => $social ),
		'home'    => array( 'label' => __( 'Home page', 'news-press' ), 'fields' => $home ),
		'engage'  => array( 'label' => __( 'Sharing & ads', 'news-press' ), 'fields' => $engage ),
	) );
}

/**
 * Flat list of all value-carrying fields.
 *
 * @return array<string,array>
 */
function np_fields() {
	$out = array();
	foreach ( np_panel_tabs() as $tab ) {
		foreach ( $tab['fields'] as $key => $f ) {
			if ( 'heading' !== $f['type'] ) {
				$out[ $key ] = $f;
			}
		}
	}
	return $out;
}

/**
 * Default values for every option.
 *
 * @return array<string,string>
 */
function np_defaults() {
	$d = array();
	foreach ( np_fields() as $key => $f ) {
		$d[ $key ] = isset( $f['default'] ) ? (string) $f['default'] : '';
	}
	return apply_filters( 'np_option_defaults', $d );
}

/**
 * Values saved by an older version of the theme in the Customizer (theme mods).
 *
 * @return array<string,string>
 */
function np_legacy_options() {
	$mods = get_theme_mods();
	$out  = array();
	if ( ! is_array( $mods ) ) {
		return $out;
	}
	foreach ( array_keys( np_defaults() ) as $key ) {
		if ( isset( $mods[ 'np_' . $key ] ) && is_scalar( $mods[ 'np_' . $key ] ) ) {
			$out[ $key ] = (string) $mods[ 'np_' . $key ];
		}
	}
	return $out;
}

/**
 * All options merged over the defaults. Cached for the request.
 *
 * @return array<string,string>
 */
function np_options() {
	static $all = null;
	if ( null === $all ) {
		$saved = get_option( 'np_options', null );
		if ( ! is_array( $saved ) ) {
			$saved = np_legacy_options(); // Not migrated yet: read the old Customizer values.
		}
		$all = array_merge( np_defaults(), $saved );
	}
	return $all;
}

/**
 * Read one theme option.
 *
 * @param string $key Option key.
 * @return string
 */
function np_opt( $key ) {
	$all = np_options();
	return isset( $all[ $key ] ) ? (string) $all[ $key ] : '';
}

/** Copy Customizer values into the new option once, the first time an admin screen loads. */
function np_maybe_migrate_options() {
	if ( null !== get_option( 'np_options', null ) ) {
		return;
	}
	$legacy = np_legacy_options();
	if ( $legacy ) {
		add_option( 'np_options', $legacy ); // The old theme mods stay in place, so rolling back loses nothing.
	}
}

/* ---------------------------------------------------------------------------
 * Derived settings
 * ------------------------------------------------------------------------- */

/** Should Bangla digits, dates and the Bangla calendar be used? */
function np_is_bangla() {
	$mode = np_opt( 'bn_mode' );
	if ( 'on' === $mode ) {
		return true;
	}
	if ( 'off' === $mode ) {
		return false;
	}
	return 0 === strpos( determine_locale(), 'bn' );
}

/** Load the bundled Bangla font? */
function np_use_bangla_font() {
	$font = np_opt( 'font' );
	if ( 'bangla' === $font ) {
		return true;
	}
	if ( 'system' === $font ) {
		return false;
	}
	return np_is_bangla();
}

/** The colour for the mobile browser bar. */
function np_theme_colour() {
	$c = sanitize_hex_color( np_opt( 'color_topbar' ) );
	return $c ? $c : '#01284F';
}

/**
 * Resolve a home section: its title, category (or null for "all posts") and visibility.
 *
 * @param string $key Section key from np_home_sections().
 * @return array{title:string,cat:?WP_Term,hidden:bool}
 */
function np_home_section( $key ) {
	$all = np_home_sections();
	$def = isset( $all[ $key ] ) ? $all[ $key ] : array( 'title' => '', 'cat' => '' );
	$val = np_opt( 'home_' . $key . '_cat' );
	$cat = null;
	if ( 'hide' === $val ) {
		return array( 'title' => '', 'cat' => null, 'hidden' => true );
	}
	if ( $val && ( $t = get_category( (int) $val ) ) && ! is_wp_error( $t ) ) {
		$cat = $t;
	} elseif ( ! empty( $def['cat'] ) ) {
		$t   = get_category_by_slug( $def['cat'] );
		$cat = $t ? $t : null;
	}
	$title = np_opt( 'home_' . $key . '_title' );
	if ( '' === trim( $title ) ) {
		$title = $def['title'] ? $def['title'] : ( $cat ? $cat->name : '' );
	}
	return array( 'title' => $title, 'cat' => $cat, 'hidden' => false );
}

/** Colour overrides as CSS variables, printed after the main stylesheet (only changed values). */
function np_colour_css() {
	$css = '';
	foreach ( np_colour_settings() as $key => $c ) {
		$v = sanitize_hex_color( np_opt( $key ) );
		if ( $v && strtolower( $v ) !== strtolower( $c[2] ) ) {
			$css .= $c[1] . ':' . $v . ';';
		}
	}
	return $css ? ':root{' . $css . '}' : '';
}

/* ---------------------------------------------------------------------------
 * Sanitising
 * ------------------------------------------------------------------------- */

/** Sanitise a checkbox to '1' / ''. */
function np_sanitize_checkbox( $v ) {
	return $v ? '1' : '';
}

/** Keep digits only. */
function np_sanitize_digits( $v ) {
	return preg_replace( '/\D/', '', (string) $v );
}

/**
 * Sanitise a category choice: '' (default), 'hide', or an existing category ID.
 *
 * @param mixed $v          Submitted value.
 * @param bool  $allow_hide Whether 'hide' is a valid choice.
 */
function np_sanitize_cat( $v, $allow_hide = true ) {
	if ( 'hide' === $v ) {
		return $allow_hide ? 'hide' : '';
	}
	$id = absint( $v );
	return ( $id && term_exists( $id, 'category' ) ) ? (string) $id : '';
}

/** Sanitise an attachment ID (images only). */
function np_sanitize_image( $v ) {
	$id = absint( $v );
	return ( $id && wp_attachment_is_image( $id ) ) ? (string) $id : '';
}

/** Sanitise a Facebook URL (facebook.com only). */
function np_sanitize_fb_url( $v ) {
	$v    = esc_url_raw( trim( (string) $v ), array( 'https' ) );
	$host = wp_parse_url( $v, PHP_URL_HOST );
	return ( $host && preg_match( '/(^|\.)(facebook|fb)\.com$/i', $host ) ) ? $v : '';
}

/**
 * Sanitise one value according to its field definition.
 *
 * @param array $f Field definition.
 * @param mixed $v Raw value.
 * @return string
 */
function np_sanitize_field( $f, $v ) {
	if ( is_array( $v ) ) {
		return isset( $f['default'] ) ? (string) $f['default'] : '';
	}
	$v = (string) $v;
	if ( ! empty( $f['sanitize'] ) && is_callable( $f['sanitize'] ) ) {
		return (string) call_user_func( $f['sanitize'], $v );
	}
	switch ( $f['type'] ) {
		case 'textarea':
			return sanitize_textarea_field( $v );
		case 'url':
			return esc_url_raw( trim( $v ) );
		case 'email':
			return sanitize_email( $v );
		case 'number':
			$n = absint( $v );
			if ( isset( $f['min'] ) ) {
				$n = max( (int) $f['min'], $n );
			}
			if ( isset( $f['max'] ) ) {
				$n = min( (int) $f['max'], $n );
			}
			return (string) $n;
		case 'checkbox':
			return np_sanitize_checkbox( $v );
		case 'select':
			return isset( $f['choices'][ $v ] ) ? $v : (string) $f['default'];
		case 'color':
			$c = sanitize_hex_color( $v );
			return $c ? $c : '';
		case 'image':
			return np_sanitize_image( $v );
		case 'category':
			return np_sanitize_cat( $v, ! empty( $f['latest'] ) );
		default:
			return sanitize_text_field( $v );
	}
}

/**
 * Sanitise the whole option on save (registered with the Settings API).
 *
 * @param mixed $input Submitted array.
 * @return array<string,string>
 */
function np_sanitize_options( $input ) {
	// "Reset to defaults" button (the Settings API has already checked the nonce and capability).
	if ( isset( $_POST['np_reset'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
		add_settings_error( 'np_options', 'np_reset', __( 'Theme options were reset to their defaults.', 'news-press' ), 'success' );
		return array();
	}
	$input = is_array( $input ) ? $input : array();
	$form  = ! empty( $input['_form'] ); // Posted by the Theme Panel: an unticked box is simply absent.
	$out   = array();
	foreach ( np_fields() as $key => $f ) {
		if ( isset( $input[ $key ] ) ) {
			$out[ $key ] = np_sanitize_field( $f, $input[ $key ] );
		} elseif ( $form ) {
			$out[ $key ] = np_sanitize_field( $f, '' );
		}
	}
	return $out;
}
