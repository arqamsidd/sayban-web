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
	if ( is_singular( 'rem_property' ) ) {
		$custom = get_stylesheet_directory() . '/single-rem_property.php';
		if ( file_exists( $custom ) ) {
			return $custom;
		}
	}
	return $template;
}, 100 );

/**
 * Sayban: fonts + single-property stylesheet, only on single listings.
 */
add_action( 'wp_enqueue_scripts', function () {
	if ( ! is_singular( 'rem_property' ) ) {
		return;
	}
	wp_enqueue_style(
		'sayban-fonts',
		'https://fonts.googleapis.com/css2?family=Marcellus&family=Manrope:wght@400;500;600;700;800&display=swap',
		array(),
		null
	);
	$css = get_stylesheet_directory() . '/assets/sayban-single.css';
	wp_enqueue_style(
		'sayban-single',
		get_stylesheet_directory_uri() . '/assets/sayban-single.css',
		array(),
		file_exists( $css ) ? (string) filemtime( $css ) : HELLO_ELEMENTOR_CHILD_VERSION
	);
}, 30 );
