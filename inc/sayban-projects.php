<?php
/**
 * Sayban.pk — Developer Projects.
 * A `sayban_project` custom post type (Sayban Builders' own developments, e.g.
 * Saudagran Enclave) with editable meta, an inquiry-email handler, and a
 * [sayban_projects] shortcode for the homepage. Templates live in the child
 * theme (single-sayban_project.php / archive-sayban_project.php).
 */
defined( 'ABSPATH' ) || exit;

/* =========================================================================
   1. Custom post type
   ========================================================================= */
function sayban_register_project_cpt() {
	register_post_type( 'sayban_project', array(
		'labels' => array(
			'name'          => 'Projects',
			'singular_name' => 'Project',
			'add_new_item'  => 'Add New Project',
			'edit_item'     => 'Edit Project',
			'menu_name'     => 'Projects',
		),
		'public'       => true,
		'has_archive'  => 'projects',
		'menu_icon'    => 'dashicons-building',
		'rewrite'      => array( 'slug' => 'projects', 'with_front' => false ),
		'supports'     => array( 'title', 'editor', 'thumbnail', 'excerpt', 'page-attributes' ),
		'show_in_rest' => true,
	) );
}
add_action( 'init', 'sayban_register_project_cpt' );

/* Flush rewrite rules once after the CPT is first registered (self-clearing flag). */
add_action( 'init', function () {
	if ( get_option( 'sayban_projects_rewrites_flushed' ) !== '1' ) {
		flush_rewrite_rules( false );
		update_option( 'sayban_projects_rewrites_flushed', '1', false );
	}
}, 99 );

/* =========================================================================
   2. Meta fields + editor meta box
   ========================================================================= */
/** The scalar project fields (key => label). */
function sayban_project_fields() {
	return array(
		'location'    => 'Location',
		'city'        => 'City',
		'status'      => 'Status (e.g. Booking Open)',
		'ptype'       => 'Type (e.g. Villas & Plots)',
		'possession'  => 'Possession',
		'price_from'  => 'Price from (e.g. PKR 1.05 Cr)',
		'area_range'  => 'Unit sizes (e.g. 5 & 10 Marla)',
		'tagline'     => 'Tagline',
		'phone'       => 'Call number (tel)',
		'whatsapp'    => 'WhatsApp number (digits, e.g. 923001234567)',
		'brochure'    => 'Brochure / plan PDF URL',
		'lat'         => 'Map latitude',
		'lng'         => 'Map longitude',
		'features'    => 'Features (one per line)',
		'pay_plans'   => 'Payment plans (JSON)',
	);
}

add_action( 'add_meta_boxes', function () {
	add_meta_box( 'sayban_project_meta', 'Project Details', 'sayban_project_meta_box', 'sayban_project', 'normal', 'high' );
} );

function sayban_project_meta_box( $post ) {
	wp_nonce_field( 'sayban_project_meta', 'sayban_project_nonce' );
	echo '<style>.sbp-meta label{display:block;font-weight:600;margin:12px 0 4px}.sbp-meta input,.sbp-meta textarea{width:100%}</style>';
	echo '<div class="sbp-meta">';
	foreach ( sayban_project_fields() as $key => $label ) {
		$val = get_post_meta( $post->ID, 'sayban_' . $key, true );
		echo '<label for="sayban_' . esc_attr( $key ) . '">' . esc_html( $label ) . '</label>';
		if ( in_array( $key, array( 'features', 'pay_plans' ), true ) ) {
			$rows = $key === 'pay_plans' ? 12 : 6;
			echo '<textarea id="sayban_' . esc_attr( $key ) . '" name="sayban_' . esc_attr( $key ) . '" rows="' . $rows . '">' . esc_textarea( $val ) . '</textarea>';
		} else {
			echo '<input type="text" id="sayban_' . esc_attr( $key ) . '" name="sayban_' . esc_attr( $key ) . '" value="' . esc_attr( $val ) . '">';
		}
	}
	// gallery attachment ids
	$gallery = get_post_meta( $post->ID, 'sayban_gallery', true );
	echo '<label for="sayban_gallery">Gallery (attachment IDs, comma-separated)</label>';
	echo '<input type="text" id="sayban_gallery" name="sayban_gallery" value="' . esc_attr( $gallery ) . '">';
	echo '</div>';
}

