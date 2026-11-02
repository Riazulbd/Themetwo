<?php
/**
 * Search results.
 *
 * @package Narayanganj_Press
 */

defined( 'ABSPATH' ) || exit;

get_header();
global $wp_query;
?>
<main id="main" class="wrap">
	<header class="page-head">
		<?php np_breadcrumbs(); ?>
		<h1 class="page-title b">
			<?php
			if ( get_search_query() ) {
				/* translators: %s: search term. */
				printf( esc_html__( '"%s" এর ফলাফল', 'narayanganj-press' ), esc_html( get_search_query() ) );
			} else {
				esc_html_e( 'সব খবর', 'narayanganj-press' );
			}
			?>
		</h1>
		<?php if ( get_search_query() ) : ?>
			<p class="page-desc"><?php echo esc_html( np_bn_digits( (int) $wp_query->found_posts ) . ' টি খবর পাওয়া গেছে' ); ?></p>
		<?php endif; ?>
		<div class="page-search"><?php get_search_form(); ?></div>
	</header>
	<?php get_template_part( 'template-parts/listing' ); ?>
</main>
<?php
get_footer();
