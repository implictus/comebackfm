<?php
/**
 * PiratenKrakers theme bootstrap.
 *
 * @package PiratenKrakers
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'PK_VERSION', '1.3.2' );
define( 'PK_THEME_DIR', get_template_directory() );
define( 'PK_THEME_URI', get_template_directory_uri() );
define( 'PK_TEXTDOMAIN', 'piratenkrakers' );
define( 'PK_OPTION_KEY', 'pk_options' );

require_once PK_THEME_DIR . '/inc/helpers.php';
require_once PK_THEME_DIR . '/inc/setup.php';
require_once PK_THEME_DIR . '/inc/assets.php';
require_once PK_THEME_DIR . '/inc/cpt.php';
require_once PK_THEME_DIR . '/inc/meta.php';
require_once PK_THEME_DIR . '/inc/radio/class-adapter.php';
require_once PK_THEME_DIR . '/inc/radio/class-adapter-custom.php';
require_once PK_THEME_DIR . '/inc/radio/class-adapter-azuracast.php';
require_once PK_THEME_DIR . '/inc/radio/class-adapter-icecast.php';
require_once PK_THEME_DIR . '/inc/radio/class-adapter-shoutcast.php';
require_once PK_THEME_DIR . '/inc/radio/class-adapter-sam.php';
require_once PK_THEME_DIR . '/inc/radio/class-engine.php';
require_once PK_THEME_DIR . '/inc/rest.php';
require_once PK_THEME_DIR . '/inc/requests.php';
require_once PK_THEME_DIR . '/inc/seo.php';
require_once PK_THEME_DIR . '/inc/template-tags.php';
require_once PK_THEME_DIR . '/inc/demo-content.php';

if ( is_admin() ) {
	require_once PK_THEME_DIR . '/inc/admin/class-admin.php';
}

/**
 * Theme activation: flush rewrites and optionally seed demo content.
 */
function pk_after_switch_theme(): void {
	pk_register_cpts();
	flush_rewrite_rules();

	if ( ! get_option( 'pk_demo_installed' ) && ! defined( 'PK_SKIP_DEMO' ) ) {
		PK_Demo_Content::install();
	}
}
add_action( 'after_switch_theme', 'pk_after_switch_theme' );

/**
 * Flush rewrites on theme deactivation.
 */
function pk_switch_theme(): void {
	flush_rewrite_rules();
}
add_action( 'switch_theme', 'pk_switch_theme' );