add_action( 'save_post_sayban_project', function ( $post_id ) {
	if ( ! isset( $_POST['sayban_project_nonce'] ) || ! wp_verify_nonce( $_POST['sayban_project_nonce'], 'sayban_project_meta' ) ) { return; }
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) { return; }
	if ( ! current_user_can( 'edit_post', $post_id ) ) { return; }
	$keys = array_keys( sayban_project_fields() );
	$keys[] = 'gallery';
	foreach ( $keys as $key ) {
		if ( ! isset( $_POST[ 'sayban_' . $key ] ) ) { continue; }
		$raw = wp_unslash( $_POST[ 'sayban_' . $key ] );
		if ( in_array( $key, array( 'features', 'pay_plans' ), true ) ) {
			update_post_meta( $post_id, 'sayban_' . $key, $key === 'pay_plans' ? wp_kses_post( $raw ) : sanitize_textarea_field( $raw ) );
		} else {
			update_post_meta( $post_id, 'sayban_' . $key, sanitize_text_field( $raw ) );
		}
	}
} );

/* =========================================================================
   3. Helpers
   ========================================================================= */
/** Get a project meta value. */
function sayban_pf( $post_id, $key, $default = '' ) {
	$v = get_post_meta( $post_id, 'sayban_' . $key, true );
	return $v === '' ? $default : $v;
}

/** Decode the payment-plans JSON into an array of plans. */
function sayban_project_plans( $post_id ) {
	$raw = get_post_meta( $post_id, 'sayban_pay_plans', true );
	if ( ! $raw ) { return array(); }
	$data = json_decode( $raw, true );
	return is_array( $data ) ? $data : array();
}

/** Features as an array. */
function sayban_project_features( $post_id ) {
	$raw = get_post_meta( $post_id, 'sayban_features', true );
	if ( ! $raw ) { return array(); }
	return array_values( array_filter( array_map( 'trim', preg_split( '/\r\n|\r|\n/', $raw ) ) ) );
}

/** Gallery attachment IDs as an array (falls back to featured image). */
function sayban_project_gallery( $post_id ) {
	$raw = get_post_meta( $post_id, 'sayban_gallery', true );
	$ids = $raw ? array_values( array_filter( array_map( 'absint', explode( ',', $raw ) ) ) ) : array();
	if ( ! $ids && has_post_thumbnail( $post_id ) ) { $ids[] = (int) get_post_thumbnail_id( $post_id ); }
	return $ids;
}

/* =========================================================================
   4. Inquiry form handler → emails the site admin
   ========================================================================= */
function sayban_project_inquiry_handler() {
	$ref = wp_get_referer() ? wp_get_referer() : home_url( '/' );
	if ( ! isset( $_POST['sayban_inq_nonce'] ) || ! wp_verify_nonce( $_POST['sayban_inq_nonce'], 'sayban_project_inquiry' ) ) {
		wp_safe_redirect( add_query_arg( 'inq', 'error', $ref ) ); exit;
	}
	if ( ! empty( $_POST['sayban_hp'] ) ) { // honeypot
		wp_safe_redirect( add_query_arg( 'inq', 'ok', $ref ) ); exit;
	}
	$pid   = isset( $_POST['project_id'] ) ? (int) $_POST['project_id'] : 0;
	$name  = sanitize_text_field( wp_unslash( $_POST['inq_name'] ?? '' ) );
	$phone = sanitize_text_field( wp_unslash( $_POST['inq_phone'] ?? '' ) );
	$email = sanitize_email( wp_unslash( $_POST['inq_email'] ?? '' ) );
	$msg   = sanitize_textarea_field( wp_unslash( $_POST['inq_message'] ?? '' ) );
	if ( ! $name || ! $phone ) {
		wp_safe_redirect( add_query_arg( 'inq', 'error', $ref ) ); exit;
	}
	$project = $pid ? get_the_title( $pid ) : 'Project';
	// Inquiries go to the sales inbox; editable via option `sayban_inquiry_email`.
	$to      = get_option( 'sayban_inquiry_email', 'digital@dragster.se' );
	$to      = apply_filters( 'sayban_project_inquiry_to', $to, $pid );
	$subject = 'New project inquiry — ' . $project;
	$body    = "New inquiry for: {$project}\n\n"
		. "Name: {$name}\nPhone: {$phone}\nEmail: {$email}\n\nMessage:\n{$msg}\n\n"
		. 'Page: ' . esc_url_raw( $ref ) . "\n";
	$headers = array();
	if ( $email ) { $headers[] = 'Reply-To: ' . $name . ' <' . $email . '>'; }
	wp_mail( $to, $subject, $body, $headers );
	wp_safe_redirect( add_query_arg( 'inq', 'ok', $ref ) . '#inquire' ); exit;
}
add_action( 'admin_post_nopriv_sayban_project_inquiry', 'sayban_project_inquiry_handler' );
add_action( 'admin_post_sayban_project_inquiry', 'sayban_project_inquiry_handler' );

