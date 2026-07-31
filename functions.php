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
	if ( ! $is_single && ! $is_listings && ! $is_create && ! $is_home ) {
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
}, 30 );
