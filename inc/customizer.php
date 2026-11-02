<?php
/**
 * Theme Panel: Appearance → Customize → "Theme Panel (Narayanganj Press)",
 * also linked from the admin menu as "Theme Panel".
 *
 * Sections: logos & colours, footer imprint (editor, address, phones, emails),
 * social links (each on/off + URL), home page sections (category + title per
 * section), single post (Facebook Page box), sharing / newsletter / ads.
 * Every input is sanitised; every output is escaped where it is printed.
 *
 * @package Narayanganj_Press
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
 * 'cat' is the default category slug ('' = all latest posts, null = no category control).
 *
 * @return array<string,array{label:string,title:string,cat:?string}>
 */
function np_home_sections() {
	return apply_filters( 'np_home_sections', array(
		'side'  => array( 'label' => __( 'সর্বশেষ (list beside the lead story)', 'narayanganj-press' ), 'title' => 'সর্বশেষ', 'cat' => '' ),
		'top'   => array( 'label' => __( 'শীর্ষ সংবাদ (4-card grid)', 'narayanganj-press' ), 'title' => 'শীর্ষ সংবাদ', 'cat' => '' ),
		'local' => array( 'label' => __( 'Local section (sub-categories become pills)', 'narayanganj-press' ), 'title' => '', 'cat' => 'narayanganj' ),
		'most'  => array( 'label' => __( 'সর্বাধিক পঠিত (most read card)', 'narayanganj-press' ), 'title' => 'সর্বাধিক পঠিত', 'cat' => null ),
		'col1'  => array( 'label' => __( 'Column 1', 'narayanganj-press' ), 'title' => '', 'cat' => 'national' ),
		'col2'  => array( 'label' => __( 'Column 2', 'narayanganj-press' ), 'title' => '', 'cat' => 'sports' ),
		'col3'  => array( 'label' => __( 'Column 3', 'narayanganj-press' ), 'title' => '', 'cat' => 'entertainment' ),
		'video' => array( 'label' => __( 'Video band', 'narayanganj-press' ), 'title' => '', 'cat' => 'video' ),
	) );
}

/**
 * Colour settings: key => [label, CSS custom property, default].
 *
 * @return array<string,array{0:string,1:string,2:string}>
 */
function np_colour_settings() {
	return array(
		'color_brand'       => array( __( 'Accent colour (buttons, badges, links)', 'narayanganj-press' ), '--brand', '#B30F0F' ),
		'color_navy'        => array( __( 'Dark colour (most-read card, quotes)', 'narayanganj-press' ), '--brand-dark', '#01284F' ),
		'color_topbar'      => array( __( 'Top bar background', 'narayanganj-press' ), '--topbar-bg', '#01284F' ),
		'color_topbar_text' => array( __( 'Top bar text', 'narayanganj-press' ), '--topbar-text', '#FFFFFF' ),
		'color_footer'      => array( __( 'Footer background', 'narayanganj-press' ), '--footer-bg', '#01284F' ),
		'color_footer_text' => array( __( 'Footer text', 'narayanganj-press' ), '--footer-text', '#FFFFFF' ),
	);
}

/**
 * Default values. Placeholders until the newsroom supplies real details.
 *
 * @return array<string,string>
 */
