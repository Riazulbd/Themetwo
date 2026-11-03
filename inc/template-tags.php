<?php
/**
 * Template tags: logo, cards, section heads, meta line, share row,
 * breadcrumbs, ad slots, most-read card, socials. All pluggable.
 *
 * @package Khobor_Press
 */

defined( 'ABSPATH' ) || exit;

/* ---------------------------------------------------------------------------
 * Query helpers: keep track of posts already shown on a page so home sections
 * never repeat a story.
 * ------------------------------------------------------------------------- */

/**
 * Get posts while excluding ones already rendered on this request.
 *
 * @param array $args WP_Query args.
 * @param bool  $mark Mark the results as shown.
 * @return WP_Post[]
 */
function np_get_posts( $args, $mark = true ) {
	global $np_shown;
	$np_shown = is_array( $np_shown ) ? $np_shown : array();
	$args     = wp_parse_args( $args, array(
		'post_type'           => 'post',
		'post_status'         => 'publish',
		'posts_per_page'      => 4,
		'ignore_sticky_posts' => true,
		'no_found_rows'       => true,
		'post__not_in'        => $np_shown,
	) );
	$posts    = get_posts( $args + array( 'suppress_filters' => false ) );
	if ( $mark ) {
		$np_shown = array_merge( $np_shown, wp_list_pluck( $posts, 'ID' ) );
	}
	return $posts;
}

/** Mark a post as shown. */
function np_mark_shown( $id ) {
	global $np_shown;
	$np_shown   = is_array( $np_shown ) ? $np_shown : array();
	$np_shown[] = (int) $id;
}

/* ---------------------------------------------------------------------------
 * Categories
 * ------------------------------------------------------------------------- */

if ( ! function_exists( 'np_post_terms_chain' ) ) {
	/**
	 * The deepest category of a post and its ancestors, top-level first.
	 *
	 * @param int|WP_Post|null $post Post.
	 * @return WP_Term[]
	 */
	function np_post_terms_chain( $post = null ) {
		$cats = get_the_category( $post ? ( is_object( $post ) ? $post->ID : $post ) : 0 );
		if ( empty( $cats ) ) {
			return array();
		}
		$deepest = null;
		$depth   = -1;
		foreach ( $cats as $c ) {
			$d = count( get_ancestors( $c->term_id, 'category' ) );
			if ( $d > $depth ) {
				$depth   = $d;
				$deepest = $c;
			}
		}
		$chain = array();
		foreach ( array_reverse( get_ancestors( $deepest->term_id, 'category' ) ) as $aid ) {
			$t = get_term( $aid, 'category' );
			if ( $t && ! is_wp_error( $t ) ) {
				$chain[] = $t;
			}
		}
		$chain[] = $deepest;
		return $chain;
	}
}

/** The most specific category (used on cards). */
function np_post_cat( $post = null ) {
	$chain = np_post_terms_chain( $post );
	return $chain ? end( $chain ) : null;
}

/** The top-level category (used for the red pill on single). */
function np_post_top_cat( $post = null ) {
	$chain = np_post_terms_chain( $post );
	return $chain ? $chain[0] : null;
}

/* ---------------------------------------------------------------------------
 * Branding
 * ------------------------------------------------------------------------- */

