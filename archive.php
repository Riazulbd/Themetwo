<?php
/**
 * Category, tag and date archives. Child categories (e.g. upazilas under
 * নারায়ণগঞ্জ) appear as pills under the title.
 *
 * @package Narayanganj_Press
 */

defined( 'ABSPATH' ) || exit;

get_header();
$np_term = get_queried_object();
?>
<main id="main" class="wrap">
	<header class="page-head">
		<?php np_breadcrumbs(); ?>
		<h1 class="page-title b">
			<?php
			if ( is_category() || is_tag() || is_tax() ) {
				single_term_title();
			} elseif ( is_date() ) {
				echo esc_html( np_bn_digits( wp_strip_all_tags( get_the_archive_title() ) ) );
			} else {
				echo esc_html( wp_strip_all_tags( get_the_archive_title() ) );
			}
			?>
		</h1>
		<?php if ( term_description() ) : ?>
			<div class="page-desc"><?php echo wp_kses_post( term_description() ); ?></div>
		<?php endif; ?>
		<?php
		if ( is_category() ) :
			$np_parent = $np_term->parent ? $np_term->parent : $np_term->term_id;
			$np_kids   = get_categories( array( 'parent' => $np_parent, 'hide_empty' => false, 'orderby' => 'term_id' ) );
			if ( $np_kids ) :
				?>
				<ul class="pills pills-sm">
					<?php foreach ( $np_kids as $np_k ) : ?>
						<li class="<?php echo (int) $np_k->term_id === (int) $np_term->term_id ? 'current-menu-item' : ''; ?>"><a href="<?php echo esc_url( get_category_link( $np_k ) ); ?>"><?php echo esc_html( $np_k->name ); ?></a></li>
					<?php endforeach; ?>
				</ul>
				<?php
			endif;
		endif;
		?>
	</header>
	<?php if ( is_active_sidebar( 'ad-header' ) ) { np_ad( 'ad-header', 'ad-leader' ); } ?>
	<?php get_template_part( 'template-parts/listing' ); ?>
</main>
<?php
get_footer();
