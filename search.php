<?php
/**
 * Search results.
 *
 * @package News_Press
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
				printf( esc_html__( 'Results for "%s"', 'news-press' ), esc_html( get_search_query() ) );
			} else {
				esc_html_e( 'All news', 'news-press' );
			}
			?>
		</h1>
		<?php if ( get_search_query() ) : ?>
			<p class="page-desc"><?php echo esc_html( sprintf( _n( '%s result found', '%s results found', (int) $wp_query->found_posts, 'news-press' ), np_bn_digits( (int) $wp_query->found_posts ) ) ); ?></p>
		<?php endif; ?>
		<div class="page-search"><?php get_search_form(); ?></div>
	</header>
	<?php get_template_part( 'template-parts/listing' ); ?>
</main>
<?php
get_footer();
