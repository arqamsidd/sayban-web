<?php
/**
 * Sayban.pk — Color theme switcher.
 *
 * A WP-admin page (Sayban Colors) lets an editor pick a color theme. The choice
 * is stored in the option `sayban_active_theme` and injected as a small :root
 * override on top of the base palette (assets/sayban-colors.css) on every page —
 * so switching is instant, with no file editing and no deploy.
 *
 * Precedence (last wins):
 *   1. assets/sayban-colors.css            (base / default palette)
 *   2. this admin selection (inline :root) (chosen in wp-admin)
 *   3. uploads/sayban/sayban-colors.css    (optional manual override file, if present)
 */
defined( 'ABSPATH' ) || exit;

/**
 * Registry of color themes.  slug => [ label, desc, swatch[4], vars{ --sb-*: hex } ]
 * `default` has no vars (it IS the base palette). Themes only need to list the
 * variables they change; anything omitted keeps its default value.
 */
function sayban_color_themes() {
	return array(
		'default' => array(
			'label'  => 'Sayban — Gold & Teal',
			'desc'   => 'The current default brand palette.',
			'swatch' => array( '#B08C46', '#2A2925', '#0E6E5E', '#FAF8F3' ),
			'vars'   => array(),
		),
		'black-white' => array(
			'label'  => 'Black & White',
			'desc'   => 'Monochrome — gray brand accent, near-black sections, white backgrounds.',
			'swatch' => array( '#8A8A8A', '#171717', '#6E6E6E', '#FFFFFF' ),
			'vars'   => array(
				'--sb-gold' => '#8A8A8A', '--sb-gold-deep' => '#5F5F5F', '--sb-gold-soft' => '#EFEFEF', '--sb-gold-lite' => '#C7C7C7', '--sb-gold-mid' => '#9E9E9E',
				'--sb-charcoal' => '#171717', '--sb-ink-2' => '#4A4A4A', '--sb-ink-3' => '#7C7C7C', '--sb-ink-soft' => '#232323',
				'--sb-teal' => '#6E6E6E', '--sb-teal-deep' => '#4A4A4A', '--sb-teal-soft' => '#ECECEC',
				'--sb-paper' => '#FFFFFF', '--sb-paper-2' => '#F7F7F7', '--sb-line' => '#E5E5E5', '--sb-line-2' => '#D0D0D0',
				'--sb-brick' => '#5F5F5F', '--sb-success' => '#6E6E6E', '--sb-dark' => '#0C0C0C', '--sb-dark-2' => '#1A1A1A',
			),
		),
		'teal-black-white' => array(
			'label'  => 'Teal / Black / White',
			'desc'   => 'Tri-color — teal brand, black text + accents, white backgrounds.',
			'swatch' => array( '#0F8770', '#141414', '#1C1C1C', '#FFFFFF' ),
			'vars'   => array(
				'--sb-gold' => '#0F8770', '--sb-gold-deep' => '#0A5F4F', '--sb-gold-soft' => '#E1F1EC', '--sb-gold-lite' => '#B9E0D6', '--sb-gold-mid' => '#4FA894',
				'--sb-charcoal' => '#141414', '--sb-ink-2' => '#444444', '--sb-ink-3' => '#7A7A7A', '--sb-ink-soft' => '#1F1F1F',
				'--sb-teal' => '#1C1C1C', '--sb-teal-deep' => '#000000', '--sb-teal-soft' => '#EAEAEA',
				'--sb-paper' => '#FFFFFF', '--sb-paper-2' => '#F5F8F7', '--sb-line' => '#E3E8E6', '--sb-line-2' => '#CCD5D2',
				'--sb-brick' => '#1C1C1C', '--sb-success' => '#0F8770', '--sb-dark' => '#0A0A0A', '--sb-dark-2' => '#161616',
			),
		),
		'emerald-bronze' => array(
			'label'  => 'Emerald & Bronze',
			'desc'   => 'Bronze brand + emerald accent.',
			'swatch' => array( '#A9722F', '#2A2925', '#0F7A54', '#FAF8F3' ),
			'vars'   => array(
				'--sb-gold' => '#A9722F', '--sb-gold-deep' => '#7E5220', '--sb-gold-soft' => '#F1E6D6', '--sb-gold-lite' => '#DBC09A', '--sb-gold-mid' => '#B0854E',
				'--sb-teal' => '#0F7A54', '--sb-teal-deep' => '#0A5539', '--sb-teal-soft' => '#E1F0E9',
			),
		),
		'royal-blue' => array(
			'label'  => 'Royal Blue',
			'desc'   => 'Keeps the gold brand, swaps the accent to royal blue.',
			'swatch' => array( '#B08C46', '#2A2925', '#2551A8', '#FAF8F3' ),
			'vars'   => array(
				'--sb-teal' => '#2551A8', '--sb-teal-deep' => '#183872', '--sb-teal-soft' => '#E5EAF6',
			),
		),
		'terracotta-slate' => array(
			'label'  => 'Terracotta & Slate',
			'desc'   => 'Warm terracotta brand + slate-teal accent.',
			'swatch' => array( '#C0623B', '#2A2925', '#2C6E6A', '#FAF8F3' ),
			'vars'   => array(
				'--sb-gold' => '#C0623B', '--sb-gold-deep' => '#97482A', '--sb-gold-soft' => '#F6E5DC', '--sb-gold-lite' => '#E8C3B2', '--sb-gold-mid' => '#C77E5C',
				'--sb-teal' => '#2C6E6A', '--sb-teal-deep' => '#1E4E4B', '--sb-teal-soft' => '#E4EEED',
			),
		),
		'midnight-gold' => array(
			'label'  => 'Midnight & Gold',
			'desc'   => 'Gold brand; dark sections turned deep navy (luxe/dark).',
			'swatch' => array( '#B08C46', '#1B2137', '#0E6E5E', '#FAF8F3' ),
			'vars'   => array(
				'--sb-charcoal' => '#1B2137', '--sb-ink-2' => '#47506B', '--sb-dark' => '#10131F', '--sb-dark-2' => '#171C2E',
			),
		),
	);
}

