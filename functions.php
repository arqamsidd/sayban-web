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
	return $template;
}, 100 );

/**
 * Sayban: fonts + single-property stylesheet, only on single listings.
 */
add_action( 'wp_enqueue_scripts', function () {
	$is_single   = is_singular( 'rem_property' );
	$is_listings = is_page( 'properties' );
	if ( ! $is_single && ! $is_listings ) {
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
}, 30 );
