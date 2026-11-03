<?php
/**
 * Footer. Two layouts from the design:
 * - desktop (≥ 768px): logo + about + newsletter, two category columns, copyright;
 * - mobile: Amar Desh–style centred stack (logo, socials, imprint, contact card,
 *   copyright).
 * Logo, colours, imprint, contacts and social links come from the Theme Panel.
 *
 * @package Khobor_Press
 */

defined( 'ABSPATH' ) || exit;

$np_year   = np_bn_digits( wp_date( 'Y' ) );
$np_editor = np_opt( 'editor_name' ) ? trim( np_opt( 'editor_label' ) . ': ' . np_opt( 'editor_name' ), ': ' ) : '';
$np_cr     = np_opt( 'copyright' ) ? np_opt( 'copyright' ) : get_bloginfo( 'name' );
?>
<footer class="site-footer">
	<div class="wrap footer-d">
		<div class="fd-top">
			<div class="fd-about">
				<?php np_logo( 'footer', false ); ?>
				<p class="fd-text">
					<?php echo esc_html( np_opt( 'footer_about' ) ); ?>
					<?php if ( $np_editor ) : ?> <?php echo esc_html( $np_editor ); ?><?php endif; ?>
					<?php if ( np_opt( 'news_email' ) ) : ?><span class="sep" aria-hidden="true">·</span><?php esc_html_e( 'Contact:', 'khobor-press' ); ?> <a href="mailto:<?php echo esc_attr( antispambot( np_opt( 'news_email' ) ) ); ?>"><?php echo esc_html( antispambot( np_opt( 'news_email' ) ) ); ?></a><?php endif; ?>
				</p>
				<?php if ( np_opt( 'newsletter_action' ) ) : ?>
					<form class="newsletter" method="post" action="<?php echo esc_url( np_opt( 'newsletter_action' ) ); ?>" target="_blank">
						<label class="screen-reader-text" for="nl-email"><?php esc_html_e( 'Your email', 'khobor-press' ); ?></label>
						<input id="nl-email" type="email" name="EMAIL" required placeholder="<?php esc_attr_e( 'Your email', 'khobor-press' ); ?>">
						<button class="btn btn-live b" type="submit"><?php esc_html_e( 'Subscribe', 'khobor-press' ); ?></button>
					</form>
				<?php endif; ?>
			</div>
			<div class="fd-cols">
				<?php
				$np_local_s = np_home_section( 'local' );
				$np_local   = $np_local_s['cat'];
				$np_cols    = array(
					'footer-1' => array( __( 'Categories', 'khobor-press' ), array() ),
					'footer-2' => array( $np_local ? $np_local_s['title'] : '', array() ),
				);
				// Category fallbacks for the first two columns (the second lists the featured section's sub-categories).
				foreach ( get_categories( array( 'parent' => 0, 'number' => 5, 'exclude' => array( get_option( 'default_category' ) ), 'orderby' => 'term_id', 'hide_empty' => false ) ) as $np_c ) {
					$np_cols['footer-1'][1][] = array( $np_c->name, get_category_link( $np_c ) );
				}
				if ( $np_local ) {
					foreach ( get_categories( array( 'parent' => $np_local->term_id, 'number' => 5, 'orderby' => 'term_id', 'hide_empty' => false ) ) as $np_c ) {
						$np_cols['footer-2'][1][] = array( $np_c->name, get_category_link( $np_c ) );
					}
				}
				foreach ( $np_cols as $np_loc => $np_col ) :
					if ( 'footer-2' === $np_loc && ! has_nav_menu( 'footer-2' ) && ! $np_col[1] ) {
						continue; // Nothing to list.
					}
					?>
					<div class="fd-col">
						<?php if ( $np_col[0] ) : ?><h2 class="fd-h b"><?php echo esc_html( $np_col[0] ); ?></h2><?php endif; ?>
						<?php np_menu( $np_loc, 'fd-links', $np_col[1], $np_col[0] ? $np_col[0] : __( 'Footer', 'khobor-press' ) ); ?>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
		<p class="fd-copy"><?php /* translators: 1: year, 2: copyright holder. */ echo esc_html( sprintf( __( '© %1$s %2$s. All rights reserved.', 'khobor-press' ), $np_year, $np_cr ) ); ?></p>
	</div>

	<div class="footer-m">
		<?php np_logo( 'footer', false ); ?>
		<p class="fm-tag"><?php echo esc_html( rtrim( np_opt( 'footer_about' ), '।.' ) ); ?></p>
		<?php np_socials(); ?>
		<hr class="fm-line">
		<div class="fm-imprint">
			<?php if ( $np_editor ) : ?><p class="fm-editor b"><?php echo esc_html( $np_editor ); ?></p><?php endif; ?>
			<?php if ( np_opt( 'publisher_line' ) ) : ?><p class="fm-pub"><?php echo esc_html( np_opt( 'publisher_line' ) ); ?></p><?php endif; ?>
		</div>
		<address class="fm-card">
			<?php if ( np_opt( 'office_address' ) ) : ?><p class="fm-office"><?php echo esc_html( __( 'Office:', 'khobor-press' ) . ' ' . np_opt( 'office_address' ) ); ?></p><?php endif; ?>
			<?php
			foreach ( array( 'news', 'ad', 'info' ) as $np_k ) :
				$np_h  = np_opt( $np_k . '_label' );
				$np_ph = np_opt( $np_k . '_phone' );
				$np_em = np_opt( $np_k . '_email' );
				if ( ! $np_ph && ! $np_em ) {
					continue;
				}
				?>
				<div class="fm-contact">
					<?php if ( $np_h ) : ?><p class="fm-h b"><?php echo esc_html( $np_h ); ?></p><?php endif; ?>
					<?php if ( $np_ph ) : ?><p><?php esc_html_e( 'Phone:', 'khobor-press' ); ?> <a href="tel:<?php echo esc_attr( preg_replace( '/[^0-9+]/', '', strtr( $np_ph, array_flip( array( '০', '১', '২', '৩', '৪', '৫', '৬', '৭', '৮', '৯' ) ) ) ) ); ?>"><?php echo esc_html( $np_ph ); ?></a></p><?php endif; ?>
					<?php if ( $np_em ) : ?><p><?php esc_html_e( 'Email:', 'khobor-press' ); ?> <a href="mailto:<?php echo esc_attr( antispambot( $np_em ) ); ?>"><?php echo esc_html( antispambot( $np_em ) ); ?></a></p><?php endif; ?>
				</div>
			<?php endforeach; ?>
		</address>
		<p class="fm-copy"><?php echo esc_html( sprintf( /* translators: 1: year, 2: copyright holder. */ __( 'Copyright © %1$s %2$s', 'khobor-press' ), $np_year, $np_cr ) ); ?></p>
	</div>
</footer>
<?php wp_footer(); ?>
</body>
</html>
