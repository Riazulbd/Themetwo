<?php
/**
 * The Theme Panel: a dedicated admin screen (top-level menu item "Theme Panel"),
 * independent of the Customizer. One form, one tab per group of settings, saved
 * through the Settings API into the single `np_options` option.
 *
 * @package News_Press
 */

defined( 'ABSPATH' ) || exit;

/** Menu slug of the Theme Panel. */
const NP_PANEL_SLUG = 'np-theme-panel';

/** Register the setting and move any old Customizer values across. */
function np_panel_register() {
	register_setting( 'np_options_group', 'np_options', array(
		'type'              => 'array',
		'sanitize_callback' => 'np_sanitize_options',
	) );
	np_maybe_migrate_options();
}
add_action( 'admin_init', 'np_panel_register' );

/** Anyone who can edit theme options may save them (options.php otherwise asks for manage_options). */
add_filter( 'option_page_capability_np_options_group', function () {
	return 'edit_theme_options';
} );

/** Add the top-level menu item. */
function np_panel_menu() {
	add_menu_page(
		__( 'Theme Panel', 'news-press' ),
		__( 'Theme Panel', 'news-press' ),
		'edit_theme_options',
		NP_PANEL_SLUG,
		'np_panel_render',
		'dashicons-layout',
		59
	);
}
add_action( 'admin_menu', 'np_panel_menu' );

/**
 * Load the picker scripts and panel assets on the Theme Panel screen only.
 *
 * @param string $hook Current admin page hook suffix.
 */
function np_panel_assets( $hook ) {
	if ( 'toplevel_page_' . NP_PANEL_SLUG !== $hook ) {
		return;
	}
	wp_enqueue_media();
	wp_enqueue_style( 'wp-color-picker' );
	wp_enqueue_style( 'np-panel', NP_URI . '/assets/admin/panel.css', array(), NP_VERSION );
	wp_enqueue_script( 'np-panel', NP_URI . '/assets/admin/panel.js', array( 'jquery', 'wp-color-picker' ), NP_VERSION, true );
	wp_localize_script( 'np-panel', 'npPanel', array(
		'chooseTitle' => __( 'Choose an image', 'news-press' ),
		'chooseBtn'   => __( 'Use this image', 'news-press' ),
		'confirm'     => __( 'Reset every Theme Panel setting to its default? This cannot be undone.', 'news-press' ),
	) );
}
add_action( 'admin_enqueue_scripts', 'np_panel_assets' );

/**
 * Category dropdown choices for a category field.
 *
 * @param array $f Field definition.
 * @return array<string,string>
 */
function np_category_choices( $f ) {
	if ( ! empty( $f['latest'] ) ) {
		$choices = array(
			''     => __( 'All categories (latest news)', 'news-press' ),
			'hide' => __( '— Hide this section —', 'news-press' ),
		);
	} elseif ( ! empty( $f['slug'] ) ) {
		/* translators: %s: category slug. */
		$choices = array( '' => sprintf( __( 'Default (%s)', 'news-press' ), $f['slug'] ) );
	} else {
		$choices = array( '' => __( '— Not shown —', 'news-press' ) );
	}
	$by_parent = array();
	foreach ( get_categories( array( 'hide_empty' => false, 'orderby' => 'name' ) ) as $c ) {
		$by_parent[ $c->parent ][] = $c;
	}
	$walk = function ( $parent, $depth ) use ( &$walk, &$choices, $by_parent ) {
		if ( empty( $by_parent[ $parent ] ) ) {
			return;
		}
		foreach ( $by_parent[ $parent ] as $c ) {
			$choices[ (string) $c->term_id ] = str_repeat( '— ', $depth ) . $c->name;
			$walk( $c->term_id, $depth + 1 );
		}
	};
	$walk( 0, 0 );
	return $choices;
}

/**
 * Print one field's input.
 *
 * @param string $key   Option key.
 * @param array  $f     Field definition.
 * @param string $value Current value.
 */
