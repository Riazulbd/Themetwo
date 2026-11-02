<?php
/**
 * Breaking ticker. Uses posts tagged "breaking" when there are any, otherwise
 * the newest headlines. Static (no animation) so it costs no CPU.
 *
 * @package Narayanganj_Press
 */

defined( 'ABSPATH' ) || exit;

$np_count = max( 1, (int) np_opt( 'ticker_count' ) );
$np_args  = array( 'posts_per_page' => $np_count, 'ignore_sticky_posts' => true, 'no_found_rows' => true );
$np_tag   = get_term_by( 'slug', apply_filters( 'np_breaking_tag', 'breaking' ), 'post_tag' );
if ( $np_tag && $np_tag->count ) {
	$np_args['tag_id'] = $np_tag->term_id;
}
$np_items = get_posts( $np_args );
if ( ! $np_items ) {
	return;
}
?>
<div class="ticker" role="region" aria-label="<?php esc_attr_e( 'ব্রেকিং নিউজ', 'narayanganj-press' ); ?>">
	<span class="ticker-badge b"><span class="dot" aria-hidden="true"></span><?php esc_html_e( 'ব্রেকিং', 'narayanganj-press' ); ?></span>
	<ul class="ticker-list">
		<?php foreach ( $np_items as $np_p ) : ?>
			<li><a href="<?php echo esc_url( get_permalink( $np_p ) ); ?>"><?php echo esc_html( get_the_title( $np_p ) ); ?></a></li>
		<?php endforeach; ?>
	</ul>
</div>
