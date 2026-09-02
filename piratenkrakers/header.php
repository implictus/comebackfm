<?php
/**
 * Theme header. Persistent chrome; main content is swapped by PJAX.
 *
 * @package PiratenKrakers
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?><!DOCTYPE html>
<html <?php language_attributes(); ?> class="pk-html">
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<a class="pk-skip" href="#pk-app"><?php esc_html_e( 'Ga naar inhoud', 'piratenkrakers' ); ?></a>
<div class="pk-noise" aria-hidden="true"></div>
<header class="pk-header" id="pk-header">
	<div class="pk-header-inner">
		<?php pk_the_logo( 'header' ); ?>
		<button class="pk-btn pk-btn-live pk-btn-live--header" type="button" data-pk-play>
			<span class="pk-live-dot" aria-hidden="true"></span>
			<span class="pk-btn-live-label"><?php esc_html_e( 'Luister live', 'piratenkrakers' ); ?></span>
		</button>
		<button class="pk-nav-toggle" type="button" aria-expanded="false" aria-controls="pk-nav" aria-label="<?php esc_attr_e( 'Menu', 'piratenkrakers' ); ?>" data-pk-nav-toggle>
			<span class="pk-nav-toggle-bars" aria-hidden="true"><span></span><span></span><span></span></span>
			<span class="screen-reader-text"><?php esc_html_e( 'Menu', 'piratenkrakers' ); ?></span>
		</button>
		<nav class="pk-nav" id="pk-nav" aria-label="<?php esc_attr_e( 'Hoofdmenu', 'piratenkrakers' ); ?>">
			<?php
			wp_nav_menu(
				array(
					'theme_location' => 'primary',
					'container'      => false,
					'menu_class'     => 'pk-nav-list',
					'fallback_cb'    => 'pk_fallback_menu',
					'depth'          => 1,
				)
			);
			?>
			<button class="pk-btn pk-btn-live" type="button" data-pk-play>
				<span class="pk-live-dot" aria-hidden="true"></span>
				<?php esc_html_e( 'Luister live', 'piratenkrakers' ); ?>
			</button>
		</nav>
	</div>
</header>
<div class="pk-app" id="pk-app">
