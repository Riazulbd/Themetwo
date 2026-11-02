<?php
/**
 * Front page: lead + side stories, top-news grid, local (Narayanganj) section
 * with upazila pills, most-read card, three category columns, video band.
 * Each section's category and title come from Theme Panel → Home page sections.
 *
 * Section order on mobile follows the mobile design (lead → latest → most read
 * → top news → local → columns → video) via CSS `order`, without duplicate markup.
 *
 * @package Narayanganj_Press
 */

defined( 'ABSPATH' ) || exit;

get_header();

$np_lead = np_front_lead_post();
if ( $np_lead ) {
	np_mark_shown( $np_lead->ID );
}
/**
 * Posts for a home section: from its category, or the latest overall when none is set.
 *
 * @param array $sec   Section from np_home_section().
 * @param int   $count Number of posts.
 * @return WP_Post[]
 */
$np_sec_posts = function ( $sec, $count ) {
	if ( $sec['hidden'] ) {
		return array();
	}
	$args = array( 'posts_per_page' => $count );
	if ( $sec['cat'] ) {
		$args['cat'] = $sec['cat']->term_id;
	}
	return np_get_posts( $args );
};

$np_side_s = np_home_section( 'side' );
$np_top_s  = np_home_section( 'top' );
$np_loc_s  = np_home_section( 'local' );
$np_side   = $np_sec_posts( $np_side_s, 4 );
$np_top    = $np_sec_posts( $np_top_s, 4 );

$np_local      = $np_loc_s['cat'];
$np_local_list = $np_local ? $np_sec_posts( $np_loc_s, 4 ) : array();
?>
<main id="main" class="wrap home-main">

	<?php if ( is_active_sidebar( 'ad-header' ) ) { np_ad( 'ad-header', 'ad-leader' ); } ?>

	<?php if ( $np_lead ) : ?>
	<div class="hero">
		<article class="lead">
			<a class="lead-link" href="<?php echo esc_url( get_permalink( $np_lead ) ); ?>">
				<?php np_thumb( $np_lead, 'np-lead', '(max-width: 820px) calc(100vw - 32px), 744px', true, 'r-16x9 thumb-lg' ); ?>
				<span class="lead-text">
					<span class="tags">
						<span class="badge badge-solid b"><?php esc_html_e( 'প্রধান খবর', 'narayanganj-press' ); ?></span>
						<?php $np_c = np_post_top_cat( $np_lead ); if ( $np_c ) : ?><span class="badge b"><?php echo esc_html( $np_c->name ); ?></span><?php endif; ?>
					</span>
					<h2 class="lead-title b"><?php echo esc_html( get_the_title( $np_lead ) ); ?></h2>
					<span class="lead-excerpt"><?php echo esc_html( wp_strip_all_tags( get_the_excerpt( $np_lead ) ) ); ?></span>
					<time class="meta" datetime="<?php echo esc_attr( get_the_date( 'c', $np_lead ) ); ?>"><?php echo esc_html( np_bn_ago( $np_lead ) ); ?></time>
				</span>
			</a>
		</article>

		<?php if ( $np_side ) : ?>
		<section class="side" aria-label="<?php echo esc_attr( $np_side_s['title'] ); ?>">
			<div class="m-only"><?php np_section_head( $np_side_s['title'], $np_side_s['cat'] ? get_category_link( $np_side_s['cat'] ) : home_url( '/?s=' ) ); ?></div>
			<?php foreach ( $np_side as $np_p ) { np_compact( $np_p ); } ?>
		</section>
		<?php endif; ?>
	</div>
	<?php endif; ?>

	<?php if ( $np_top ) : ?>
	<section class="top" aria-labelledby="top-h">
		<?php np_section_head( $np_top_s['title'], $np_top_s['cat'] ? get_category_link( $np_top_s['cat'] ) : home_url( '/?s=' ), 'সব খবর →', 'top-h' ); ?>
		<div class="grid-4 cards-m-first">
			<?php foreach ( $np_top as $np_p ) { np_card( $np_p ); } ?>
		</div>
	</section>
	<?php endif; ?>

	<?php if ( is_active_sidebar( 'ad-home' ) ) { np_ad( 'ad-home', 'ad-leader' ); } ?>

	<div class="row2">
		<?php if ( $np_local && $np_local_list ) : ?>
		<section class="local" aria-labelledby="local-h">
			<?php np_section_head( $np_loc_s['title'], get_category_link( $np_local ), 'সব খবর →', 'local-h' ); ?>
			<?php
			$np_kids = get_categories( array( 'parent' => $np_local->term_id, 'hide_empty' => false, 'orderby' => 'term_id' ) );
			if ( $np_kids ) :
				?>
				<ul class="pills pills-sm" aria-label="<?php esc_attr_e( 'উপজেলা', 'narayanganj-press' ); ?>">
					<?php foreach ( $np_kids as $np_i => $np_k ) : ?>
						<li class="<?php echo 0 === $np_i ? 'current-menu-item' : ''; ?>"><a href="<?php echo esc_url( get_category_link( $np_k ) ); ?>"><?php echo esc_html( $np_k->name ); ?></a></li>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>
			<div class="local-body">
				<?php $np_f = array_shift( $np_local_list ); $np_fc = np_post_cat( $np_f ); ?>
				<article class="feature">
					<a href="<?php echo esc_url( get_permalink( $np_f ) ); ?>">
						<?php np_thumb( $np_f, 'np-card', '(max-width: 820px) calc(100vw - 32px), 380px', false, 'r-16x10' ); ?>
						<?php if ( $np_fc ) : ?><span class="kicker b"><?php echo esc_html( $np_fc->name ); ?></span><?php endif; ?>
						<h3 class="feature-title b"><?php echo esc_html( get_the_title( $np_f ) ); ?></h3>
						<time class="meta" datetime="<?php echo esc_attr( get_the_date( 'c', $np_f ) ); ?>"><?php echo esc_html( np_bn_ago( $np_f ) ); ?></time>
					</a>
				</article>
				<div class="local-list">
					<?php foreach ( $np_local_list as $np_p ) { np_compact( $np_p ); } ?>
				</div>
			</div>
		</section>
		<?php endif; ?>
		<?php np_most_read( 5 ); ?>
	</div>

	<?php
	$np_cols = array_filter( array_map( 'np_home_section', array( 'col1', 'col2', 'col3' ) ), function ( $sec ) {
		return ! $sec['hidden'] && $sec['cat'];
	} );
	if ( $np_cols ) :
		?>
	<div class="cols">
		<?php
		foreach ( $np_cols as $np_sec ) :
			$np_cat   = $np_sec['cat'];
			$np_items = $np_sec_posts( $np_sec, 3 );
			if ( ! $np_items ) {
				continue;
			}
			$np_first = array_shift( $np_items );
			?>
			<section class="col" aria-labelledby="col-<?php echo esc_attr( $np_cat->term_id ); ?>">
				<?php np_section_head( $np_sec['title'], get_category_link( $np_cat ), 'আরও →', 'col-' . $np_cat->term_id ); ?>
				<a class="col-first" href="<?php echo esc_url( get_permalink( $np_first ) ); ?>">
					<?php np_thumb( $np_first, 'np-card', '(max-width: 820px) calc(100vw - 32px), 380px', false, 'r-16x9' ); ?>
					<h3 class="col-title b"><?php echo esc_html( get_the_title( $np_first ) ); ?></h3>
				</a>
				<ul class="col-list">
					<?php foreach ( $np_items as $np_p ) : ?>
						<li><a href="<?php echo esc_url( get_permalink( $np_p ) ); ?>"><?php echo esc_html( get_the_title( $np_p ) ); ?></a></li>
					<?php endforeach; ?>
				</ul>
			</section>
		<?php endforeach; ?>
	</div>
	<?php endif; ?>
