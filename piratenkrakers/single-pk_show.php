<?php
/**
 * Single show.
 *
 * @package PiratenKrakers
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
get_header();
the_post();
$start = get_post_meta( get_the_ID(), 'pk_start', true );
$end   = get_post_meta( get_the_ID(), 'pk_end', true );
$dj_id = absint( get_post_meta( get_the_ID(), 'pk_dj_id', true ) );
$genre = get_post_meta( get_the_ID(), 'pk_genre_text', true );
$days  = array_map( 'intval', (array) get_post_meta( get_the_ID(), 'pk_weekdays', true ) );
$labels = pk_weekdays();
$dnames = array();
foreach ( $days as $d ) {
	if ( isset( $labels[ $d ] ) ) {
		$dnames[] = $labels[ $d ];
	}
}
?>
<main class="pk-main pk-main--single" id="main">
	<article class="pk-article">
		<header class="pk-pagehead">
			<p class="pk-kicker"><?php esc_html_e( 'Programma', 'piratenkrakers' ); ?></p>
			<h1><?php the_title(); ?></h1>
			<p class="pk-lede">
				<?php echo esc_html( implode( ', ', $dnames ) ); ?>
				· <?php echo esc_html( $start ); ?>–<?php echo esc_html( $end ); ?>
				<?php if ( $genre ) : ?> · <?php echo esc_html( $genre ); ?><?php endif; ?>
			</p>
		</header>
		<div class="pk-split">
			<div class="pk-prose"><?php the_content(); ?></div>
			<?php if ( $dj_id ) : ?>
				<aside class="pk-panel">
					<p class="pk-kicker"><?php esc_html_e( 'DJ', 'piratenkrakers' ); ?></p>
					<h2><a href="<?php echo esc_url( get_permalink( $dj_id ) ); ?>"><?php echo esc_html( get_the_title( $dj_id ) ); ?></a></h2>
					<?php if ( has_post_thumbnail( $dj_id ) ) : ?>
						<a href="<?php echo esc_url( get_permalink( $dj_id ) ); ?>"><?php echo get_the_post_thumbnail( $dj_id, 'pk-dj' ); ?></a>
					<?php endif; ?>
				</aside>
			<?php endif; ?>
		</div>
	</article>
</main>
<?php
get_footer();