function np_defaults() {
	$d = array(
		'tagline'           => 'নারায়ণগঞ্জের প্রতিদিনের খবর',
		'header_logo'       => '',
		'footer_logo'       => '',
		'footer_about'      => 'নারায়ণগঞ্জের সব উপজেলার সর্বশেষ খবর, প্রতিদিন।',
		'editor_label'      => 'সম্পাদক ও প্রকাশক',
		'editor_name'       => '[নাম]',
		'publisher_line'    => '[নাম] কর্তৃক [ঠিকানা], নারায়ণগঞ্জ থেকে প্রকাশিত।',
		'office_address'    => '[ঠিকানা], নারায়ণগঞ্জ',
		'news_phone'        => '০১XXX-XXXXXX',
		'news_email'        => 'news@narayanganjpress.com',
		'ad_phone'          => '০১XXX-XXXXXX',
		'ad_email'          => 'ad@narayanganjpress.com',
		'info_phone'        => '০১XXX-XXXXXX',
		'info_email'        => 'info@narayanganjpress.com',
		'social_facebook'   => 'https://www.facebook.com/',
		'social_youtube'    => 'https://www.youtube.com/',
		'social_whatsapp'   => 'https://www.whatsapp.com/',
		'social_x'          => 'https://x.com/',
		'social_instagram'  => 'https://www.instagram.com/',
		'social_tiktok'     => 'https://www.tiktok.com/',
		'social_gnews'      => 'https://news.google.com/',
		'fb_page_url'       => '',
		'fb_page_title'     => 'ফেসবুকে আমরা',
		'newsletter_action' => '',
		'fb_app_id'         => '',
		'ticker_count'      => '3',
		'ad_placeholders'   => '1',
		'copyright'         => 'নারায়ণগঞ্জ প্রেস',
	);
	foreach ( np_social_networks() as $k => $label ) {
		$d[ 'social_' . $k . '_on' ] = '1';
	}
	foreach ( np_home_sections() as $k => $s ) {
		$d[ 'home_' . $k . '_title' ] = '';
		$d[ 'home_' . $k . '_cat' ]   = '';
	}
	foreach ( np_colour_settings() as $k => $c ) {
		$d[ $k ] = $c[2];
	}
	return apply_filters( 'np_customizer_defaults', $d );
}

/**
 * Read a theme option with its default.
 *
 * @param string $key Setting key without prefix.
 * @return string
 */
