<?php
/**
 * Breaking ticker. Uses posts with the Theme Panel's ticker tag when there are any, otherwise
 * the newest headlines. Scrolls with a CSS-only animation (no JavaScript); it pauses on hover or focus and stays still for readers who prefer reduced motion.
 *
 * @package Khobor_Press
 */

defined( 'ABSPATH' ) || exit;

$np_count = max( 1, (int) np_opt( 'ticker_count' ) );
$np_label = np_opt( 'ticker_label' );
$np_args  = array( 'posts_per_page' => $np_count, 'ignore_sticky_posts' => true, 'no_found_rows' => true, 'update_post_term_cache' => false, 'update_post_meta_cache' => false ); // Titles and links only.
$np_tag   = get_term_by( 'slug', apply_filters( 'np_breaking_tag', np_opt( 'ticker_tag' ) ), 'post_tag' );
if ( $np_tag && $np_tag->count ) {
	$np_args['tag_id'] = $np_tag->term_id;
}
$np_items = get_posts( $np_args );
if ( ! $np_items ) {
	return;
}
?>
<?php
// Scroll speed: about 14 characters per second, never faster than 18s per loop.
$np_chars = 0;
foreach ( $np_items as $np_p ) {
	$np_chars += mb_strlen( get_the_title( $np_p ) ) + 4;
}
$np_dur = max( 18, (int) round( $np_chars / 14 ) );
?>
<div class="ticker" role="region" aria-label="<?php esc_attr_e( 'Breaking news', 'khobor-press' ); ?>">
	<span class="ticker-badge b"><span class="dot" aria-hidden="true"></span><?php echo esc_html( $np_label ); ?></span>
	<div class="ticker-view">
		<div class="ticker-track" style="--np-ticker:<?php echo (int) $np_dur; ?>s">
			<?php foreach ( array( false, true ) as $np_copy ) : // The second copy makes the loop seamless; screen readers and keyboards skip it. ?>
				<ul class="ticker-list"<?php echo $np_copy ? ' aria-hidden="true"' : ''; ?>>
					<?php foreach ( $np_items as $np_p ) : ?>
						<li><a href="<?php echo esc_url( get_permalink( $np_p ) ); ?>"<?php echo $np_copy ? ' tabindex="-1"' : ''; ?>><?php echo esc_html( get_the_title( $np_p ) ); ?></a></li>
					<?php endforeach; ?>
				</ul>
			<?php endforeach; ?>
		</div>
	</div>
</div>
