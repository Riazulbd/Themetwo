<?php
/**
 * Article / archive sidebar: most-read card, Facebook Page box (Theme Panel),
 * latest news, optional widgets, 300×250 ad. On phones only the most-read card
 * and the Facebook box are shown.
 *
 * @package News_Press
 */

defined( 'ABSPATH' ) || exit;
?>
<aside class="sidebar" aria-label="<?php esc_attr_e( 'Sidebar', 'news-press' ); ?>">
	<?php np_most_read( 5 ); ?>
	<?php np_fb_page_box(); ?>
	<div class="d-only sidebar-rest">
		<?php np_latest_box( 5, is_singular() ? array( get_queried_object_id() ) : array() ); ?>
		<?php if ( is_active_sidebar( 'sidebar-extra' ) ) : ?>
			<div class="box"><?php dynamic_sidebar( 'sidebar-extra' ); ?></div>
		<?php endif; ?>
		<?php np_ad( 'ad-sidebar', 'ad-rect', '300×250' ); ?>
	</div>
</aside>