if ( ! function_exists( 'np_logo' ) ) {
	/**
	 * Logo: custom logo if set, otherwise the two-colour wordmark.
	 *
	 * @param string $variant 'header' | 'footer'.
	 * @param bool   $tagline Show the tagline under the wordmark.
	 */
	function np_logo( $variant = 'header', $tagline = true ) {
		$tag = ( 'header' === $variant && ( is_front_page() || is_home() ) ) ? 'h1' : 'p';
		echo '<' . $tag . ' class="logo logo-' . esc_attr( $variant ) . '">';
		echo '<a href="' . esc_url( home_url( '/' ) ) . '" rel="home">';
		// Theme Panel logo per variant; the header also falls back to Site Identity's logo.
		$id = (int) np_opt( $variant . '_logo' );
		if ( ! $id && 'header' === $variant && has_custom_logo() ) {
			$id = (int) get_theme_mod( 'custom_logo' );
		}
		if ( $id && wp_attachment_is_image( $id ) ) {
			echo wp_get_attachment_image( $id, 'full', false, array(
				'class'    => 'logo-img',
				'alt'      => get_bloginfo( 'name' ),
				'loading'  => 'header' === $variant ? 'eager' : 'lazy',
				'decoding' => 'async',
			) );
		} else {
			$name  = get_bloginfo( 'name' );
			$parts = preg_split( '/\s+/u', trim( $name ) );
			$last  = count( $parts ) > 1 ? array_pop( $parts ) : '';
			echo '<span class="wm b"><span class="wm-a">' . esc_html( implode( ' ', $parts ) ) . '</span>';
			if ( $last ) {
				echo ' <span class="wm-b">' . esc_html( $last ) . '</span>';
			}
			echo '</span>';
		}
		echo '</a></' . $tag . '>';
		if ( $tagline && np_opt( 'tagline' ) ) {
			echo '<p class="logo-tag">' . esc_html( np_opt( 'tagline' ) ) . '</p>';
		}
	}
}

/* ---------------------------------------------------------------------------
 * Images
 * ------------------------------------------------------------------------- */

if ( ! function_exists( 'np_thumb' ) ) {
	/**
	 * Featured image inside an aspect-ratio box, or a neutral placeholder.
	 *
	 * @param WP_Post $post  Post.
	 * @param string  $size  Image size.
	 * @param string  $sizes `sizes` attribute.
	 * @param bool    $lcp   Above-the-fold hero: eager + fetchpriority=high.
	 * @param string  $ratio CSS class for the ratio box (r-16x9, r-5x3, r-3x2).
	 * @param bool    $video Overlay a play button.
	 */
	function np_thumb( $post, $size = 'np-card', $sizes = '(max-width: 600px) 100vw, 300px', $lcp = false, $ratio = 'r-5x3', $video = false ) {
		echo '<span class="thumb ' . esc_attr( $ratio ) . '">';
		if ( has_post_thumbnail( $post ) ) {
			$attr = array(
				'sizes'    => $sizes,
				'alt'      => '',
				'loading'  => $lcp ? 'eager' : 'lazy',
				'decoding' => $lcp ? 'sync' : 'async',
			);
			if ( $lcp ) {
				$attr['fetchpriority'] = 'high';
			}
			echo get_the_post_thumbnail( $post, $size, $attr ); // phpcs:ignore WordPress.Security.EscapeOutput -- core-generated markup.
		} else {
			echo '<span class="ph" aria-hidden="true">' . esc_html__( 'Image', 'khobor-press' ) . '</span>';
		}
		if ( $video ) {
			echo '<span class="play" aria-hidden="true">' . np_icon( 'play', 20 ) . '</span>'; // phpcs:ignore WordPress.Security.EscapeOutput
		}
		echo '</span>';
	}
}

/* ---------------------------------------------------------------------------
 * Cards
 * ------------------------------------------------------------------------- */

if ( ! function_exists( 'np_card' ) ) {
	/**
	 * Grid card: image, category badge, headline, time.
	 *
	 * @param WP_Post $post    Post.
	 * @param string  $heading Heading tag.
	 */
	function np_card( $post, $heading = 'h3' ) {
		$cat = np_post_cat( $post );
		?>
		<article class="card">
			<a class="card-link" href="<?php echo esc_url( get_permalink( $post ) ); ?>">
				<?php np_thumb( $post, 'np-card', '(max-width: 600px) calc(100vw - 32px), (max-width: 1100px) 45vw, 300px' ); ?>
				<span class="card-body">
					<?php if ( $cat ) : ?><span class="badge"><?php echo esc_html( $cat->name ); ?></span><?php endif; ?>
					<<?php echo tag_escape( $heading ); ?> class="card-title b"><?php echo esc_html( get_the_title( $post ) ); ?></<?php echo tag_escape( $heading ); ?>>
					<time class="meta" datetime="<?php echo esc_attr( get_the_date( 'c', $post ) ); ?>"><?php echo esc_html( np_bn_ago( $post ) ); ?></time>
				</span>
			</a>
		</article>
		<?php
	}
}

