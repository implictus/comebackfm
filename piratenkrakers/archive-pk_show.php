<?php
/**
 * Programmagids.
 *
 * @package PiratenKrakers
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
get_header();
$schedule = pk_get_schedule();
$today    = pk_current_weekday();
?>
<main class="pk-main pk-main--schedule" id="main">
	<header class="pk-pagehead">
		<p class="pk-kicker"><?php esc_html_e( 'Weekoverzicht', 'piratenkrakers' ); ?></p>
		<h1><?php esc_html_e( 'Programma', 'piratenkrakers' ); ?></h1>
		<p class="pk-lede"><?php esc_html_e( 'Van ochtendkrakers tot nachtradio. Tijden in Nederlandse tijd.', 'piratenkrakers' ); ?></p>
	</header>
	<div class="pk-schedule">
		<?php foreach ( pk_weekdays() as $num => $label ) : ?>
			<section class="pk-day<?php echo $num === $today ? ' is-today' : ''; ?>" id="dag-<?php echo esc_attr( (string) $num ); ?>">
				<h2><?php echo esc_html( $label ); ?><?php echo $num === $today ? ' <span class="pk-pill">vandaag</span>' : ''; ?></h2>
				<?php if ( empty( $schedule[ $num ] ) ) : ?>
					<p class="pk-muted"><?php esc_html_e( 'Nog geen programma ingepland.', 'piratenkrakers' ); ?></p>
				<?php else : ?>
					<?php
					foreach ( $schedule[ $num ] as $post ) {
						setup_postdata( $GLOBALS['post'] = $post ); // phpcs:ignore
						get_template_part( 'template-parts/card-show' );
					}
					wp_reset_postdata();
					?>
				<?php endif; ?>
			</section>
		<?php endforeach; ?>
	</div>
</main>
<?php
get_footer();