</main>

<?php
$np_vid_s = np_home_section( 'video' );
$np_vcat  = $np_vid_s['hidden'] ? null : $np_vid_s['cat'];
$np_vids  = $np_vcat ? get_posts( array( 'posts_per_page' => 4, 'cat' => $np_vcat->term_id, 'ignore_sticky_posts' => true, 'no_found_rows' => true ) ) : array();
if ( $np_vids ) :
	$np_v = array_shift( $np_vids );
	?>
<section class="video-band" aria-labelledby="video-h">
	<div class="wrap">
		<?php np_section_head( $np_vid_s['title'], get_category_link( $np_vcat ), 'সব ভিডিও →', 'video-h' ); ?>
		<div class="video-row">
			<article class="video-main">
				<a href="<?php echo esc_url( get_permalink( $np_v ) ); ?>">
					<?php np_thumb( $np_v, 'np-lead', '(max-width: 820px) calc(100vw - 32px), 700px', false, 'r-video thumb-lg', true ); ?>
					<h3 class="video-title b"><?php echo esc_html( get_the_title( $np_v ) ); ?></h3>
					<?php $np_d = get_post_meta( $np_v->ID, 'np_duration', true ); if ( $np_d ) : ?><span class="meta"><?php echo esc_html( np_bn_digits( $np_d ) ); ?></span><?php endif; ?>
				</a>
			</article>
			<?php if ( $np_vids ) : ?>
			<ul class="video-more">
				<?php foreach ( $np_vids as $np_p ) : ?>
					<li><a href="<?php echo esc_url( get_permalink( $np_p ) ); ?>">
						<?php np_thumb( $np_p, 'np-thumb', '160px', false, 'r-5x3', true ); ?>
						<span><span class="video-t b"><?php echo esc_html( get_the_title( $np_p ) ); ?></span>
						<?php $np_d = get_post_meta( $np_p->ID, 'np_duration', true ); if ( $np_d ) : ?><span class="meta"><?php echo esc_html( np_bn_digits( $np_d ) ); ?></span><?php endif; ?></span>
					</a></li>
				<?php endforeach; ?>
			</ul>
			<?php endif; ?>
		</div>
	</div>
</section>
<?php endif; ?>

<?php
get_footer();