if ( ! function_exists( 'np_compact' ) ) {
	/**
	 * Compact row: thumbnail left, category kicker + headline right.
	 *
	 * @param WP_Post $post    Post.
	 * @param string  $heading Heading tag.
	 * @param bool    $show_cat Show the category kicker.
	 */
	function np_compact( $post, $heading = 'h3', $show_cat = true ) {
		$cat = np_post_cat( $post );
		?>
		<article class="compact">
			<a class="compact-link" href="<?php echo esc_url( get_permalink( $post ) ); ?>">
				<?php np_thumb( $post, 'np-thumb', '120px', false, 'r-3x2' ); ?>
				<span class="compact-text">
					<?php if ( $show_cat && $cat ) : ?><span class="kicker b"><?php echo esc_html( $cat->name ); ?></span><?php endif; ?>
					<<?php echo tag_escape( $heading ); ?> class="compact-title b"><?php echo esc_html( get_the_title( $post ) ); ?></<?php echo tag_escape( $heading ); ?>>
				</span>
			</a>
		</article>
		<?php
	}
}

if ( ! function_exists( 'np_section_head' ) ) {
	/**
	 * Section header: red bar + title, "more" link on the right.
	 *
	 * @param string $title Title.
	 * @param string $url   More link (optional).
	 * @param string $more  More label.
	 * @param string $id    Heading id for aria-labelledby.
	 */
	function np_section_head( $title, $url = '', $more = null, $id = '' ) {
		if ( null === $more ) {
			$more = __( 'All news →', 'khobor-press' );
		}
		echo '<div class="sec-head">';
		printf( '<h2 class="sec-title b"%s>%s</h2>', $id ? ' id="' . esc_attr( $id ) . '"' : '', esc_html( $title ) );
		if ( $url && $more ) {
			printf( '<a class="sec-more b" href="%s">%s</a>', esc_url( $url ), esc_html( $more ) );
		}
		echo '</div>';
	}
}

/* ---------------------------------------------------------------------------
 * Single post pieces
 * ------------------------------------------------------------------------- */

if ( ! function_exists( 'np_meta_line' ) ) {
	/** "Published: … · Updated: … · N min read" — no author, by design. */
	function np_meta_line( $post = null ) {
		$post     = get_post( $post );
		$pub      = get_post_timestamp( $post );
		$mod      = get_post_timestamp( $post, 'modified' );
		$same_day = wp_date( 'Ymd', $pub ) === wp_date( 'Ymd', $mod );
		echo '<p class="meta-line">';
		printf( '<span>%s <time datetime="%s">%s</time></span>', esc_html__( 'Published:', 'khobor-press' ), esc_attr( get_the_date( 'c', $post ) ), esc_html( np_bn_date( $pub, false, true ) ) );
		if ( $mod > $pub + 5 * MINUTE_IN_SECONDS ) {
			printf( '<span class="sep" aria-hidden="true">·</span><span>%s <time datetime="%s">%s</time></span>',
				esc_html__( 'Updated:', 'khobor-press' ),
				esc_attr( get_the_modified_date( 'c', $post ) ),
				esc_html( $same_day ? np_bn_time( $mod ) : np_bn_date( $mod, false, true ) )
			);
		}
		printf( '<span class="sep" aria-hidden="true">·</span><span class="rt">%s</span>', esc_html( sprintf( /* translators: %s: minutes. */ __( '%s min read', 'khobor-press' ), np_bn_digits( np_reading_minutes( $post ) ) ) ) );
		echo '</p>';
	}
}

