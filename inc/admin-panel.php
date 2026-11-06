<?php
/**
 * The Theme Panel: a dedicated admin screen (top-level menu item "Theme Panel"),
 * independent of the Customizer. One form, one tab per group of settings, saved
 * through the Settings API into the single `np_options` option.
 *
 * @package Khobor_Press
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
		__( 'Theme Panel', 'khobor-press' ),
		__( 'Theme Panel', 'khobor-press' ),
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
		'chooseTitle' => __( 'Choose an image', 'khobor-press' ),
		'chooseBtn'   => __( 'Use this image', 'khobor-press' ),
		'confirm'     => __( 'Reset every Theme Panel setting to its default? This cannot be undone.', 'khobor-press' ),
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
		$first = __( 'All categories (latest news)', 'khobor-press' );
	} elseif ( ! empty( $f['slug'] ) ) {
		/* translators: %s: category slug. */
		$first = sprintf( __( 'Default (%s)', 'khobor-press' ), $f['slug'] );
	} else {
		$first = __( 'Automatic (picks a busy category)', 'khobor-press' );
	}
	$choices = array(
		''     => $first,
		'hide' => __( '— Hide this section —', 'khobor-press' ),
	);
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
 * Print one field's control (the label and help text are printed by the row around it).
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
			printf( '<textarea id="%s" name="%s" rows="3" class="np-input"%s>%s</textarea>', esc_attr( $id ), esc_attr( $name ), $ph, esc_textarea( $value ) ); // phpcs:ignore WordPress.Security.EscapeOutput -- $ph is escaped above.
			break;

		case 'checkbox':
			printf(
				'<label class="np-switch" for="%1$s"><input type="checkbox" id="%1$s" name="%2$s" value="1"%3$s><span class="np-switch-ui" aria-hidden="true"></span><span class="np-switch-text">%4$s</span></label>',
				esc_attr( $id ),
				esc_attr( $name ),
				checked( $value, '1', false ),
				esc_html( isset( $f['text'] ) ? $f['text'] : __( 'Enabled', 'khobor-press' ) )
			);
			break;

		case 'select':
		case 'category':
			$choices = 'category' === $f['type'] ? np_category_choices( $f ) : $f['choices'];
			printf( '<select id="%s" name="%s" class="np-input">', esc_attr( $id ), esc_attr( $name ) );
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
			printf( '<button type="button" class="button np-img-pick">%s</button> ', esc_html__( 'Choose image', 'khobor-press' ) );
			printf( '<button type="button" class="button-link np-img-remove"%s>%s</button>', $has ? '' : ' hidden', esc_html__( 'Remove', 'khobor-press' ) ); // phpcs:ignore WordPress.Security.EscapeOutput
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
				'<input type="%s" id="%s" name="%s" value="%s" class="np-input%s"%s%s>',
				esc_attr( $type ),
				esc_attr( $id ),
				esc_attr( $name ),
				esc_attr( $value ),
				'number' === $type ? ' np-input--short' : '',
				$ph, // phpcs:ignore WordPress.Security.EscapeOutput -- escaped above.
				$attr // phpcs:ignore WordPress.Security.EscapeOutput -- integers only.
			);
	}
}

/**
 * Split a tab's flat field list into cards: every heading starts a new card.
 *
 * @param array $fields Tab fields.
 * @return array<int,array{title:string,desc:string,fields:array}>
 */
function np_panel_cards( $fields ) {
	$cards = array();
	$cur   = null;
	foreach ( $fields as $key => $f ) {
		if ( 'heading' === $f['type'] ) {
			if ( $cur ) {
				$cards[] = $cur;
			}
			$cur = array( 'title' => $f['label'], 'desc' => isset( $f['desc'] ) ? $f['desc'] : '', 'fields' => array() );
			continue;
		}
		if ( ! $cur ) {
			$cur = array( 'title' => '', 'desc' => '', 'fields' => array() );
		}
		$cur['fields'][ $key ] = $f;
	}
	if ( $cur ) {
		$cards[] = $cur;
	}
	return $cards;
}

