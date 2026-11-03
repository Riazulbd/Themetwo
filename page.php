<?php
/**
 * Static page (about, contact, policies). Single readable column.
 *
 * @package News_Press
 */

defined( 'ABSPATH' ) || exit;

get_header();
while ( have_posts() ) :
	the_post();
	?>
<main id="main" class="wrap">
	<article <?php post_class( 'page-article' ); ?>>
		<header class="page-head">
			<?php np_breadcrumbs(); ?>
			<h1 class="page-title b"><?php the_title(); ?></h1>
		</header>
		<?php if ( has_post_thumbnail() ) : ?>
			<figure class="post-figure"><span class="thumb r-16x9 thumb-lg"><?php the_post_thumbnail( 'np-lead', array( 'loading' => 'eager', 'fetchpriority' => 'high' ) ); ?></span></figure>
		<?php endif; ?>
		<div class="entry">
			<?php
			the_content();
			wp_link_pages( array( 'before' => '<nav class="page-links">', 'after' => '</nav>' ) );
			?>
		</div>
	</article>
</main>
	<?php
endwhile;
get_footer();
