<?php
/**
 * Hello Elementor Child — Sayban.pk theme functions.
 *
 * @package HelloElementorChild
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

define( 'HELLO_ELEMENTOR_CHILD_VERSION', '2.0.0' );

// Shared parts: design card + [sayban_finder] / [sayban_featured] shortcodes.
require_once __DIR__ . '/inc/sayban-parts.php';
// Blog helpers: read-time, excerpt, category chip, editorial post card.
require_once __DIR__ . '/inc/sayban-blog-parts.php';
// Developer Projects: sayban_project CPT, meta, inquiry handler, [sayban_projects].
require_once __DIR__ . '/inc/sayban-projects.php';

/** True when the current page should load the Sayban home CSS (finder/featured). */
function sayban_needs_home_css() {
	if ( is_front_page() || is_home() ) { return true; }
	if ( is_singular() ) {
		$post = get_post();
		if ( $post && ( has_shortcode( $post->post_content, 'sayban_finder' ) || has_shortcode( $post->post_content, 'sayban_featured' ) ) ) {
			return true;
		}
	}
	return false;
}

/**
 * Load child theme base stylesheet.
 */
function hello_elementor_child_scripts_styles() {
	wp_enqueue_style(
		'hello-elementor-child-style',
		get_stylesheet_directory_uri() . '/style.css',
		array( 'hello-elementor-theme-style' ),
		HELLO_ELEMENTOR_CHILD_VERSION
	);
}
add_action( 'wp_enqueue_scripts', 'hello_elementor_child_scripts_styles', 20 );

/**
 * Sitewide color system — the single source of truth for every color on the
 * site (custom templates, Elementor pages, and REM). Loaded on EVERY front-end
 * page, early, so its :root variables are always available to resolve var()s.
 *
 *  1. assets/sayban-colors.css  — the default, fully-commented palette (edit to
 *     re-theme the whole site).
 *  2. uploads/sayban/sayban-colors.css  — OPTIONAL override, loaded last, wins.
 *     Drop a variation file here to re-skin the live site with no code deploy;
 *     delete it to fall back to the theme default.
 */
function sayban_enqueue_color_system() {
	$dir  = get_stylesheet_directory();
	$uri  = get_stylesheet_directory_uri();
	$base = $dir . '/assets/sayban-colors.css';
	if ( file_exists( $base ) ) {
		wp_enqueue_style( 'sayban-colors', $uri . '/assets/sayban-colors.css', array(), (string) filemtime( $base ) );
	}
	// Optional live override from uploads (no deploy needed).
	$ov_path = WP_CONTENT_DIR . '/uploads/sayban/sayban-colors.css';
	if ( file_exists( $ov_path ) ) {
		wp_enqueue_style( 'sayban-colors-override', content_url( '/uploads/sayban/sayban-colors.css' ), array( 'sayban-colors' ), (string) filemtime( $ov_path ) );
	}
}
add_action( 'wp_enqueue_scripts', 'sayban_enqueue_color_system', 1 );

/**
 * Sayban: use our custom single-property template instead of REM Pro's.
 * REM hooks template_include at priority 99; we run at 100 so ours wins.
 */
add_filter( 'template_include', function ( $template ) {
	$dir = get_stylesheet_directory();
	if ( is_singular( 'rem_property' ) && file_exists( $dir . '/single-rem_property.php' ) ) {
		return $dir . '/single-rem_property.php';
	}
	if ( is_page( 'properties' ) && file_exists( $dir . '/page-listings.php' ) ) {
		return $dir . '/page-listings.php';
	}
	if ( is_page( array( 'create-property', 'create-property-page' ) ) && file_exists( $dir . '/page-create-property.php' ) ) {
		return $dir . '/page-create-property.php';
	}
	if ( is_page( 'agent-registration' ) && file_exists( $dir . '/page-agent-registration.php' ) ) {
		return $dir . '/page-agent-registration.php';
	}
	if ( is_page( 'agent-login' ) && file_exists( $dir . '/page-agent-login.php' ) ) {
		return $dir . '/page-agent-login.php';
	}
	if ( is_page( 'blog' ) && file_exists( $dir . '/page-blog.php' ) ) {
		return $dir . '/page-blog.php';
	}
	if ( is_page( array( 'my-properties', 'edit-profile', 'edit-property' ) ) && file_exists( $dir . '/page-dashboard.php' ) ) {
		return $dir . '/page-dashboard.php';
	}
	return $template;
}, 100 );

