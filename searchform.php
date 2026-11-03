<?php
/**
 * Pill search form.
 *
 * @package News_Press
 */

defined( 'ABSPATH' ) || exit;

$np_sid = wp_unique_id( 's-' );
?>
<form role="search" method="get" class="search-form" action="<?php echo esc_url( home_url( '/' ) ); ?>">
	<label class="screen-reader-text" for="<?php echo esc_attr( $np_sid ); ?>"><?php esc_html_e( 'Search news', 'news-press' ); ?></label>
	<input type="search" id="<?php echo esc_attr( $np_sid ); ?>" class="search-field" name="s" value="<?php echo esc_attr( get_search_query() ); ?>" placeholder="<?php esc_attr_e( 'Search news…', 'news-press' ); ?>">
	<button type="submit" class="search-submit" aria-label="<?php esc_attr_e( 'Search', 'news-press' ); ?>"><?php echo np_icon( 'search' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></button>
</form>
