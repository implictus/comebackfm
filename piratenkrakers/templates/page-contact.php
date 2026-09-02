<?php
/**
 * Template Name: Contact
 *
 * @package PiratenKrakers
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
get_header();
$email = pk_get_option( 'contact_email' );
$phone = pk_get_option( 'contact_phone' );
$addr  = pk_get_option( 'contact_address' );
?>
<main class="pk-main" id="main">
	<header class="pk-pagehead">
		<p class="pk-kicker"><?php esc_html_e( 'Studio', 'piratenkrakers' ); ?></p>
		<h1><?php the_title(); ?></h1>
	</header>
	<div class="pk-split">
		<div class="pk-prose">
			<?php
			while ( have_posts() ) :
				the_post();
				the_content();
			endwhile;
			?>
		</div>
		<aside class="pk-panel">
			<h2><?php echo esc_html( pk_get_option( 'station_name' ) ); ?></h2>
			<?php if ( $addr ) : ?><p><?php echo nl2br( esc_html( $addr ) ); ?></p><?php endif; ?>
			<?php if ( $email ) : ?><p><a href="mailto:<?php echo esc_attr( $email ); ?>"><?php echo esc_html( $email ); ?></a></p><?php endif; ?>
			<?php if ( $phone ) : ?><p><a href="tel:<?php echo esc_attr( preg_replace( '/\s+/', '', (string) $phone ) ); ?>"><?php echo esc_html( $phone ); ?></a></p><?php endif; ?>
			<?php pk_social_links(); ?>
			<p class="pk-muted"><?php esc_html_e( 'Voor verzoekjes gebruik je het verzoekjesformulier — die komen rechtstreeks bij de DJ.', 'piratenkrakers' ); ?></p>
			<a class="pk-btn pk-btn-gold" href="<?php echo esc_url( home_url( '/verzoekjes/' ) ); ?>"><?php esc_html_e( 'Naar verzoekjes', 'piratenkrakers' ); ?></a>
		</aside>
	</div>
</main>
<?php
get_footer();