/**
 * Sayban: fonts + single-property stylesheet, only on single listings.
 */
add_action( 'wp_enqueue_scripts', function () {
	$is_single   = is_singular( 'rem_property' );
	$is_listings = is_page( 'properties' );
	$is_create   = is_page( array( 'create-property', 'create-property-page' ) ) || is_page( array( 'agent-registration', 'agent-login' ) );
	$is_home     = sayban_needs_home_css();
	$is_blog     = is_page( 'blog' ) || is_singular( 'post' );
	$is_dash     = is_page( array( 'my-properties', 'edit-profile', 'edit-property' ) );
	$is_project  = is_singular( 'sayban_project' ) || is_post_type_archive( 'sayban_project' );
	if ( ! $is_project && is_singular() ) {
		// Any page/post whose content OR Elementor data holds [sayban_projects] (home, company, …).
		$pp = get_post();
		if ( $pp ) {
			$eld = get_post_meta( $pp->ID, '_elementor_data', true );
			if ( has_shortcode( $pp->post_content, 'sayban_projects' )
				|| ( is_string( $eld ) && strpos( $eld, '[sayban_projects' ) !== false ) ) {
				$is_project = true;
			}
			// Blog cards ([sayban_blog]) embedded on the homepage (or any page).
			if ( ! $is_blog
				&& ( has_shortcode( $pp->post_content, 'sayban_blog' )
					|| ( is_string( $eld ) && strpos( $eld, '[sayban_blog' ) !== false ) ) ) {
				$is_blog = true;
			}
		}
	}
	if ( ! $is_single && ! $is_listings && ! $is_create && ! $is_home && ! $is_blog && ! $is_dash && ! $is_project ) {
		return;
	}

	wp_enqueue_style(
		'sayban-fonts',
		'https://fonts.googleapis.com/css2?family=Marcellus&family=Manrope:wght@400;500;600;700;800&display=swap',
		array(),
		null
	);

	$dir = get_stylesheet_directory();
	$uri = get_stylesheet_directory_uri();
	$enqueue = function ( $handle, $file ) use ( $dir, $uri ) {
		$path = $dir . '/assets/' . $file;
		wp_enqueue_style(
			$handle,
			$uri . '/assets/' . $file,
			array(),
			file_exists( $path ) ? (string) filemtime( $path ) : HELLO_ELEMENTOR_CHILD_VERSION
		);
	};

	if ( $is_single ) {
		$enqueue( 'sayban-single', 'sayban-single.css' );
	}
	if ( $is_listings ) {
		$enqueue( 'sayban-listings', 'sayban-listings.css' );
	}
	if ( $is_create ) {
		$enqueue( 'sayban-create', 'sayban-create.css' );
	}
	if ( $is_home ) {
		$enqueue( 'sayban-home', 'sayban-home.css' );
	}
	if ( $is_blog ) {
		$enqueue( 'sayban-blog', 'sayban-blog.css' );
	}
	if ( $is_dash ) {
		$enqueue( 'sayban-create', 'sayban-create.css' );   // profile/edit forms + wizard reuse this
		$enqueue( 'sayban-dashboard', 'sayban-dashboard.css' );
	}
	if ( $is_project ) {
		$enqueue( 'sayban-project', 'sayban-project.css' );
	}
	// Shared gallery lightbox on single listings + single projects.
	if ( is_singular( 'rem_property' ) || is_singular( 'sayban_project' ) ) {
		$enqueue( 'sayban-lightbox', 'sayban-lightbox.css' );
		$ljs = $dir . '/assets/sayban-lightbox.js';
		wp_enqueue_script(
			'sayban-lightbox',
			$uri . '/assets/sayban-lightbox.js',
			array(),
			file_exists( $ljs ) ? (string) filemtime( $ljs ) : HELLO_ELEMENTOR_CHILD_VERSION,
			true
		);
	}
}, 30 );
