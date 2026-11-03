<?php
/**
 * Fallback template (also the blog index when a static front page is set).
 *
 * @package Khobor_Press
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<main id="main" class="wrap">
	<header class="page-head">
		<?php np_breadcrumbs(); ?>
		<h1 class="page-title b"><?php echo is_home() && get_option( 'page_for_posts' ) ? esc_html( get_the_title( get_option( 'page_for_posts' ) ) ) : esc_html__( 'Latest news', 'khobor-press' ); ?></h1>
	</header>
	<?php get_template_part( 'template-parts/listing' ); ?>
</main>
<?php
get_footer();
