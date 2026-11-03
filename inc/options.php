<?php
/**
 * Theme options.
 *
 * One schema (np_panel_tabs) drives everything: the defaults, the sanitising and
 * the form on the dedicated Theme Panel screen (inc/admin-panel.php). All values
 * live in a single option, `np_options`. Nothing here depends on one particular
 * site: every default is neutral, and anything language specific is translatable.
 *
 * @package Khobor_Press
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
		'side'  => array( 'label' => __( 'Latest (list beside the lead story)', 'khobor-press' ), 'title' => __( 'Latest', 'khobor-press' ), 'cat' => '', 'latest' => true ),
		'top'   => array( 'label' => __( 'Top stories (4-card grid)', 'khobor-press' ), 'title' => __( 'Top stories', 'khobor-press' ), 'cat' => '', 'latest' => true ),
		'local' => array( 'label' => __( 'Featured section (its sub-categories become pills)', 'khobor-press' ), 'title' => '', 'cat' => '', 'latest' => false ),
		'most'  => array( 'label' => __( 'Most read card', 'khobor-press' ), 'title' => __( 'Most read', 'khobor-press' ), 'cat' => null, 'latest' => false ),
		'col1'  => array( 'label' => __( 'Column 1', 'khobor-press' ), 'title' => '', 'cat' => '', 'latest' => false ),
		'col2'  => array( 'label' => __( 'Column 2', 'khobor-press' ), 'title' => '', 'cat' => '', 'latest' => false ),
		'col3'  => array( 'label' => __( 'Column 3', 'khobor-press' ), 'title' => '', 'cat' => '', 'latest' => false ),
		'video' => array( 'label' => __( 'Video band', 'khobor-press' ), 'title' => '', 'cat' => '', 'latest' => false ),
	) );
}

/**
 * Colour settings: key => [label, CSS custom property, default].
 *
 * @return array<string,array{0:string,1:string,2:string}>
 */
function np_colour_settings() {
	return array(
		'color_brand'       => array( __( 'Accent colour (buttons, badges, links)', 'khobor-press' ), '--brand', '#B30F0F' ),
		'color_navy'        => array( __( 'Dark colour (most-read card, quotes)', 'khobor-press' ), '--brand-dark', '#01284F' ),
		'color_topbar'      => array( __( 'Top bar background', 'khobor-press' ), '--topbar-bg', '#01284F' ),
		'color_topbar_text' => array( __( 'Top bar text', 'khobor-press' ), '--topbar-text', '#FFFFFF' ),
		'color_footer'      => array( __( 'Footer background', 'khobor-press' ), '--footer-bg', '#01284F' ),
		'color_footer_text' => array( __( 'Footer text', 'khobor-press' ), '--footer-text', '#FFFFFF' ),
	);
}

/**
 * Header colour settings: key => label. Blank means "use the theme's default header".
 *
 * @return array<string,string>
 */