/** Active theme slug (validated against the registry; falls back to default). */
function sayban_active_theme() {
	$slug   = get_option( 'sayban_active_theme', 'default' );
	$themes = sayban_color_themes();
	return isset( $themes[ $slug ] ) ? $slug : 'default';
}

/** Inline `:root { … }` CSS for the active theme (empty string for default). */
function sayban_active_theme_css() {
	$themes = sayban_color_themes();
	$vars   = $themes[ sayban_active_theme() ]['vars'];
	if ( empty( $vars ) ) { return ''; }
	$decls = '';
	foreach ( $vars as $k => $v ) { $decls .= $k . ':' . $v . ';'; }
	return ':root{' . $decls . '}';
}

/* =========================================================================
   Admin page — Sayban Colors (top-level menu)
   ========================================================================= */
add_action( 'admin_menu', function () {
	add_menu_page(
		'Sayban Colors',
		'Sayban Colors',
		'manage_options',
		'sayban-colors',
		'sayban_colors_render_admin_page',
		'dashicons-art',
		59
	);
} );

add_action( 'admin_init', function () {
	register_setting( 'sayban_colors_group', 'sayban_active_theme', array(
		'type'              => 'string',
		'sanitize_callback' => 'sayban_sanitize_theme_slug',
		'default'           => 'default',
	) );
} );

/** Only allow slugs that exist in the registry. */
function sayban_sanitize_theme_slug( $val ) {
	$themes = sayban_color_themes();
	$val    = is_string( $val ) ? sanitize_key( $val ) : 'default';
	return isset( $themes[ $val ] ) ? $val : 'default';
}