if ( ! function_exists( 'np_share_row' ) ) {
	/**
	 * Share buttons: Facebook, WhatsApp, Messenger (official colours) + copy link.
	 * Desktop shows labelled pills; under 640px they collapse to 44px circles.
	 *
	 * @param WP_Post|null $post Post.
	 */
	function np_share_row( $post = null ) {
		$post  = get_post( $post );
		$url   = get_permalink( $post );
		$title = wp_strip_all_tags( get_the_title( $post ) );
		$enc   = rawurlencode( $url );
		$app   = np_opt( 'fb_app_id' );
		$ms    = $app
			? 'https://www.facebook.com/dialog/send?app_id=' . rawurlencode( $app ) . '&link=' . $enc . '&redirect_uri=' . $enc
			: 'fb-messenger://share/?link=' . $enc;
		$items = apply_filters( 'np_share_items', array(
			'facebook'  => array( __( 'Facebook', 'khobor-press' ), 'https://www.facebook.com/sharer/sharer.php?u=' . $enc, __( 'Share on Facebook', 'khobor-press' ) ),
			'whatsapp'  => array( __( 'WhatsApp', 'khobor-press' ), 'https://wa.me/?text=' . rawurlencode( $title . ' ' . $url ), __( 'Share on WhatsApp', 'khobor-press' ) ),
			'messenger' => array( __( 'Messenger', 'khobor-press' ), $ms, __( 'Share on Messenger', 'khobor-press' ) ),
		), $post );
		echo '<div class="share" data-share>';
		echo '<span class="share-label b">' . esc_html__( 'Share:', 'khobor-press' ) . '</span>';
		foreach ( $items as $key => $it ) {
			printf(
				'<a class="sh sh-%1$s" href="%2$s" target="_blank" rel="noopener nofollow" aria-label="%3$s"%5$s>%4$s<span class="sh-t b">%6$s</span></a>',
				esc_attr( $key ),
				esc_url( $it[1], array( 'https', 'http', 'fb-messenger' ) ),
				esc_attr( $it[2] ),
				np_icon( $key ), // phpcs:ignore WordPress.Security.EscapeOutput
				( 'messenger' === $key && ! $app ) ? ' data-ms-fallback' : '',
				esc_html( $it[0] )
			);
		}
		printf(
			'<button class="sh sh-copy" type="button" data-copy="%1$s" aria-label="%2$s">%3$s<span class="sh-t">%4$s</span></button>',
			esc_attr( $url ),
			esc_attr__( 'Copy the link', 'khobor-press' ),
			np_icon( 'copy', 16 ), // phpcs:ignore WordPress.Security.EscapeOutput
			esc_html__( 'Copy link', 'khobor-press' )
		);
		echo '<span class="sh-status" role="status" aria-live="polite"></span>';
		echo '</div>';
	}
}

if ( ! function_exists( 'np_breadcrumbs' ) ) {
	/** Visible breadcrumb (its JSON-LD twin is in inc/seo.php). */
	function np_breadcrumbs() {
		$items = np_breadcrumb_items();
		if ( count( $items ) < 2 ) {
			return;
		}
		echo '<nav class="crumbs" aria-label="' . esc_attr__( 'Breadcrumb', 'khobor-press' ) . '"><ol>';
		$last = count( $items ) - 1;
		foreach ( $items as $i => $it ) {
			if ( $i === $last && is_singular() ) {
				echo '<li><a href="' . esc_url( $it['url'] ) . '">' . esc_html( $it['name'] ) . '</a></li>';
			} elseif ( $i === $last ) {
				echo '<li aria-current="page">' . esc_html( $it['name'] ) . '</li>';
			} else {
				echo '<li><a href="' . esc_url( $it['url'] ) . '">' . esc_html( $it['name'] ) . '</a></li>';
			}
		}
		echo '</ol></nav>';
	}
}

