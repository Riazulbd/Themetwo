<?php
/**
 * Not found.
 *
 * @package Narayanganj_Press
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<main id="main" class="wrap">
	<section class="nf">
		<p class="nf-code b" aria-hidden="true">৪০৪</p>
		<h1 class="page-title b"><?php esc_html_e( 'পাতাটি খুঁজে পাওয়া যায়নি', 'narayanganj-press' ); ?></h1>
		<p class="page-desc"><?php esc_html_e( 'লিংকটি ভুল অথবা খবরটি সরিয়ে নেওয়া হয়েছে। নিচে খুঁজে দেখুন অথবা প্রচ্ছদে ফিরে যান।', 'narayanganj-press' ); ?></p>
		<div class="page-search"><?php get_search_form(); ?></div>
		<a class="btn btn-live b" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'প্রচ্ছদে ফিরুন', 'narayanganj-press' ); ?></a>
	</section>
	<?php
	$np_latest = get_posts( array( 'posts_per_page' => 4, 'ignore_sticky_posts' => true, 'no_found_rows' => true ) );
	if ( $np_latest ) :
		?>
		<section class="related" aria-labelledby="nf-h">
			<?php np_section_head( 'সর্বশেষ খবর', '', '', 'nf-h' ); ?>
			<div class="grid-4 cards-m-compact"><?php foreach ( $np_latest as $np_p ) { np_card( $np_p ); } ?></div>
		</section>
	<?php endif; ?>
</main>
<?php
get_footer();