function sayban_colors_render_admin_page() {
	if ( ! current_user_can( 'manage_options' ) ) { return; }
	$themes  = sayban_color_themes();
	$active  = sayban_active_theme();
	$has_override = file_exists( WP_CONTENT_DIR . '/uploads/sayban/sayban-colors.css' );
	?>
	<div class="wrap sayban-colors-admin">
		<h1><span class="dashicons dashicons-art" style="font-size:28px;width:28px;height:28px;vertical-align:-4px"></span> Sayban Colors</h1>
		<p class="description" style="font-size:14px;max-width:70ch">
			Pick a color theme for the whole site — the custom pages, the Home / Company / Contact
			Elementor pages, and the property (REM) pages all follow it. Changes apply immediately
			(you may need a hard refresh — Ctrl/Cmd&#8209;Shift&#8209;R).
		</p>

		<?php if ( $has_override ) : ?>
			<div class="notice notice-warning inline"><p>
				A manual override file <code>wp-content/uploads/sayban/sayban-colors.css</code> exists and
				<strong>takes precedence over this selection</strong>. Delete it to let this switcher control colors.
			</p></div>
		<?php endif; ?>

		<form method="post" action="options.php">
			<?php settings_fields( 'sayban_colors_group' ); ?>
			<div class="sb-theme-grid">
				<?php foreach ( $themes as $slug => $t ) : ?>
					<label class="sb-theme-card<?php echo $slug === $active ? ' is-active' : ''; ?>">
						<input type="radio" name="sayban_active_theme" value="<?php echo esc_attr( $slug ); ?>" <?php checked( $slug, $active ); ?>>
						<span class="sb-theme-swatches" aria-hidden="true">
							<?php foreach ( $t['swatch'] as $c ) : ?>
								<span class="sb-theme-dot" style="background:<?php echo esc_attr( $c ); ?>"></span>
							<?php endforeach; ?>
						</span>
						<span class="sb-theme-meta">
							<strong><?php echo esc_html( $t['label'] ); ?><?php echo $slug === 'default' ? ' <em>(current default)</em>' : ''; ?></strong>
							<span class="sb-theme-desc"><?php echo esc_html( $t['desc'] ); ?></span>
						</span>
						<span class="sb-theme-check dashicons dashicons-yes"></span>
					</label>
				<?php endforeach; ?>
			</div>

			<p style="margin-top:22px">
				<?php submit_button( 'Save & apply theme', 'primary', 'submit', false ); ?>
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>" target="_blank" class="button button-secondary" style="margin-left:8px">View site &#8599;</a>
			</p>
		</form>

		<p class="description" style="margin-top:18px">
			Note: the logo is an image with gold baked in, so it stays gold in every theme — a full
			re-brand (e.g. black &amp; white) needs a recolored logo file.
		</p>
	</div>

	<style>
		.sayban-colors-admin .sb-theme-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(320px,1fr));gap:14px;margin-top:18px;max-width:1000px}
		.sayban-colors-admin .sb-theme-card{position:relative;display:flex;align-items:center;gap:14px;background:#fff;border:2px solid #e2e4e7;border-radius:10px;padding:16px 18px;cursor:pointer;transition:border-color .12s,box-shadow .12s}
		.sayban-colors-admin .sb-theme-card:hover{border-color:#b8bcc2}
		.sayban-colors-admin .sb-theme-card.is-active{border-color:#B08C46;box-shadow:0 0 0 1px #B08C46}
		.sayban-colors-admin .sb-theme-card input{position:absolute;opacity:0;pointer-events:none}
		.sayban-colors-admin .sb-theme-swatches{display:inline-flex;flex:0 0 auto;box-shadow:0 1px 3px rgba(0,0,0,.12);border-radius:6px;overflow:hidden}
		.sayban-colors-admin .sb-theme-dot{width:26px;height:44px;display:block}
		.sayban-colors-admin .sb-theme-meta{display:flex;flex-direction:column;gap:3px;line-height:1.35}
		.sayban-colors-admin .sb-theme-meta strong{font-size:14px}
		.sayban-colors-admin .sb-theme-desc{color:#646970;font-size:12.5px}
		.sayban-colors-admin .sb-theme-check{margin-left:auto;color:#B08C46;opacity:0;font-size:22px;width:22px;height:22px}
		.sayban-colors-admin .sb-theme-card.is-active .sb-theme-check{opacity:1}
		/* live selection (before save) via :has for browsers that support it */
		.sayban-colors-admin .sb-theme-card:has(input:checked){border-color:#B08C46;box-shadow:0 0 0 1px #B08C46}
		.sayban-colors-admin .sb-theme-card:has(input:checked) .sb-theme-check{opacity:1}
	</style>
	<script>
	(function(){
		var cards = document.querySelectorAll('.sayban-colors-admin .sb-theme-card');
		document.querySelectorAll('.sayban-colors-admin input[name="sayban_active_theme"]').forEach(function(radio){
			radio.addEventListener('change', function(){
				cards.forEach(function(c){ c.classList.remove('is-active'); });
				var card = radio.closest('.sb-theme-card');
				if (card) { card.classList.add('is-active'); }
			});
		});
	})();
	</script>
	<?php
}