/**
 * Breadcrumb trail for the current view. For single posts it ends with the
 * deepest category (as in the design), not the headline.
 *
 * @return array<int,array{name:string,url:string}>
 */
function np_breadcrumb_items() {
	$items = array( array( 'name' => __( 'Home', 'khobor-press' ), 'url' => home_url( '/' ) ) );
	if ( is_single() ) {
		foreach ( np_post_terms_chain( get_queried_object_id() ) as $t ) {
			$items[] = array( 'name' => $t->name, 'url' => get_term_link( $t ) );
		}
	} elseif ( is_category() || is_tag() || is_tax() ) {
		$term = get_queried_object();
		if ( $term && is_taxonomy_hierarchical( $term->taxonomy ) ) {
			foreach ( array_reverse( get_ancestors( $term->term_id, $term->taxonomy ) ) as $aid ) {
				$t       = get_term( $aid, $term->taxonomy );
				$items[] = array( 'name' => $t->name, 'url' => get_term_link( $t ) );
			}
		}
		if ( $term ) {
			$items[] = array( 'name' => $term->name, 'url' => get_term_link( $term ) );
		}
	} elseif ( is_page() ) {
		$items[] = array( 'name' => get_the_title(), 'url' => get_permalink() );
	} elseif ( is_search() ) {
		$items[] = array( 'name' => __( 'Search results', 'khobor-press' ), 'url' => get_search_link() );
	} elseif ( is_date() ) {
		$items[] = array( 'name' => __( 'Archive', 'khobor-press' ), 'url' => '' );
	}
	return apply_filters( 'np_breadcrumb_items', $items );
}

/* ---------------------------------------------------------------------------
 * Ads
 * ------------------------------------------------------------------------- */

if ( ! function_exists( 'np_ad' ) ) {
	/**
	 * Ad slot with reserved space (prevents layout shift).
	 *
	 * @param string $area  Widget area id.
	 * @param string $class Size class: ad-leader (728×90), ad-rect (300×250), ad-inline (728×90 desktop / 300×250 mobile).
	 * @param string $label Placeholder size label.
	 */
	function np_ad( $area, $class = 'ad-leader', $label = '728×90' ) {
		$active = is_active_sidebar( $area );
		if ( ! $active && ! np_opt( 'ad_placeholders' ) ) {
			return;
		}
		echo '<aside class="ad ' . esc_attr( $class . ( $active ? '' : ' ad-empty' ) ) . '" aria-label="' . esc_attr__( 'Advertisement', 'khobor-press' ) . '">';
		if ( $active ) {
			dynamic_sidebar( $area );
		} else {
			echo '<span class="ad-k b">' . esc_html__( 'Advertisement', 'khobor-press' ) . '</span><span class="ad-s">' . esc_html( $label ) . '</span>';
		}
		echo '</aside>';
	}
}

/** Insert the in-article ad after the 3rd paragraph of single posts. */
function np_inline_ad( $content ) {
	if ( ! is_singular( 'post' ) || ! in_the_loop() || ! is_main_query() ) {
		return $content;
	}
	$after = (int) apply_filters( 'np_inline_ad_after_paragraph', 3 );
	$parts = explode( '</p>', $content );
	if ( count( $parts ) <= $after + 1 ) {
		return $content;
	}
	ob_start();
	np_ad( 'ad-single-inline', 'ad-inline', '728×90' );
	$ad = ob_get_clean();
	if ( '' === $ad ) {
		return $content;
	}
	$out  = '';
	$last = count( $parts ) - 1;
	foreach ( $parts as $i => $part ) {
		$out .= $part . ( $i < $last ? '</p>' : '' );
		if ( $i === $after - 1 ) {
			$out .= $ad;
		}
	}
	return $out;
}
add_filter( 'the_content', 'np_inline_ad', 20 );

