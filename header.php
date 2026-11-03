<?php
/**
 * Site header: top bar with the centred date (plus the Bangla calendar date in Bangla mode), gradient
 * masthead, pill category nav (from the menu assigned to "Category navigation")
 * and (front page only) the breaking ticker.
 *
 * @package News_Press
 */

defined( 'ABSPATH' ) || exit;
?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<a class="skip-link" href="#main"><?php esc_html_e( 'Skip to content', 'news-press' ); ?></a>

<?php if ( np_opt( 'topbar_date' ) ) : ?>
<div class="topbar">
	<div class="wrap topbar-in">
		<p class="today">
			<span><?php echo esc_html( np_bn_date( null, true ) ); ?></span><?php if ( np_is_bangla() && np_opt( 'topbar_calendar' ) ) : ?><span class="cal"><span class="sep" aria-hidden="true">·</span><?php echo esc_html( np_bn_calendar_date() ); ?></span><?php endif; ?>
		</p>
	</div>
</div>
<?php endif; ?>

<header class="site-header" id="top">
	<div class="wrap">
		<div class="masthead">
			<div class="brand"><?php np_logo( 'header' ); ?></div>
			<div class="actions">
				<div class="hsearch"><?php get_search_form(); ?></div>
				<button class="icon-btn js-toggle" type="button" data-target="msearch" aria-controls="msearch" aria-expanded="false" aria-label="<?php esc_attr_e( 'Search news', 'news-press' ); ?>"><?php echo np_icon( 'search' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></button>
				<button class="icon-btn js-toggle" type="button" data-target="drawer" aria-controls="drawer" aria-expanded="false" aria-label="<?php esc_attr_e( 'Menu', 'news-press' ); ?>"><?php echo np_icon( 'menu' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></button>
			</div>
		</div>

		<div class="msearch" id="msearch" hidden><?php get_search_form(); ?></div>

		<nav class="catnav" aria-label="<?php esc_attr_e( 'Categories', 'news-press' ); ?>">
			<?php
			if ( has_nav_menu( 'primary' ) ) {
				wp_nav_menu( array(
					'theme_location' => 'primary',
					'container'      => false,
					'menu_class'     => 'pills',
					'depth'          => 1,
					'fallback_cb'    => false,
				) );
			} else {
				np_primary_fallback();
			}
			?>
		</nav>

		<?php
		if ( is_front_page() && np_opt( 'ticker_on' ) ) {
			get_template_part( 'template-parts/ticker' );
		}
		?>
	</div>
</header>

<div class="drawer" id="drawer" hidden>
	<nav aria-label="<?php esc_attr_e( 'All categories', 'news-press' ); ?>">
		<?php
		if ( has_nav_menu( 'primary' ) ) {
			wp_nav_menu( array( 'theme_location' => 'primary', 'container' => false, 'menu_class' => 'drawer-list', 'depth' => 1, 'fallback_cb' => false ) );
		} else {
			np_primary_fallback();
		}
		?>
	</nav>
</div>
