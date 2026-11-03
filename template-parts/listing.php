<?php
/**
 * Shared listing for archive, search and index: first post as a wide lead,
 * the rest as cards, Bangla-digit pagination, sidebar on desktop.
 *
 * @package News_Press
 */

defined( 'ABSPATH' ) || exit;
?>
<div class="layout">
	<div class="listing">
		<?php if ( have_posts() ) : ?>
			<?php
			$np_i = 0;
			while ( have_posts() ) :
				the_post();
				if ( 0 === $np_i && ! is_paged() ) :
					?>
					<article class="lead lead-archive">
						<a class="lead-link" href="<?php the_permalink(); ?>">
							<?php np_thumb( get_post(), 'np-lead', '(max-width: 820px) calc(100vw - 32px), 760px', true, 'r-16x9 thumb-lg' ); ?>
							<span class="lead-text">
								<?php $np_c = np_post_cat(); if ( $np_c ) : ?><span class="tags"><span class="badge b"><?php echo esc_html( $np_c->name ); ?></span></span><?php endif; ?>
								<h2 class="lead-title b"><?php the_title(); ?></h2>
								<span class="lead-excerpt"><?php echo esc_html( wp_strip_all_tags( get_the_excerpt() ) ); ?></span>
								<time class="meta" datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>"><?php echo esc_html( np_bn_ago() ); ?></time>
							</span>
						</a>
					</article>
					<div class="grid-3 cards-m-compact">
					<?php
				else :
					if ( 0 === $np_i ) {
						echo '<div class="grid-3 cards-m-compact">';
					}
					np_card( get_post(), 'h2' );
				endif;
				$np_i++;
			endwhile;
			echo '</div>';
			?>
			<?php
			$np_pages = paginate_links( array(
				'type'      => 'array',
				'prev_text' => __( '← Previous', 'news-press' ),
				'next_text' => __( 'Next →', 'news-press' ),
				'mid_size'  => 1,
			) );
			if ( $np_pages ) :
				?>
				<nav class="pagination" aria-label="<?php esc_attr_e( 'Pagination', 'news-press' ); ?>">
					<?php foreach ( $np_pages as $np_link ) { echo wp_kses_post( preg_replace_callback( '/>(\d+)</', function ( $m ) { return '>' . np_bn_digits( $m[1] ) . '<'; }, $np_link ) ); } ?>
				</nav>
			<?php endif; ?>
		<?php else : ?>
			<div class="empty">
				<p><?php esc_html_e( 'Sorry, nothing was found.', 'news-press' ); ?></p>
				<?php get_search_form(); ?>
			</div>
		<?php endif; ?>
	</div>
	<?php get_sidebar(); ?>
</div>
