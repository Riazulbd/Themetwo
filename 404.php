<?php
/**
 * Not found.
 *
 * @package Khobor_Press
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<main id="main" class="wrap">
	<section class="nf">
		<p class="nf-code b" aria-hidden="true"><?php echo esc_html( np_bn_digits( '404' ) ); ?></p>
		<h1 class="page-title b"><?php esc_html_e( 'Page not found', 'khobor-press' ); ?></h1>
		<p class="page-desc"><?php esc_html_e( 'The link may be wrong or the page has been moved. Try searching below or go back to the home page.', 'khobor-press' ); ?></p>
		<div class="page-search"><?php get_search_form(); ?></div>
		<a class="btn btn-live b" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Back to home', 'khobor-press' ); ?></a>
	</section>
	<?php
	$np_latest = get_posts( array( 'posts_per_page' => 4, 'ignore_sticky_posts' => true, 'no_found_rows' => true ) );
	if ( $np_latest ) :
		?>
		<section class="related" aria-labelledby="nf-h">
			<?php np_section_head( __( 'Latest news', 'khobor-press' ), '', '', 'nf-h' ); ?>
			<div class="grid-4 cards-m-compact"><?php foreach ( $np_latest as $np_p ) { np_card( $np_p ); } ?></div>
		</section>
	<?php endif; ?>
</main>
<?php
get_footer();