function np_opt( $key ) {
	static $d = null;
	if ( null === $d ) {
		$d = np_defaults();
	}
	return (string) get_theme_mod( 'np_' . $key, isset( $d[ $key ] ) ? $d[ $key ] : '' );
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

/** Sanitise a checkbox to '1' / ''. */
function np_sanitize_checkbox( $v ) {
	return $v ? '1' : '';
}

/** Sanitise a category choice: '' (default), 'hide', or an existing category ID. */
function np_sanitize_cat( $v ) {
	if ( 'hide' === $v ) {
		return 'hide';
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
 * Category dropdown choices.
 *
 * @param string $default_label Label of the "default" option.
 * @return array<string,string>
 */
function np_category_choices( $default_label ) {
	$choices = array(
		''     => $default_label,
		'hide' => __( '— Hide this section —', 'narayanganj-press' ),
	);
	$cats    = get_categories( array( 'hide_empty' => false, 'orderby' => 'name' ) );
	$by_par  = array();
	foreach ( $cats as $c ) {
		$by_par[ $c->parent ][] = $c;
	}
	$walk = function ( $parent, $depth ) use ( &$walk, &$choices, $by_par ) {
		if ( empty( $by_par[ $parent ] ) ) {
			return;
		}
		foreach ( $by_par[ $parent ] as $c ) {
			$choices[ (string) $c->term_id ] = str_repeat( '— ', $depth ) . $c->name;
			$walk( $c->term_id, $depth + 1 );
		}
	};
	$walk( 0, 0 );
	return $choices;
}

/**
 * Register settings and controls.
 *
 * @param WP_Customize_Manager $wp_customize Manager.
 */
function np_customize_register( $wp_customize ) {
	$wp_customize->add_panel( 'np_panel', array(
		'title'       => __( 'Theme Panel (Narayanganj Press)', 'narayanganj-press' ),
		'description' => __( 'Logos, colours, footer details, social links, home page sections and the Facebook Page box.', 'narayanganj-press' ),
		'priority'    => 1,
	) );

	$sections = array(
		'np_brand'   => __( 'Logos & colours', 'narayanganj-press' ),
		'np_imprint' => __( 'Footer: editor & contact', 'narayanganj-press' ),
		'np_social'  => __( 'Social links', 'narayanganj-press' ),
		'np_home'    => __( 'Home page sections', 'narayanganj-press' ),
		'np_single'  => __( 'Single post: Facebook Page box', 'narayanganj-press' ),
		'np_misc'    => __( 'Sharing, newsletter & ads', 'narayanganj-press' ),
	);
	$prio     = 10;
	foreach ( $sections as $id => $title ) {
		$wp_customize->add_section( $id, array( 'title' => $title, 'panel' => 'np_panel', 'priority' => $prio ) );
		$prio += 10;
	}
	// Menus: point people at the menu that drives the category pills.
	$wp_customize->get_section( 'np_brand' )->description = __( 'The category pills under the header come from the menu assigned to "Category navigation (pills)" in Menus.', 'narayanganj-press' );

	$defaults = np_defaults();
	$add      = function ( $key, $section, $label, $type, $sanitize, $extra = array() ) use ( $wp_customize, $defaults ) {
		$wp_customize->add_setting( 'np_' . $key, array(
			'default'           => isset( $defaults[ $key ] ) ? $defaults[ $key ] : '',
			'sanitize_callback' => $sanitize,
			'transport'         => 'refresh',
		) );
		$args = array_merge( array( 'section' => $section, 'label' => $label ), $extra );
		if ( 'image' === $type ) {
			$wp_customize->add_control( new WP_Customize_Media_Control( $wp_customize, 'np_' . $key, $args + array( 'mime_type' => 'image' ) ) );
		} elseif ( 'color' === $type ) {
			$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'np_' . $key, $args ) );
		} else {
			$wp_customize->add_control( 'np_' . $key, $args + array( 'type' => $type ) );
		}
	};

	/* Logos & colours. */
	$add( 'header_logo', 'np_brand', __( 'Header logo', 'narayanganj-press' ), 'image', 'np_sanitize_image', array( 'description' => __( 'Blank = the site name as a wordmark. Recommended: transparent PNG/SVG-like WebP, 320×80.', 'narayanganj-press' ) ) );
	$add( 'footer_logo', 'np_brand', __( 'Footer logo', 'narayanganj-press' ), 'image', 'np_sanitize_image', array( 'description' => __( 'Shown on the dark footer, so use a light version. Blank = wordmark.', 'narayanganj-press' ) ) );
	$add( 'tagline', 'np_brand', __( 'Logo tagline', 'narayanganj-press' ), 'text', 'sanitize_text_field' );
	foreach ( np_colour_settings() as $key => $c ) {
		$add( $key, 'np_brand', $c[0], 'color', 'sanitize_hex_color' );
	}

	/* Footer imprint. */
	$imprint = array(
		'footer_about'   => array( __( 'Footer description', 'narayanganj-press' ), 'textarea', 'sanitize_textarea_field' ),
		'editor_label'   => array( __( 'Editor label (e.g. সম্পাদক ও প্রকাশক)', 'narayanganj-press' ), 'text', 'sanitize_text_field' ),
		'editor_name'    => array( __( 'Editor name', 'narayanganj-press' ), 'text', 'sanitize_text_field' ),
		'publisher_line' => array( __( 'Published-from line', 'narayanganj-press' ), 'text', 'sanitize_text_field' ),
		'office_address' => array( __( 'Office address (কার্যালয়)', 'narayanganj-press' ), 'text', 'sanitize_text_field' ),
		'news_phone'     => array( __( 'বার্তা বিভাগ: phone', 'narayanganj-press' ), 'text', 'sanitize_text_field' ),
		'news_email'     => array( __( 'বার্তা বিভাগ: email', 'narayanganj-press' ), 'email', 'sanitize_email' ),
		'ad_phone'       => array( __( 'বিজ্ঞাপন: phone', 'narayanganj-press' ), 'text', 'sanitize_text_field' ),
		'ad_email'       => array( __( 'বিজ্ঞাপন: email', 'narayanganj-press' ), 'email', 'sanitize_email' ),
		'info_phone'     => array( __( 'সাধারণ যোগাযোগ: phone', 'narayanganj-press' ), 'text', 'sanitize_text_field' ),
		'info_email'     => array( __( 'সাধারণ যোগাযোগ: email', 'narayanganj-press' ), 'email', 'sanitize_email' ),
		'copyright'      => array( __( 'Copyright holder', 'narayanganj-press' ), 'text', 'sanitize_text_field' ),
	);
	foreach ( $imprint as $key => $f ) {
		$add( $key, 'np_imprint', $f[0], $f[1], $f[2] );
	}

	/* Social links: on/off + URL for each network. */
	foreach ( np_social_networks() as $k => $label ) {
		/* translators: %s: social network. */
		$add( 'social_' . $k . '_on', 'np_social', sprintf( __( 'Show %s', 'narayanganj-press' ), $label ), 'checkbox', 'np_sanitize_checkbox' );
		/* translators: %s: social network. */
		$add( 'social_' . $k, 'np_social', sprintf( __( '%s link', 'narayanganj-press' ), $label ), 'url', 'esc_url_raw' );
	}

	/* Home page sections: category + title. */
	foreach ( np_home_sections() as $k => $s ) {
		if ( null !== $s['cat'] ) {
			$def_label = '' === $s['cat']
				? __( 'All categories (latest news)', 'narayanganj-press' )
				/* translators: %s: category slug. */
				: sprintf( __( 'Default (%s)', 'narayanganj-press' ), $s['cat'] );
			/* translators: %s: section name. */
			$add( 'home_' . $k . '_cat', 'np_home', sprintf( __( '%s — category', 'narayanganj-press' ), $s['label'] ), 'select', 'np_sanitize_cat', array( 'choices' => np_category_choices( $def_label ) ) );
		}
		/* translators: %s: section name. */
		$add( 'home_' . $k . '_title', 'np_home', sprintf( __( '%s — title', 'narayanganj-press' ), $s['label'] ), 'text', 'sanitize_text_field', array(
			'input_attrs' => array( 'placeholder' => $s['title'] ? $s['title'] : __( 'Blank = category name', 'narayanganj-press' ) ),
		) );
	}
	$add( 'ticker_count', 'np_home', __( 'Breaking ticker: number of headlines', 'narayanganj-press' ), 'number', 'absint' );

	/* Single post: Facebook Page box. */
	$add( 'fb_page_url', 'np_single', __( 'Facebook Page URL', 'narayanganj-press' ), 'url', 'np_sanitize_fb_url', array(
		'description' => __( 'e.g. https://www.facebook.com/YourPage — shown after "সর্বাধিক পঠিত" in the sidebar. Blank hides the box. The box loads only when scrolled near.', 'narayanganj-press' ),
	) );
	$add( 'fb_page_title', 'np_single', __( 'Box title', 'narayanganj-press' ), 'text', 'sanitize_text_field' );

	/* Misc. */
	$add( 'fb_app_id', 'np_misc', __( 'Facebook App ID (enables Messenger share on desktop)', 'narayanganj-press' ), 'text', 'absint' );
	$add( 'newsletter_action', 'np_misc', __( 'Newsletter form action URL (blank hides the form)', 'narayanganj-press' ), 'url', 'esc_url_raw' );
	$add( 'ad_placeholders', 'np_misc', __( 'Show reserved ad boxes when an ad widget area is empty', 'narayanganj-press' ), 'checkbox', 'np_sanitize_checkbox' );
}
add_action( 'customize_register', 'np_customize_register' );

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

/** "Theme Panel" link in the admin menu → opens the panel in the Customizer. */
function np_admin_menu() {
	add_menu_page(
		__( 'Theme Panel', 'narayanganj-press' ),
		__( 'Theme Panel', 'narayanganj-press' ),
		'edit_theme_options',
		'customize.php?autofocus[panel]=np_panel',
		'',
		'dashicons-admin-customizer',
		59
	);
}
add_action( 'admin_menu', 'np_admin_menu' );
