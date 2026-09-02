<?php
/**
 * 404.
 *
 * @package PiratenKrakers
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
get_header();
?>
<main class="pk-main pk-main--404" id="main">
	<header class="pk-pagehead">
		<p class="pk-kicker">404</p>
		<h1><?php esc_html_e( 'Deze frequentie is leeg', 'piratenkrakers' ); ?></h1>
		<p class="pk-lede"><?php esc_html_e( 'Deze pagina bestaat niet. De radio wel. Luister live of ga terug naar home.', 'piratenkrakers' ); ?></p>
		<p>
			<button class="pk-btn pk-btn-live" type="button" data-pk-play><?php esc_html_e( 'Luister Live', 'piratenkrakers' ); ?></button>
			<a class="pk-btn pk-btn-ghost" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Naar home', 'piratenkrakers' ); ?></a>
		</p>
	</header>
</main>
<?php
get_footer();