/** Print the Theme Panel screen. */
function np_panel_render() {
	if ( ! current_user_can( 'edit_theme_options' ) ) {
		wp_die( esc_html__( 'You do not have permission to change theme options.', 'khobor-press' ) );
	}
	$tabs   = np_panel_tabs();
	$values = np_options();
	$theme  = wp_get_theme();
	$icons  = array(
		'general' => 'dashicons-admin-settings',
		'colours' => 'dashicons-art',
		'footer'  => 'dashicons-id-alt',
		'social'  => 'dashicons-share',
		'home'    => 'dashicons-admin-home',
		'engage'  => 'dashicons-megaphone',
		'login'   => 'dashicons-lock',
	);
	?>
	<div class="wrap np-wrap">
		<h1 class="screen-reader-text"><?php esc_html_e( 'Theme Panel', 'khobor-press' ); ?></h1>
		<hr class="wp-header-end">
		<?php settings_errors(); ?>

		<form method="post" action="options.php" id="np-panel-form" class="np-app">
			<?php settings_fields( 'np_options_group' ); ?>
			<input type="hidden" name="np_options[_form]" value="1">

			<header class="np-top">
				<div class="np-brand">
					<span class="np-brand-icon dashicons dashicons-layout" aria-hidden="true"></span>
					<div>
						<strong class="np-brand-name"><?php esc_html_e( 'Theme Panel', 'khobor-press' ); ?></strong>
						<span class="np-brand-sub"><?php echo esc_html( $theme->get( 'Name' ) . ' ' . $theme->get( 'Version' ) ); ?></span>
					</div>
				</div>
				<div class="np-search">
					<span class="dashicons dashicons-search" aria-hidden="true"></span>
					<input type="search" id="np-search" placeholder="<?php esc_attr_e( 'Search settings…', 'khobor-press' ); ?>" aria-label="<?php esc_attr_e( 'Search settings', 'khobor-press' ); ?>">
				</div>
				<div class="np-actions">
					<span class="np-dirty" hidden><?php esc_html_e( 'Unsaved changes', 'khobor-press' ); ?></span>
					<a class="button np-view" href="<?php echo esc_url( home_url( '/' ) ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'View site', 'khobor-press' ); ?></a>
					<?php submit_button( __( 'Save changes', 'khobor-press' ), 'primary', 'submit', false ); ?>
				</div>
			</header>

			<div class="np-body">
				<nav class="np-nav" aria-label="<?php esc_attr_e( 'Theme Panel sections', 'khobor-press' ); ?>">
					<?php foreach ( $tabs as $id => $tab ) : ?>
						<a href="#np-tab-<?php echo esc_attr( $id ); ?>"><span class="dashicons <?php echo esc_attr( isset( $icons[ $id ] ) ? $icons[ $id ] : 'dashicons-admin-generic' ); ?>" aria-hidden="true"></span><span><?php echo esc_html( $tab['label'] ); ?></span></a>
					<?php endforeach; ?>
				</nav>

				<div class="np-main">
					<?php foreach ( $tabs as $id => $tab ) : ?>
						<section class="np-tab" id="np-tab-<?php echo esc_attr( $id ); ?>">
							<h2 class="np-tab-title"><?php echo esc_html( $tab['label'] ); ?></h2>
							<?php foreach ( np_panel_cards( $tab['fields'] ) as $card ) : ?>
								<?php
								$all_colours = $card['fields'] && count( $card['fields'] ) === count( wp_list_filter( $card['fields'], array( 'type' => 'color' ) ) );
								?>
								<div class="np-card">
									<?php if ( $card['title'] ) : ?>
										<div class="np-card-head">
											<h3><?php echo esc_html( $card['title'] ); ?></h3>
											<?php if ( $card['desc'] ) : ?><p><?php echo esc_html( $card['desc'] ); ?></p><?php endif; ?>
										</div>
									<?php endif; ?>
									<div class="np-rows<?php echo $all_colours ? ' np-rows--grid' : ''; ?>">
										<?php
										foreach ( $card['fields'] as $key => $f ) :
											$value  = isset( $values[ $key ] ) ? (string) $values[ $key ] : '';
											$search = strtolower( $card['title'] . ' ' . $f['label'] . ' ' . ( isset( $f['desc'] ) ? $f['desc'] : '' ) . ' ' . ( isset( $f['text'] ) ? $f['text'] : '' ) );
											?>
											<div class="np-row" data-search="<?php echo esc_attr( $search ); ?>">
												<div class="np-row-label">
													<label for="np-<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $f['label'] ); ?></label>
													<?php if ( ! empty( $f['desc'] ) ) : ?><p class="description"><?php echo esc_html( $f['desc'] ); ?></p><?php endif; ?>
												</div>
												<div class="np-row-control"><?php np_panel_field( $key, $f, $value ); ?></div>
											</div>
										<?php endforeach; ?>
									</div>
								</div>
							<?php endforeach; ?>
						</section>
					<?php endforeach; ?>
					<p class="np-empty" hidden><?php esc_html_e( 'No settings match your search.', 'khobor-press' ); ?></p>

					<div class="np-foot">
						<?php submit_button( __( 'Save changes', 'khobor-press' ), 'primary', 'submit_bottom', false ); ?>
						<?php submit_button( __( 'Reset all to defaults', 'khobor-press' ), 'link-delete', 'np_reset', false ); ?>
					</div>
				</div>
			</div>
		</form>
	</div>
	<?php
}