/* ---------------------------------------------------------------------------
 * Most read card
 * ------------------------------------------------------------------------- */

if ( ! function_exists( 'np_most_read' ) ) {
	/** Dark "Most read" card with big numbers. */
	function np_most_read( $count = 5 ) {
		$posts = np_most_read_posts( $count );
		if ( ! $posts ) {
			return;
		}
		?>
		<section class="most" aria-labelledby="most-h">
			<div class="most-head">
				<h2 class="most-title b" id="most-h"><?php echo esc_html( np_home_section( 'most' )['title'] ); ?></h2>
				<span class="badge badge-dark b"><?php esc_html_e( 'Today', 'khobor-press' ); ?></span>
			</div>
			<ol class="most-list">
				<?php foreach ( $posts as $i => $p ) : ?>
					<li><a href="<?php echo esc_url( get_permalink( $p ) ); ?>"><span class="most-n b" aria-hidden="true"><?php echo esc_html( np_bn_digits( sprintf( '%02d', $i + 1 ) ) ); ?></span><span class="most-t b"><?php echo esc_html( get_the_title( $p ) ); ?></span></a></li>
				<?php endforeach; ?>
			</ol>
		</section>
		<?php
	}
}

if ( ! function_exists( 'np_latest_box' ) ) {
	/** Sidebar "Latest" list with relative times. */
	function np_latest_box( $count = 5, $exclude = array() ) {
		$posts = get_posts( array(
			'posts_per_page'      => $count,
			'post__not_in'        => $exclude,
			'ignore_sticky_posts' => true,
			'no_found_rows'       => true,
		) );
		if ( ! $posts ) {
			return;
		}
		echo '<section class="box latest" aria-labelledby="latest-h"><h2 class="box-title b" id="latest-h">' . esc_html__( 'Latest', 'khobor-press' ) . '</h2><ul>';
		foreach ( $posts as $p ) {
			printf(
				'<li><a href="%1$s"><time class="meta brand" datetime="%2$s">%3$s</time><span class="latest-t b">%4$s</span></a></li>',
				esc_url( get_permalink( $p ) ),
				esc_attr( get_the_date( 'c', $p ) ),
				esc_html( np_bn_ago( $p ) ),
				esc_html( get_the_title( $p ) )
			);
		}
		echo '</ul></section>';
	}
}

if ( ! function_exists( 'np_fb_page_box' ) ) {
	/**
	 * Facebook Page box (Theme Panel → Sharing & ads). A lazy iframe: nothing from
	 * Facebook loads until the reader scrolls near it, and its height is reserved (no CLS).
	 */
	function np_fb_page_box() {
		$url = np_opt( 'fb_page_url' );
		if ( ! $url ) {
			return;
		}
		$src = add_query_arg( array(
			'href'                  => rawurlencode( $url ),
			'tabs'                  => 'timeline',
			'width'                 => 500,
			'height'                => 500,
			'small_header'          => 'false',
			'adapt_container_width' => 'true',
			'hide_cover'            => 'false',
			'show_facepile'         => 'true',
			'locale'                => 0 === strpos( get_locale(), 'bn' ) ? 'bn_IN' : get_locale(),
		), 'https://www.facebook.com/plugins/page.php' );
		$title = np_opt( 'fb_page_title' );
		echo '<section class="box fbpage"' . ( $title ? ' aria-labelledby="fbpage-h"' : ' aria-label="Facebook"' ) . '>';
		if ( $title ) {
			echo '<h2 class="box-title b" id="fbpage-h">' . esc_html( $title ) . '</h2>';
		}
		printf(
			'<iframe class="fbpage-frame" src="%1$s" width="500" height="500" loading="lazy" title="%2$s" allow="encrypted-media" referrerpolicy="strict-origin-when-cross-origin"></iframe>',
			esc_url( $src ),
			esc_attr( sprintf( /* translators: %s: site name. */ __( '%s on Facebook', 'khobor-press' ), get_bloginfo( 'name' ) ) )
		);
		echo '</section>';
	}
}