/* =========================================================================
   5. [sayban_projects] shortcode — card grid (for the homepage, later)
   ========================================================================= */
/**
 * Projects in display order: active first, "Coming Soon" pushed to the end,
 * then by menu_order (ASC) and date (DESC). Shared by the shortcode + archive.
 */
function sayban_sorted_projects( $limit = -1 ) {
	$q = new WP_Query( array(
		'post_type'      => 'sayban_project',
		'post_status'    => 'publish',
		'posts_per_page' => -1,
		'orderby'        => array( 'menu_order' => 'ASC', 'date' => 'DESC' ),
		'no_found_rows'  => true,
	) );
	$posts = $q->posts;
	wp_reset_postdata();
	usort( $posts, function ( $a, $b ) {
		$ca = stripos( (string) get_post_meta( $a->ID, 'sayban_status', true ), 'coming' ) !== false ? 1 : 0;
		$cb = stripos( (string) get_post_meta( $b->ID, 'sayban_status', true ), 'coming' ) !== false ? 1 : 0;
		if ( $ca !== $cb ) { return $ca - $cb; }                       // coming-soon last
		if ( $a->menu_order !== $b->menu_order ) { return $a->menu_order - $b->menu_order; }
		return strcmp( $b->post_date, $a->post_date );
	} );
	if ( $limit > 0 ) { $posts = array_slice( $posts, 0, $limit ); }
	return $posts;
}

function sayban_projects_shortcode( $atts ) {
	$a = shortcode_atts( array( 'count' => 6 ), $atts, 'sayban_projects' );
	$posts = sayban_sorted_projects( (int) $a['count'] );
	if ( ! $posts ) { return ''; }
	ob_start();
	echo '<div class="sbp-grid">';
	foreach ( $posts as $p ) { echo sayban_project_card_html( $p->ID ); }
	echo '</div>';
	return ob_get_clean();
}
add_shortcode( 'sayban_projects', 'sayban_projects_shortcode' );

/** One dark project card (shared by shortcode + archive). */
function sayban_project_card_html( $pid ) {
	$img   = get_the_post_thumbnail_url( $pid, 'large' );
	$loc   = sayban_pf( $pid, 'location' );
	$type  = sayban_pf( $pid, 'ptype' );
	$price = sayban_pf( $pid, 'price_from' );
	$status = sayban_pf( $pid, 'status' );
	$link  = get_permalink( $pid );
	ob_start(); ?>
	<article class="sbp-card">
	  <a class="sbp-card-img" href="<?php echo esc_url( $link ); ?>" <?php if ( $img ) echo 'style="background-image:url(\'' . esc_url( $img ) . '\')"'; ?>>
		<?php if ( $status ) : ?><span class="sbp-card-status"><?php echo esc_html( $status ); ?></span><?php endif; ?>
	  </a>
	  <div class="sbp-card-body">
		<?php if ( $type ) : ?><span class="sbp-tag"><?php echo esc_html( $type ); ?></span><?php endif; ?>
		<h3 class="sbp-card-title"><a href="<?php echo esc_url( $link ); ?>"><?php echo esc_html( get_the_title( $pid ) ); ?></a></h3>
		<?php if ( $loc ) : ?><p class="sbp-card-loc"><?php echo esc_html( $loc ); ?></p><?php endif; ?>
		<?php if ( $price ) : ?><div class="sbp-card-price"><span>Starting from</span><b><?php echo esc_html( $price ); ?></b></div><?php endif; ?>
		<div class="sbp-card-btns">
		  <a class="sbp-btn sbp-btn-gold" href="<?php echo esc_url( $link ); ?>">View Project</a>
		  <a class="sbp-btn sbp-btn-ghostgold" href="<?php echo esc_url( $link ); ?>#payment">Payment Plan</a>
		</div>
	  </div>
	</article>
	<?php return ob_get_clean();
}