function np_panel_field( $key, $f, $value ) {
	$name = 'np_options[' . $key . ']';
	$id   = 'np-' . $key;
	$ph   = isset( $f['placeholder'] ) ? ' placeholder="' . esc_attr( $f['placeholder'] ) . '"' : '';

	switch ( $f['type'] ) {
		case 'textarea':
			printf( '<textarea id="%s" name="%s" rows="3" class="large-text"%s>%s</textarea>', esc_attr( $id ), esc_attr( $name ), $ph, esc_textarea( $value ) ); // phpcs:ignore WordPress.Security.EscapeOutput -- $ph is escaped above.
			break;

		case 'checkbox':
			printf(
				'<label for="%1$s"><input type="checkbox" id="%1$s" name="%2$s" value="1"%3$s> %4$s</label>',
				esc_attr( $id ),
				esc_attr( $name ),
				checked( $value, '1', false ),
				esc_html( isset( $f['text'] ) ? $f['text'] : __( 'Enabled', 'news-press' ) )
			);
			break;

		case 'select':
		case 'category':
			$choices = 'category' === $f['type'] ? np_category_choices( $f ) : $f['choices'];
			printf( '<select id="%s" name="%s">', esc_attr( $id ), esc_attr( $name ) );
			foreach ( $choices as $v => $label ) {
				printf( '<option value="%s"%s>%s</option>', esc_attr( $v ), selected( (string) $v, $value, false ), esc_html( $label ) );
			}
			echo '</select>';
			break;

		case 'color':
			printf(
				'<input type="text" id="%s" name="%s" value="%s" class="np-color" data-default-color="%s">',
				esc_attr( $id ),
				esc_attr( $name ),
				esc_attr( $value ),
				esc_attr( $f['default'] )
			);
			break;

		case 'image':
			$has = $value && wp_attachment_is_image( (int) $value );
			echo '<div class="np-img">';
			printf( '<input type="hidden" id="%s" name="%s" value="%s">', esc_attr( $id ), esc_attr( $name ), esc_attr( $value ) );
			echo '<div class="np-img-preview">' . ( $has ? wp_get_attachment_image( (int) $value, 'medium' ) : '' ) . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput -- core markup.
			printf( '<button type="button" class="button np-img-pick">%s</button> ', esc_html__( 'Choose image', 'news-press' ) );
			printf( '<button type="button" class="button-link np-img-remove"%s>%s</button>', $has ? '' : ' hidden', esc_html__( 'Remove', 'news-press' ) ); // phpcs:ignore WordPress.Security.EscapeOutput
			echo '</div>';
			break;

		default:
			$type = in_array( $f['type'], array( 'url', 'email', 'number' ), true ) ? $f['type'] : 'text';
			$attr = '';
			if ( 'number' === $type ) {
				$attr .= isset( $f['min'] ) ? ' min="' . (int) $f['min'] . '"' : '';
				$attr .= isset( $f['max'] ) ? ' max="' . (int) $f['max'] . '"' : '';
			}
			printf(
				'<input type="%s" id="%s" name="%s" value="%s" class="%s"%s%s>',
				esc_attr( $type ),
				esc_attr( $id ),
				esc_attr( $name ),
				esc_attr( $value ),
				'number' === $type ? 'small-text' : 'regular-text',
				$ph, // phpcs:ignore WordPress.Security.EscapeOutput -- escaped above.
				$attr // phpcs:ignore WordPress.Security.EscapeOutput -- integers only.
			);
	}
	if ( ! empty( $f['desc'] ) ) {
		echo '<p class="description">' . esc_html( $f['desc'] ) . '</p>';
	}
}

/** Print the Theme Panel screen. */
function np_panel_render() {
	if ( ! current_user_can( 'edit_theme_options' ) ) {
		wp_die( esc_html__( 'You do not have permission to change theme options.', 'news-press' ) );
	}
	$tabs   = np_panel_tabs();
	$values = np_options();
	$theme  = wp_get_theme();
	?>
	<div class="wrap np-panel">
		<h1><?php esc_html_e( 'Theme Panel', 'news-press' ); ?> <span class="np-ver"><?php echo esc_html( $theme->get( 'Name' ) . ' ' . $theme->get( 'Version' ) ); ?></span></h1>
		<?php settings_errors(); ?>

		<form method="post" action="options.php" id="np-panel-form">
			<?php settings_fields( 'np_options_group' ); ?>
			<input type="hidden" name="np_options[_form]" value="1">

			<nav class="nav-tab-wrapper np-tabs" aria-label="<?php esc_attr_e( 'Theme Panel sections', 'news-press' ); ?>">
				<?php foreach ( $tabs as $id => $tab ) : ?>
					<a class="nav-tab" href="#np-tab-<?php echo esc_attr( $id ); ?>"><?php echo esc_html( $tab['label'] ); ?></a>
				<?php endforeach; ?>
			</nav>

			<?php foreach ( $tabs as $id => $tab ) : ?>
				<section class="np-tab" id="np-tab-<?php echo esc_attr( $id ); ?>">
					<?php
					$open = false;
					foreach ( $tab['fields'] as $key => $f ) {
						if ( 'heading' === $f['type'] ) {
							if ( $open ) {
								echo '</tbody></table>';
								$open = false;
							}
							echo '<h2>' . esc_html( $f['label'] ) . '</h2>';
							if ( ! empty( $f['desc'] ) ) {
								echo '<p class="np-lead">' . esc_html( $f['desc'] ) . '</p>';
							}
							continue;
						}
						if ( ! $open ) {
							echo '<table class="form-table" role="presentation"><tbody>';
							$open = true;
						}
						$value = isset( $values[ $key ] ) ? (string) $values[ $key ] : '';
						echo '<tr><th scope="row"><label for="np-' . esc_attr( $key ) . '">' . esc_html( $f['label'] ) . '</label></th><td>';
						np_panel_field( $key, $f, $value );
						echo '</td></tr>';
					}
					if ( $open ) {
						echo '</tbody></table>';
					}
					?>
				</section>
			<?php endforeach; ?>

			<p class="submit np-submit">
				<?php submit_button( __( 'Save changes', 'news-press' ), 'primary', 'submit', false ); ?>
				<?php submit_button( __( 'Reset to defaults', 'news-press' ), 'delete', 'np_reset', false ); ?>
			</p>
		</form>
	</div>
	<?php
}
