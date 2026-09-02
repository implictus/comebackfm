<?php
/**
 * Theme footer + sticky player shell (never swapped by PJAX).
 *
 * @package PiratenKrakers
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
</div><!-- #pk-app -->
<footer class="pk-footer" id="pk-footer">
	<div class="pk-footer-inner">
		<div class="pk-footer-brand">
			<?php pk_the_logo( 'footer' ); ?>
			<p class="pk-footer-tag"><?php echo esc_html( pk_get_option( 'tagline', 'Muziek uit het hart' ) ); ?></p>
			<p class="pk-footer-copy">Nederlandse piratenmuziek, live en gezellig. Mensen achter de knoppen, geen algoritme.</p>
			<?php pk_social_links( 'pk-social pk-social--footer' ); ?>
		</div>
		<div class="pk-footer-col">
			<h2 class="pk-footer-title"><?php esc_html_e( 'Luisteren', 'piratenkrakers' ); ?></h2>
			<?php
			wp_nav_menu(
				array(
					'theme_location' => 'footer',
					'container'      => false,
					'menu_class'     => 'pk-footer-list',
					'fallback_cb'    => 'pk_fallback_menu',
					'depth'          => 1,
				)
			);
			?>
		</div>
		<div class="pk-footer-col">
			<h2 class="pk-footer-title"><?php esc_html_e( 'Studio', 'piratenkrakers' ); ?></h2>
			<ul class="pk-footer-list">
				<?php if ( pk_get_option( 'contact_email' ) ) : ?>
					<li><a href="mailto:<?php echo esc_attr( pk_get_option( 'contact_email' ) ); ?>"><?php echo esc_html( pk_get_option( 'contact_email' ) ); ?></a></li>
				<?php endif; ?>
				<?php if ( pk_get_option( 'contact_phone' ) ) : ?>
					<li><a href="tel:<?php echo esc_attr( preg_replace( '/\s+/', '', (string) pk_get_option( 'contact_phone' ) ) ); ?>"><?php echo esc_html( pk_get_option( 'contact_phone' ) ); ?></a></li>
				<?php endif; ?>
				<li><a href="<?php echo esc_url( home_url( '/verzoekjes/' ) ); ?>"><?php esc_html_e( 'Stuur een verzoekje', 'piratenkrakers' ); ?></a></li>
				<li><a href="<?php echo esc_url( home_url( '/contact/' ) ); ?>"><?php esc_html_e( 'Contact', 'piratenkrakers' ); ?></a></li>
			</ul>
		</div>
	</div>
	<div class="pk-footer-bar">
		<p>© <?php echo esc_html( wp_date( 'Y' ) ); ?> <?php echo esc_html( pk_get_option( 'station_name', 'PiratenKrakers.nl' ) ); ?></p>
	</div>
</footer>
<?php get_template_part( 'template-parts/player-sticky' ); ?>
<?php wp_footer(); ?>
</body>
</html>
