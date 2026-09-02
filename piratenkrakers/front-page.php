<?php
/**
 * Homepage — live first.
 *
 * @package PiratenKrakers
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
get_header();

$djs    = get_posts( array( 'post_type' => 'pk_dj', 'posts_per_page' => 8, 'post_status' => 'publish' ) );
$news   = get_posts( array( 'post_type' => 'post', 'posts_per_page' => 3, 'post_status' => 'publish' ) );
$today  = pk_current_weekday();
$sched  = pk_get_schedule();
$tonite = $sched[ $today ] ?? array();
?>
<main class="pk-main pk-main--home" id="main">
	<?php get_template_part( 'template-parts/hero-live' ); ?>

	<?php if ( $tonite ) : ?>
	<section class="pk-section pk-dayguide" aria-label="<?php esc_attr_e( 'Programma vandaag', 'piratenkrakers' ); ?>">
		<div class="pk-section-head">
			<p class="pk-kicker"><?php echo esc_html( pk_weekdays()[ $today ] ); ?></p>
			<h2><?php esc_html_e( 'Vandaag op PiratenKrakers', 'piratenkrakers' ); ?></h2>
			<a class="pk-btn pk-btn-ghost pk-btn-compact" href="<?php echo esc_url( get_post_type_archive_link( 'pk_show' ) ?: home_url( '/programma/' ) ); ?>"><?php esc_html_e( 'Bekijk volledig programma', 'piratenkrakers' ); ?></a>
		</div>
		<div class="pk-dayguide-track">
			<?php foreach ( $tonite as $show ) : ?>
				<?php
				$start = get_post_meta( $show->ID, 'pk_start', true );
				$end   = get_post_meta( $show->ID, 'pk_end', true );
				$dj_id = absint( get_post_meta( $show->ID, 'pk_dj_id', true ) );
				$on    = pk_is_on_air( (string) $start, (string) $end );
				?>
				<article class="pk-slot<?php echo $on ? ' is-live' : ''; ?>">
					<?php if ( $on ) : ?>
						<span class="pk-pill pk-pill--live"><span class="pk-live-dot" aria-hidden="true"></span> <?php esc_html_e( 'LIVE', 'piratenkrakers' ); ?></span>
					<?php endif; ?>
					<time><?php echo esc_html( $start ); ?> – <?php echo esc_html( $end ); ?></time>
					<h3><a href="<?php echo esc_url( get_permalink( $show ) ); ?>"><?php echo esc_html( get_the_title( $show ) ); ?></a></h3>
					<?php if ( $dj_id ) : ?>
						<p><?php echo esc_html( get_the_title( $dj_id ) ); ?></p>
					<?php endif; ?>
					<?php if ( $on ) : ?>
						<div class="pk-vu pk-vu--slot" aria-hidden="true" data-pk-vu>
							<span></span><span></span><span></span><span></span><span></span>
						</div>
					<?php endif; ?>
				</article>
			<?php endforeach; ?>
		</div>
	</section>
	<?php endif; ?>

	<?php if ( $djs ) : ?>
	<section class="pk-section">
		<div class="pk-section-head">
			<p class="pk-kicker"><?php esc_html_e( 'De studio', 'piratenkrakers' ); ?></p>
			<h2><?php esc_html_e( "DJ's", 'piratenkrakers' ); ?></h2>
			<a class="pk-textlink" href="<?php echo esc_url( get_post_type_archive_link( 'pk_dj' ) ?: home_url( '/djs/' ) ); ?>"><?php esc_html_e( 'Alle DJ’s', 'piratenkrakers' ); ?></a>
		</div>
		<div class="pk-grid pk-grid--djs">
			<?php
			foreach ( $djs as $post ) {
				setup_postdata( $GLOBALS['post'] = $post ); // phpcs:ignore
				get_template_part( 'template-parts/card-dj' );
			}
			wp_reset_postdata();
			?>
		</div>
	</section>
	<?php endif; ?>

	<section class="pk-section pk-cta-request">
		<div class="pk-cta-card">
			<p class="pk-kicker"><?php esc_html_e( 'Groeten & platen', 'piratenkrakers' ); ?></p>
			<h2><?php esc_html_e( 'Stuur een verzoekje', 'piratenkrakers' ); ?></h2>
			<p>Jarig, nachtdienst of zin in dat ene nummer? Zet je naam, plaats en plaat erin.</p>
			<a class="pk-btn pk-btn-primary" href="<?php echo esc_url( home_url( '/verzoekjes/' ) ); ?>"><?php esc_html_e( 'Verzoekje sturen', 'piratenkrakers' ); ?></a>
		</div>
	</section>

	<?php if ( $news ) : ?>
	<section class="pk-section">
		<div class="pk-section-head">
			<p class="pk-kicker"><?php esc_html_e( 'Nieuws', 'piratenkrakers' ); ?></p>
			<h2><?php esc_html_e( 'Uit de studio', 'piratenkrakers' ); ?></h2>
			<a class="pk-textlink" href="<?php echo esc_url( get_permalink( get_option( 'page_for_posts' ) ) ?: home_url( '/nieuws/' ) ); ?>"><?php esc_html_e( 'Alles', 'piratenkrakers' ); ?></a>
		</div>
		<div class="pk-grid pk-grid--news">
			<?php
			foreach ( $news as $post ) {
				setup_postdata( $GLOBALS['post'] = $post ); // phpcs:ignore
				get_template_part( 'template-parts/card-news' );
			}
			wp_reset_postdata();
			?>
		</div>
	</section>
	<?php endif; ?>
</main>
<?php
get_footer();