/* ---------------------------------------------------------------------------
 * Navigation fallbacks (work before menus are assigned)
 * ------------------------------------------------------------------------- */

/** Primary pills fallback: Home + top-level categories. */
function np_primary_fallback() {
	$cats = get_categories( array( 'parent' => 0, 'hide_empty' => false, 'exclude' => array( get_option( 'default_category' ) ), 'number' => 11, 'orderby' => 'term_id' ) );
	echo '<ul class="pills">';
	printf( '<li class="%s"><a href="%s">%s</a></li>', is_front_page() ? 'current-menu-item' : '', esc_url( home_url( '/' ) ), esc_html__( 'Home', 'khobor-press' ) );
	$active = is_single() ? np_post_top_cat() : ( is_category() ? get_queried_object() : null );
	foreach ( $cats as $c ) {
		$cur = $active && (int) $active->term_id === (int) $c->term_id;
		printf( '<li class="%s"><a href="%s">%s</a></li>', $cur ? 'current-menu-item' : '', esc_url( get_category_link( $c ) ), esc_html( $c->name ) );
	}
	echo '</ul>';
}

/**
 * Simple link list fallback.
 *
 * @param array<int,array{0:string,1:string}> $links Label/URL pairs.
 * @param string $class List class.
 */
function np_link_list( $links, $class ) {
	echo '<ul class="' . esc_attr( $class ) . '">';
	foreach ( $links as $l ) {
		printf( '<li><a href="%s">%s</a></li>', esc_url( $l[1] ), esc_html( $l[0] ) );
	}
	echo '</ul>';
}

/**
 * Render a menu location, or a fallback link list.
 *
 * @param string $location Menu location.
 * @param string $class    List class.
 * @param array  $fallback Label/URL pairs.
 * @param string $label    aria-label for the nav.
 */
function np_menu( $location, $class, $fallback, $label ) {
	echo '<nav class="nav-' . esc_attr( $location ) . '" aria-label="' . esc_attr( $label ) . '">';
	if ( has_nav_menu( $location ) ) {
		wp_nav_menu( array(
			'theme_location' => $location,
			'container'      => false,
			'menu_class'     => $class,
			'depth'          => 1,
			'fallback_cb'    => false,
		) );
	} else {
		np_link_list( $fallback, $class );
	}
	echo '</nav>';
}

/** Default page URL by slug (falls back to /slug/ before the page exists). */
function np_page_url( $slug ) {
	$p = get_page_by_path( $slug );
	return $p ? get_permalink( $p ) : home_url( '/' . $slug . '/' );
}

/* ---------------------------------------------------------------------------
 * Social icons
 * ------------------------------------------------------------------------- */

/** Round brand-coloured social icons (only networks switched on in the Theme Panel and with a URL). */
function np_socials() {
	$items = array();
	foreach ( np_social_networks() as $key => $label ) {
		if ( np_opt( 'social_' . $key . '_on' ) && np_opt( 'social_' . $key ) ) {
			$items[ $key ] = $label;
		}
	}
	if ( ! $items ) {
		return;
	}
	echo '<ul class="socials">';
	foreach ( $items as $key => $label ) {
		$url = np_opt( 'social_' . $key );
		printf(
			'<li><a class="so so-%1$s" href="%2$s" target="_blank" rel="noopener" aria-label="%3$s">%4$s</a></li>',
			esc_attr( $key ),
			esc_url( $url ),
			/* translators: %s: social network. */
			esc_attr( sprintf( /* translators: 1: site name, 2: social network. */ __( '%1$s on %2$s', 'khobor-press' ), get_bloginfo( 'name' ), $label ) ),
			np_icon( $key ) // phpcs:ignore WordPress.Security.EscapeOutput
		);
	}
	echo '</ul>';
}
