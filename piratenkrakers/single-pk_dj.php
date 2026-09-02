<?php
/**
 * DJ profile.
 *
 * @package PiratenKrakers
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
get_header();
the_post();
$role  = get_post_meta( get_the_ID(), 'pk_role', true );
$alias = get_post_meta( get_the_ID(), 'pk_alias', true );
$fav   = get_post_meta( get_the_ID(), 'pk_favorite_music', true );
$shows = pk_get_shows_for_dj( get_the_ID() );
$social = array(
	'facebook'  => get_post_meta( get_the_ID(), 'pk_facebook', true ),
	'instagram' => get_post_meta( get_the_ID(), 'pk_instagram', true ),
	'tiktok'    => get_post_meta( get_the_ID(), 'pk_tiktok', true ),
	'youtube'   => get_post_meta( get_the_ID(), 'pk_youtube', true ),
);
?>
<main class="pk-main pk-main--dj" id="main">
	<article class="pk-dj-profile">
		<div class="pk-dj-hero">
			<div class="pk-dj-photo">
				<?php if ( has_post_thumbnail() ) : ?>
					<?php the_post_thumbnail( 'pk-dj' ); ?>
				<?php else : ?>
					<span class="pk-avatar-fallback pk-avatar-fallback--xl"><?php echo esc_html( mb_strtoupper( mb_substr( get_the_title(), 0, 1 ) ) ); ?></span>
				<?php endif; ?>
			</div>
			<div>
				<p class="pk-kicker"><?php echo esc_html( $role ?: __( 'DJ', 'piratenkrakers' ) ); ?></p>
				<h1><?php the_title(); ?></h1>
				<?php if ( $alias ) : ?><p class="pk-lede"><?php echo esc_html( $alias ); ?></p><?php endif; ?>
				<ul class="pk-social">
					<?php foreach ( $social as $net => $url ) : ?>
						<?php if ( $url ) : ?>
							<li><a class="pk-social-link pk-social-link--<?php echo esc_attr( $net ); ?>" href="<?php echo esc_url( $url ); ?>" rel="noopener noreferrer" target="_blank"><?php echo esc_html( ucfirst( $net ) ); ?></a></li>
						<?php endif; ?>
					<?php endforeach; ?>
				</ul>
			</div>
		</div>
		<div class="pk-split">
			<div class="pk-prose"><?php the_content(); ?></div>
			<aside class="pk-panel">
				<?php if ( $fav ) : ?>
					<h2><?php esc_html_e( 'Favoriete muziek', 'piratenkrakers' ); ?></h2>
					<p><?php echo esc_html( $fav ); ?></p>
				<?php endif; ?>
				<?php if ( $shows ) : ?>
					<h2><?php esc_html_e( 'Programma', 'piratenkrakers' ); ?></h2>
					<ul class="pk-footer-list">
						<?php foreach ( $shows as $show ) : ?>
							<?php
							$start = get_post_meta( $show->ID, 'pk_start', true );
							$end   = get_post_meta( $show->ID, 'pk_end', true );
							$days  = array_map( 'intval', (array) get_post_meta( $show->ID, 'pk_weekdays', true ) );
							$dn    = array();
							foreach ( $days as $d ) {
								$dn[] = mb_substr( pk_weekdays()[ $d ] ?? '', 0, 2 );
							}
							?>
							<li>
								<a href="<?php echo esc_url( get_permalink( $show ) ); ?>"><?php echo esc_html( get_the_title( $show ) ); ?></a>
								<span class="pk-muted"><?php echo esc_html( implode( '/', $dn ) . ' ' . $start . '–' . $end ); ?></span>
							</li>
						<?php endforeach; ?>
					</ul>
				<?php endif; ?>
			</aside>
		</div>
	</article>
</main>
<?php
get_footer();
