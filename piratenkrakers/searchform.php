<?php
/**
 * Search form.
 *
 * @package PiratenKrakers
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<form class="pk-search" role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>">
	<label>
		<span class="screen-reader-text"><?php esc_html_e( 'Zoeken', 'piratenkrakers' ); ?></span>
		<input type="search" name="s" value="<?php echo esc_attr( get_search_query() ); ?>" placeholder="<?php esc_attr_e( 'Zoek nieuws, DJ’s, programma’s…', 'piratenkrakers' ); ?>">
	</label>
	<button class="pk-btn pk-btn-gold" type="submit"><?php esc_html_e( 'Zoek', 'piratenkrakers' ); ?></button>
</form>
