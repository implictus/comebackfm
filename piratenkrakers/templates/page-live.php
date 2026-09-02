<?php
/**
 * Template Name: Live
 *
 * @package PiratenKrakers
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
get_header();
$np      = PK_Radio_Engine::now_playing();
$streams = pk_get_enabled_streams();
?>
<main class="pk-main pk-main--live" id="main">
	<?php get_template_part( 'template-parts/hero-live' ); ?>
	<section class="pk-section">
		<div class="pk-section-head">
			<p class="pk-kicker"><?php esc_html_e( 'Streams', 'piratenkrakers' ); ?></p>
			<h2><?php esc_html_e( 'Kies je ether', 'piratenkrakers' ); ?></h2>
		</div>
		<div class="pk-grid pk-grid--streams">
			<?php foreach ( $streams as $id => $stream ) : ?>
				<button class="pk-streamcard" type="button" data-pk-play data-pk-stream-id="<?php echo esc_attr( $id ); ?>">
					<span class="pk-live-dot" aria-hidden="true"></span>
					<strong><?php echo esc_html( $stream['name'] ); ?></strong>
					<span><?php echo empty( $stream['stream_url'] ) ? esc_html__( 'Nog niet gekoppeld', 'piratenkrakers' ) : esc_html__( 'Luister live', 'piratenkrakers' ); ?></span>
				</button>
			<?php endforeach; ?>
		</div>
		<div class="pk-prose">
			<?php
			while ( have_posts() ) :
				the_post();
				the_content();
			endwhile;
			?>
		</div>
	</section>
</main>
<?php
get_footer();
