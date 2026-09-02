<?php
/**
 * Show row/card.
 *
 * @package PiratenKrakers
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$start = get_post_meta( get_the_ID(), 'pk_start', true );
$end   = get_post_meta( get_the_ID(), 'pk_end', true );
$dj_id = absint( get_post_meta( get_the_ID(), 'pk_dj_id', true ) );
$genre = get_post_meta( get_the_ID(), 'pk_genre_text', true );
$on    = pk_is_on_air( (string) $start, (string) $end );
?>
<article <?php post_class( 'pk-showrow' . ( $on ? ' is-live' : '' ) ); ?>>
	<time class="pk-showrow-time"><?php echo esc_html( $start ); ?>–<?php echo esc_html( $end ); ?></time>
	<div class="pk-showrow-body">
		<h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
		<p>
			<?php if ( $dj_id ) : ?>
				<a href="<?php echo esc_url( get_permalink( $dj_id ) ); ?>"><?php echo esc_html( get_the_title( $dj_id ) ); ?></a>
			<?php endif; ?>
			<?php if ( $genre ) : ?>
				<span class="pk-sep">·</span> <?php echo esc_html( $genre ); ?>
			<?php endif; ?>
		</p>
	</div>
	<?php if ( $on ) : ?><span class="pk-pill pk-pill--live"><?php esc_html_e( 'Nu live', 'piratenkrakers' ); ?></span><?php endif; ?>
</article>