function np_header_colour_settings() {
	return array(
		'color_header'           => __( 'Header background (light mode)', 'khobor-press' ),
		'color_header_text'      => __( 'Header text and wordmark (light mode)', 'khobor-press' ),
		'color_header_dark'      => __( 'Header background (dark mode)', 'khobor-press' ),
		'color_header_text_dark' => __( 'Header text and wordmark (dark mode)', 'khobor-press' ),
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
		'h_brand'      => array( 'type' => 'heading', 'label' => __( 'Branding', 'khobor-press' ), 'desc' => __( 'The category pills under the header come from the menu assigned to "Category navigation (pills)" under Appearance → Menus.', 'khobor-press' ) ),
		'header_logo'  => array( 'type' => 'image', 'label' => __( 'Header logo', 'khobor-press' ), 'default' => '', 'desc' => __( 'Blank = the logo from Site Identity, then the site name as a wordmark. Recommended: transparent PNG/WebP, about 320×80.', 'khobor-press' ) ),
		'footer_logo'  => array( 'type' => 'image', 'label' => __( 'Footer logo', 'khobor-press' ), 'default' => '', 'desc' => __( 'Shown on the footer background, so use a version that suits it. Blank = wordmark.', 'khobor-press' ) ),
		'tagline'      => array( 'type' => 'text', 'label' => __( 'Logo tagline', 'khobor-press' ), 'default' => '', 'desc' => __( 'Short line shown under the header wordmark. Blank = none.', 'khobor-press' ) ),
		'h_lang'       => array( 'type' => 'heading', 'label' => __( 'Language & dates', 'khobor-press' ) ),
		'ui_language'  => array(
			'type'    => 'select',
			'label'   => __( 'Site interface language', 'khobor-press' ),
			'default' => 'bn',
			'choices' => array(
				'bn'   => __( 'Bengali (all theme text shown in Bengali)', 'khobor-press' ),
				'site' => __( 'Follow the site language (Settings → General)', 'khobor-press' ),
			),
			'desc'    => __( 'Controls the text built into the theme, such as buttons, labels and dates.', 'khobor-press' ),
		),
		'bn_mode'      => array(
			'type'    => 'select',
			'label'   => __( 'Bangla digits, dates & calendar', 'khobor-press' ),
			'default' => 'auto',
			'choices' => array(
				'auto' => __( 'Automatic (when the site language is Bangla)', 'khobor-press' ),
				'on'   => __( 'Always on', 'khobor-press' ),
				'off'  => __( 'Always off (use the WordPress date and time format)', 'khobor-press' ),
			),
		),
		'topbar_date'     => array( 'type' => 'checkbox', 'label' => __( 'Top bar', 'khobor-press' ), 'text' => __( 'Show the top bar with today\'s date', 'khobor-press' ), 'default' => '1' ),
		'topbar_calendar' => array( 'type' => 'checkbox', 'label' => __( 'Bangla calendar date', 'khobor-press' ), 'text' => __( 'Also show the Bangla calendar date in the top bar (only when Bangla dates are on)', 'khobor-press' ), 'default' => '1' ),
		'h_mode'       => array( 'type' => 'heading', 'label' => __( 'Light & dark mode', 'khobor-press' ), 'desc' => __( 'Visitors can always switch with the button in the header; their choice is remembered on their device.', 'khobor-press' ) ),
		'color_mode'   => array(
			'type'    => 'select',
			'label'   => __( 'Default mode', 'khobor-press' ),
			'default' => 'time',
			'choices' => array(
				'time'   => __( 'Automatic by time of day (visitor\'s local time)', 'khobor-press' ),
				'system' => __( 'Follow the visitor\'s device setting', 'khobor-press' ),
				'light'  => __( 'Always light', 'khobor-press' ),
				'dark'   => __( 'Always dark', 'khobor-press' ),
			),
		),
		'dark_from'    => array( 'type' => 'number', 'label' => __( 'Dark mode starts at (hour, 0–23)', 'khobor-press' ), 'default' => '18', 'min' => 0, 'max' => 23, 'desc' => __( 'Used by "Automatic by time of day".', 'khobor-press' ) ),
		'dark_until'   => array( 'type' => 'number', 'label' => __( 'Light mode starts at (hour, 0–23)', 'khobor-press' ), 'default' => '6', 'min' => 0, 'max' => 23 ),
		'mode_toggle'  => array( 'type' => 'checkbox', 'label' => __( 'Switch button', 'khobor-press' ), 'text' => __( 'Show the light/dark switch in the header', 'khobor-press' ), 'default' => '1' ),
		'h_type'       => array( 'type' => 'heading', 'label' => __( 'Typography', 'khobor-press' ) ),
		'font'         => array(
			'type'    => 'select',
			'label'   => __( 'Font', 'khobor-press' ),
			'default' => 'auto',
			'choices' => array(
				'auto'   => __( 'Automatic (bundled Bangla font when Bangla dates are on, otherwise the system font)', 'khobor-press' ),
				'bangla' => __( 'Bundled Bangla font (SolaimanLipi)', 'khobor-press' ),
				'system' => __( 'System font (fastest, nothing to download)', 'khobor-press' ),
			),
		),
	);

	/* Colours. */
	$colours = array(
		'h_header' => array( 'type' => 'heading', 'label' => __( 'Header', 'khobor-press' ), 'desc' => __( 'Pick a header colour that matches your logo, for example when the logo has a solid background instead of a transparent one. Leave blank for the default look.', 'khobor-press' ) ),
	);
	foreach ( np_header_colour_settings() as $key => $label ) {
		$colours[ $key ] = array( 'type' => 'color', 'label' => $label, 'default' => '', 'blank' => true );
	}
	$colours['h_site'] = array( 'type' => 'heading', 'label' => __( 'Site colours', 'khobor-press' ) );
	foreach ( np_colour_settings() as $key => $c ) {
		$colours[ $key ] = array( 'type' => 'color', 'label' => $c[0], 'default' => $c[2] );
	}

	/* Footer. */
	$footer = array(
		'h_about'        => array( 'type' => 'heading', 'label' => __( 'About & imprint', 'khobor-press' ) ),
		'footer_about'   => array( 'type' => 'textarea', 'label' => __( 'Footer description', 'khobor-press' ), 'default' => '' ),
		'copyright'      => array( 'type' => 'text', 'label' => __( 'Copyright holder', 'khobor-press' ), 'default' => '', 'desc' => __( 'Blank = the site name.', 'khobor-press' ) ),
		'editor_label'   => array( 'type' => 'text', 'label' => __( 'Editor label', 'khobor-press' ), 'default' => __( 'Editor & Publisher', 'khobor-press' ), 'desc' => __( 'Shown before the editor name, e.g. "Editor in chief".', 'khobor-press' ) ),
		'editor_name'    => array( 'type' => 'text', 'label' => __( 'Editor name', 'khobor-press' ), 'default' => '', 'desc' => __( 'Blank hides the editor line.', 'khobor-press' ) ),
		'publisher_line' => array( 'type' => 'text', 'label' => __( 'Published-from line', 'khobor-press' ), 'default' => '' ),
		'office_address' => array( 'type' => 'text', 'label' => __( 'Office address', 'khobor-press' ), 'default' => '' ),
	);
	$departments = array(
		'news' => __( 'Newsroom', 'khobor-press' ),
		'ad'   => __( 'Advertising', 'khobor-press' ),
		'info' => __( 'General enquiries', 'khobor-press' ),
	);
	foreach ( $departments as $k => $label ) {
		/* translators: %s: contact block name. */
		$footer[ 'h_' . $k ]     = array( 'type' => 'heading', 'label' => sprintf( __( 'Contact block: %s', 'khobor-press' ), $label ) );
		$footer[ $k . '_label' ] = array( 'type' => 'text', 'label' => __( 'Heading', 'khobor-press' ), 'default' => $label );
		$footer[ $k . '_phone' ] = array( 'type' => 'text', 'label' => __( 'Phone', 'khobor-press' ), 'default' => '' );
		$footer[ $k . '_email' ] = array( 'type' => 'email', 'label' => __( 'Email', 'khobor-press' ), 'default' => '' );
	}

	/* Social. */
	$social = array(
		'h_social' => array( 'type' => 'heading', 'label' => __( 'Social links', 'khobor-press' ), 'desc' => __( 'Switch a network on and add its link. Networks without a link are not shown.', 'khobor-press' ) ),
	);
	foreach ( np_social_networks() as $k => $label ) {
		$social[ 'social_' . $k . '_on' ] = array(
			'type'    => 'checkbox',
			'label'   => $label,
			'text'    => __( 'Show in the footer', 'khobor-press' ),
			'default' => '1',
		);
		$social[ 'social_' . $k ] = array( 'type' => 'url', 'label' => __( 'Link', 'khobor-press' ), 'default' => '', 'placeholder' => 'https://' );
	}

	/* Home page. */
	$home = array(
		'h_ticker'     => array( 'type' => 'heading', 'label' => __( 'Breaking ticker', 'khobor-press' ) ),
		'ticker_on'    => array( 'type' => 'checkbox', 'label' => __( 'Ticker', 'khobor-press' ), 'text' => __( 'Show the ticker under the header on the home page', 'khobor-press' ), 'default' => '1' ),
		'ticker_label' => array( 'type' => 'text', 'label' => __( 'Ticker label', 'khobor-press' ), 'default' => __( 'Breaking', 'khobor-press' ) ),
		'ticker_count' => array( 'type' => 'number', 'label' => __( 'Number of headlines', 'khobor-press' ), 'default' => '3', 'min' => 1, 'max' => 10 ),
		'ticker_tag'   => array( 'type' => 'text', 'label' => __( 'Tag that feeds the ticker', 'khobor-press' ), 'default' => 'breaking', 'desc' => __( 'Tag slug. Posts with this tag appear first; if no post has it, the newest headlines are used.', 'khobor-press' ) ),
		'h_sections'   => array( 'type' => 'heading', 'label' => __( 'Home page sections', 'khobor-press' ), 'desc' => __( 'Pick the category each section shows and the title printed on the site. A blank title uses the default title, or the category name.', 'khobor-press' ) ),
	);
	foreach ( np_home_sections() as $k => $s ) {
		if ( null !== $s['cat'] ) {
			$home[ 'home_' . $k . '_cat' ] = array(
				'type'    => 'category',
				/* translators: %s: section name. */
				'label'   => sprintf( __( '%s: category', 'khobor-press' ), $s['label'] ),
				'default' => '',
				'latest'  => ! empty( $s['latest'] ),
				'slug'    => (string) $s['cat'],
			);
		}
		$home[ 'home_' . $k . '_title' ] = array(
			'type'        => 'text',
			/* translators: %s: section name. */
			'label'       => sprintf( __( '%s: title', 'khobor-press' ), $s['label'] ),
			'default'     => '',
			'placeholder' => $s['title'] ? $s['title'] : __( 'Blank = category name', 'khobor-press' ),
		);
	}

	/* Sharing, newsletter & ads. */
	$engage = array(
		'h_fb'              => array( 'type' => 'heading', 'label' => __( 'Facebook Page box', 'khobor-press' ), 'desc' => __( 'Shown in the sidebar after the "Most read" card on posts and archives. It is a lazy-loaded iframe, so nothing from Facebook loads until the reader scrolls near it.', 'khobor-press' ) ),
		'fb_page_url'       => array( 'type' => 'url', 'label' => __( 'Facebook Page URL', 'khobor-press' ), 'default' => '', 'sanitize' => 'np_sanitize_fb_url', 'placeholder' => 'https://www.facebook.com/YourPage', 'desc' => __( 'Blank hides the box.', 'khobor-press' ) ),
		'fb_page_title'     => array( 'type' => 'text', 'label' => __( 'Box title', 'khobor-press' ), 'default' => __( 'Follow us on Facebook', 'khobor-press' ) ),
		'h_share'           => array( 'type' => 'heading', 'label' => __( 'Sharing & newsletter', 'khobor-press' ) ),
		'fb_app_id'         => array( 'type' => 'text', 'label' => __( 'Facebook App ID', 'khobor-press' ), 'default' => '', 'sanitize' => 'np_sanitize_digits', 'desc' => __( 'Optional. Enables the Messenger share button on desktop.', 'khobor-press' ) ),
		'newsletter_action' => array( 'type' => 'url', 'label' => __( 'Newsletter form action URL', 'khobor-press' ), 'default' => '', 'desc' => __( 'The form posts an "EMAIL" field to this address (Mailchimp, MailerLite and similar). Blank hides the form.', 'khobor-press' ) ),
		'h_speed'           => array( 'type' => 'heading', 'label' => __( 'Speed & caching', 'khobor-press' ) ),
		'edge_ttl'          => array( 'type' => 'number', 'label' => __( 'Edge cache time (seconds)', 'khobor-press' ), 'default' => '300', 'min' => 0, 'max' => 86400, 'desc' => __( 'Tells Cloudflare (and other CDNs set to cache HTML) how long it may keep a page for logged-out visitors. 0 turns it off. Logged-in users, previews, search and 404 pages are never cached.', 'khobor-press' ) ),
		'h_ads'             => array( 'type' => 'heading', 'label' => __( 'Advertising', 'khobor-press' ), 'desc' => __( 'Ad slots are widget areas: Appearance → Widgets.', 'khobor-press' ) ),
		'ad_placeholders'   => array( 'type' => 'checkbox', 'label' => __( 'Reserved space', 'khobor-press' ), 'text' => __( 'Show a reserved ad box when an ad widget area is empty', 'khobor-press' ), 'default' => '' ),
	);

	return apply_filters( 'np_panel_tabs', array(
		'general' => array( 'label' => __( 'General', 'khobor-press' ), 'fields' => $general ),
		'colours' => array( 'label' => __( 'Colours', 'khobor-press' ), 'fields' => $colours ),
		'footer'  => array( 'label' => __( 'Footer & contact', 'khobor-press' ), 'fields' => $footer ),
		'social'  => array( 'label' => __( 'Social', 'khobor-press' ), 'fields' => $social ),
		'home'    => array( 'label' => __( 'Home page', 'khobor-press' ), 'fields' => $home ),
		'engage'  => array( 'label' => __( 'Sharing & ads', 'khobor-press' ), 'fields' => $engage ),
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
	} else {
		if ( ! empty( $def['cat'] ) ) {
			$t   = get_category_by_slug( $def['cat'] );
			$cat = $t ? $t : null;
		}
		if ( ! $cat && empty( $def['latest'] ) && null !== $def['cat'] ) {
			$auto = np_auto_sections();
			$cat  = isset( $auto[ $key ] ) ? $auto[ $key ] : null; // Nothing chosen: pick a busy category by itself.
		}
	}
	$title = np_opt( 'home_' . $key . '_title' );
	if ( '' === trim( $title ) ) {
		$title = $def['title'] ? $def['title'] : ( $cat ? $cat->name : '' );
	}
	return array( 'title' => $title, 'cat' => $cat, 'hidden' => false );
}

/**
 * Categories picked automatically for sections that have none chosen: the featured
 * section gets a busy category that has sub-categories, the columns get the next
 * busiest ones, and the video band gets a category named "video" if there is one.
 * Remembered for an hour.
 *
 * @return array<string,WP_Term> section key => category.
 */
function np_auto_sections() {
	static $map = null;
	if ( null !== $map ) {
		return $map;
	}
	$ids = get_transient( 'np_auto_cats' );
	if ( ! is_array( $ids ) ) {
		$ids  = array();
		$cats = get_categories( array(
			'hide_empty' => true,
			'pad_counts' => true,
			'orderby'    => 'count',
			'order'      => 'DESC',
			'exclude'    => array( (int) get_option( 'default_category' ) ),
		) );
		$kids = array();
		$tops = array();
		$vid  = 0;
		foreach ( $cats as $c ) {
			if ( $c->parent ) {
				$kids[ $c->parent ] = true;
			} else {
				$tops[] = $c;
			}
			if ( ! $vid && ( preg_match( '/^videos?$/i', $c->slug ) || false !== mb_stripos( $c->name, 'video' ) || false !== mb_strpos( $c->name, 'ভিডিও' ) ) ) {
				$vid = (int) $c->term_id;
			}
		}
		$tops = array_values( array_filter( $tops, function ( $c ) use ( $vid ) {
			return (int) $c->term_id !== $vid;
		} ) );
		$local = 0;
		foreach ( $tops as $i => $c ) {
			if ( isset( $kids[ $c->term_id ] ) ) {
				$local = (int) $c->term_id;
				unset( $tops[ $i ] );
				break;
			}
		}
		$tops = array_values( $tops );
		if ( ! $local && $tops ) {
			$local = (int) $tops[0]->term_id;
			array_shift( $tops );
		}
		if ( $local ) {
			$ids['local'] = $local;
		}
		foreach ( array( 'col1', 'col2', 'col3' ) as $i => $k ) {
			if ( isset( $tops[ $i ] ) ) {
				$ids[ $k ] = (int) $tops[ $i ]->term_id;
			}
		}
		if ( $vid ) {
			$ids['video'] = $vid;
		}
		set_transient( 'np_auto_cats', $ids, HOUR_IN_SECONDS );
	}
	$map = array();
	foreach ( $ids as $k => $id ) {
		$t = get_category( (int) $id );
		if ( $t && ! is_wp_error( $t ) ) {
			$map[ $k ] = $t;
		}
	}
	return $map;
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
	$out = $css ? ':root,:root[data-theme=dark]{' . $css . '}' : '';

	// Header colours, separate for light and dark mode (blank = default look).
	$light = '';
	$dark  = '';
	$pairs = array(
		'color_header'           => array( '--head-bg', 'light' ),
		'color_header_text'      => array( '--head-text', 'light' ),
		'color_header_dark'      => array( '--head-bg', 'dark' ),
		'color_header_text_dark' => array( '--head-text', 'dark' ),
	);
	foreach ( $pairs as $key => $p ) {
		$v = sanitize_hex_color( np_opt( $key ) );
		if ( $v ) {
			if ( 'light' === $p[1] ) {
				$light .= $p[0] . ':' . $v . ';';
			} else {
				$dark .= $p[0] . ':' . $v . ';';
			}
		}
	}
	$out .= $light ? ':root{' . $light . '}' : '';
	$out .= $dark ? ':root[data-theme=dark]{' . $dark . '}' : '';
	return $out;
}

/**
 * The interface language chosen in the Theme Panel ('bn' or 'site').
 * Read straight from the option so it works before the translations are loaded.
 */
function np_ui_language() {
	$o = get_option( 'np_options', null );
	return ( is_array( $o ) && isset( $o['ui_language'] ) && 'site' === $o['ui_language'] ) ? 'site' : 'bn';
}

/**
 * Show the front end in Bengali whatever language WordPress itself is set to.
 * Only the visitor-facing site is affected; the admin keeps the user's language.
 */
function np_force_locale( $locale ) {
	if ( is_admin() || 'bn' !== np_ui_language() || 0 === strpos( $locale, 'bn' ) ) {
		return $locale;
	}
	return 'bn_BD';
}
add_filter( 'locale', 'np_force_locale' );

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
			return np_sanitize_cat( $v, true );
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
		add_settings_error( 'np_options', 'np_reset', __( 'Theme options were reset to their defaults.', 'khobor-press' ), 'success' );
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
