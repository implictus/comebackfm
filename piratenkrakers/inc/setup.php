<?php
/**
 * Theme supports, menus, image sizes.
 *
 * @package PiratenKrakers
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * After setup.
 */
function pk_setup(): void {
	load_theme_textdomain( 'piratenkrakers', PK_THEME_DIR . '/languages' );

	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'automatic-feed-links' );
	add_theme_support( 'html5', array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script' ) );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'align-wide' );
	add_theme_support( 'custom-logo', array(
		'height'      => 120,
		'width'       => 120,
		'flex-height' => true,
		'flex-width'  => true,
	) );
	add_theme_support( 'editor-styles' );

	register_nav_menus(
		array(
			'primary' => __( 'Hoofdmenu', 'piratenkrakers' ),
			'footer'  => __( 'Footermenu', 'piratenkrakers' ),
		)
	);

	add_image_size( 'pk-card', 720, 480, true );
	add_image_size( 'pk-dj', 640, 800, true );
	add_image_size( 'pk-artwork', 400, 400, true );
	add_image_size( 'pk-hero', 1600, 900, true );
}
add_action( 'after_setup_theme', 'pk_setup' );

/**
 * Content width.
 */
function pk_content_width(): void {
	$GLOBALS['content_width'] = 760;
}
add_action( 'after_setup_theme', 'pk_content_width', 0 );

/**
 * Document title parts.
 */
function pk_document_title( array $parts ): array {
	$name = pk_get_option( 'station_name', 'PiratenKrakers.nl' );
	if ( empty( $parts['site'] ) ) {
		$parts['site'] = $name;
	}
	if ( is_front_page() ) {
		$parts['title'] = $name;
		$parts['tagline'] = pk_get_option( 'tagline', 'Muziek uit het hart' );
	}
	return $parts;
}
add_filter( 'document_title_parts', 'pk_document_title' );

/**
 * Body classes.
 */
function pk_body_class( array $classes ): array {
	$classes[] = 'pk-body';
	$classes[] = 'pk-has-player';
	if ( is_front_page() ) {
		$classes[] = 'pk-home';
	}
	$np = class_exists( 'PK_Radio_Engine' ) ? PK_Radio_Engine::now_playing() : array();
	if ( ! empty( $np['demo'] ) ) {
		$classes[] = 'pk-demo';
	}
	if ( ! empty( $np['offline'] ) ) {
		$classes[] = 'is-offline';
	}
	return $classes;
}
add_filter( 'body_class', 'pk_body_class' );

/**
 * Excerpt length.
 */
function pk_excerpt_length(): int {
	return 28;
}
add_filter( 'excerpt_length', 'pk_excerpt_length' );

/**
 * Excerpt more.
 */
function pk_excerpt_more(): string {
	return '…';
}
add_filter( 'excerpt_more', 'pk_excerpt_more' );

/**
 * Disable WP emojis on the frontend (performance).
 */
function pk_disable_emojis(): void {
	if ( is_admin() ) {
		return;
	}
	remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
	remove_action( 'wp_print_styles', 'print_emoji_styles' );
	remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
	remove_action( 'admin_print_styles', 'print_emoji_styles' );
}
add_action( 'init', 'pk_disable_emojis' );
