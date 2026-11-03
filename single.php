<?php
/**
 * Single post. No author box, byline or bio and no comments, by design.
 *
 * @package Khobor_Press
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();
	?>
<main id="main" class="wrap">
	<div class="layout">
		<article <?php post_class( 'post' ); ?>>
			<header class="post-head">
				<h1 class="post-title b"><?php the_title(); ?></h1>
				<?php if ( has_excerpt() ) : ?>
					<p class="post-sub"><?php echo esc_html( wp_strip_all_tags( get_the_excerpt() ) ); ?></p>
				<?php endif; ?>
			</header>

			<?php if ( has_post_thumbnail() ) : ?>
				<figure class="post-figure">
					<span class="thumb r-16x9 thumb-lg">
						<?php
						the_post_thumbnail( 'np-lead', array(
							'loading'       => 'eager',
							'fetchpriority' => 'high',
							'decoding'      => 'sync',
							'sizes'         => '(max-width: 820px) calc(100vw - 32px), 760px',
						) );
						?>
					</span>
					<?php $np_cap = get_the_post_thumbnail_caption(); if ( $np_cap ) : ?>
						<figcaption><?php echo esc_html( $np_cap ); ?></figcaption>
					<?php endif; ?>
				</figure>
			<?php endif; ?>

			<div class="post-meta">
				<?php np_meta_line(); ?>
				<?php np_share_row(); ?>
			</div>

			<div class="entry">
				<?php
				the_content();
				wp_link_pages( array( 'before' => '<nav class="page-links">', 'after' => '</nav>' ) );
				?>
			</div>

			<?php
			$np_tags = get_the_tags();
			// The ticker's "breaking" tag is an editorial flag, not a topic: keep it out of the tag list.
			$np_tags = $np_tags ? array_values( wp_list_filter( $np_tags, array( 'slug' => apply_filters( 'np_breaking_tag', np_opt( 'ticker_tag' ) ) ), 'NOT' ) ) : array();
			if ( $np_tags ) :
				?>
				<div class="post-tags">
					<span class="b"><?php esc_html_e( 'Tags:', 'khobor-press' ); ?></span>
					<ul class="pills pills-sm">
						<?php foreach ( $np_tags as $np_t ) : ?>
							<li><a href="<?php echo esc_url( get_tag_link( $np_t ) ); ?>" rel="tag"><?php echo esc_html( $np_t->name ); ?></a></li>
						<?php endforeach; ?>
					</ul>
				</div>
			<?php endif; ?>

			<div class="share-box">
				<p class="share-box-title b"><?php esc_html_e( 'Enjoyed this story? Share it', 'khobor-press' ); ?></p>
				<?php np_share_row(); ?>
			</div>

			<?php
			$np_prev = get_previous_post();
			$np_next = get_next_post();
			if ( $np_prev || $np_next ) :
				?>
				<nav class="post-nav" aria-label="<?php esc_attr_e( 'Previous and next stories', 'khobor-press' ); ?>">
					<?php if ( $np_prev ) : ?>
						<a class="pn pn-prev" href="<?php echo esc_url( get_permalink( $np_prev ) ); ?>" rel="prev"><span class="pn-k b">← <?php esc_html_e( 'Previous story', 'khobor-press' ); ?></span><span class="pn-t b"><?php echo esc_html( get_the_title( $np_prev ) ); ?></span></a>
					<?php endif; ?>
					<?php if ( $np_next ) : ?>
						<a class="pn pn-next" href="<?php echo esc_url( get_permalink( $np_next ) ); ?>" rel="next"><span class="pn-k b"><?php esc_html_e( 'Next story', 'khobor-press' ); ?> →</span><span class="pn-t b"><?php echo esc_html( get_the_title( $np_next ) ); ?></span></a>
					<?php endif; ?>
				</nav>
			<?php endif; ?>
		</article>

		<?php get_sidebar(); ?>
	</div>

	<?php
	$np_rel_cat = np_post_cat();
	$np_related = $np_rel_cat ? get_posts( array(
		'posts_per_page'      => 4,
		'post__not_in'        => array( get_the_ID() ),
		'category__in'        => array_filter( array( $np_rel_cat->term_id, $np_top_cat ? $np_top_cat->term_id : 0 ) ),
		'ignore_sticky_posts' => true,
		'no_found_rows'       => true,
	) ) : array();
	if ( $np_related ) :
		$np_more = $np_top_cat ? get_category_link( $np_top_cat ) : '';
		?>
		<section class="related" aria-labelledby="rel-h">
			<?php np_section_head( __( 'Related stories', 'khobor-press' ), $np_more, __( 'More →', 'khobor-press' ), 'rel-h' ); ?>
			<div class="grid-4 cards-m-compact">
				<?php foreach ( $np_related as $np_p ) { np_card( $np_p ); } ?>
			</div>
		</section>
	<?php endif; ?>
</main>
	<?php
endwhile;

get_footer();
