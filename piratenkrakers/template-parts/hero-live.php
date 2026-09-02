<?php
/**
 * Hero: brand, slogan, live, giant play, now playing.
 *
 * @package PiratenKrakers
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$np      = PK_Radio_Engine::now_playing();
$hero    = pk_asset( 'img/hero-studio.jpg' );
$art     = $np['artwork'] ?: pk_get_option( 'fallback_artwork' );
$offline = ! empty( $np['offline'] );
$tagline = pk_get_option( 'tagline', 'Muziek uit het hart' );
?>
<section class="pk-hero" aria-label="<?php esc_attr_e( 'Live radio', 'piratenkrakers' ); ?>">
	<div class="pk-hero-bg" style="--pk-hero-image:url('<?php echo esc_url( $hero ); ?>')"></div>
	<div class="pk-hero-shade"></div>
	<div class="pk-hero-inner">
		<div class="pk-hero-copy">
			<p class="pk-onair" aria-hidden="true">ON AIR</p>
			<h1 class="pk-hero-brand">PiratenKrakers<span>.nl</span></h1>
			<p class="pk-slogan"><?php echo esc_html( $tagline ); ?></p>
			<p class="pk-live-badge <?php echo $offline ? 'is-offline' : 'is-live'; ?>" data-pk-live-badge>
				<span class="pk-live-dot" aria-hidden="true"></span>
				<span data-pk-live-label><?php echo $offline ? esc_html__( 'OFFLINE', 'piratenkrakers' ) : esc_html__( 'NU LIVE', 'piratenkrakers' ); ?></span>
			</p>
			<p class="pk-hero-track">
				<strong data-pk-field="artist"><?php echo esc_html( $np['artist'] ); ?></strong>
				<span data-pk-field="title"><?php echo esc_html( $np['title'] ); ?></span>
			</p>
			<p class="pk-hero-dj" data-pk-field="dj"><?php echo esc_html( $np['dj'] ?: '' ); ?></p>
			<p class="pk-hero-show" data-pk-field="show"><?php echo esc_html( $np['show'] ?: '' ); ?></p>
			<p class="pk-hero-offline<?php echo $offline ? ' is-on' : ''; ?>" data-pk-offline-msg>
				<?php esc_html_e( 'PiratenKrakers is momenteel offline', 'piratenkrakers' ); ?>
			</p>
		</div>

		<div class="pk-hero-playwrap">
			<button class="pk-play-hero" type="button" data-pk-play aria-label="<?php esc_attr_e( 'Luister live', 'piratenkrakers' ); ?>">
				<span class="pk-play-waves" aria-hidden="true"></span>
				<span class="pk-play-icon" data-pk-play-icon aria-hidden="true"></span>
			</button>
			<p class="pk-play-caption"><?php esc_html_e( 'Luister live', 'piratenkrakers' ); ?></p>
		</div>
	</div>

	<article class="pk-now" data-pk-nowcard>
		<p class="pk-now-kicker">
			<span class="pk-live-dot" aria-hidden="true"></span>
			<?php esc_html_e( 'Nu op de radio', 'piratenkrakers' ); ?>
		</p>
		<figure class="pk-artwork">
			<img src="<?php echo esc_url( $art ); ?>" alt="" width="160" height="160" data-pk-artwork decoding="async">
		</figure>
		<div class="pk-now-meta">
			<p class="pk-now-artist" data-pk-field="artist"><?php echo esc_html( $np['artist'] ); ?></p>
			<p class="pk-now-title" data-pk-field="title"><?php echo esc_html( $np['title'] ); ?></p>
			<p class="pk-now-byline">
				<span data-pk-field="dj"><?php echo esc_html( $np['dj'] ?: '' ); ?></span>
				<?php if ( ! empty( $np['show'] ) ) : ?>
					<span class="pk-sep">·</span>
					<span data-pk-field="show"><?php echo esc_html( $np['show'] ); ?></span>
				<?php else : ?>
					<span data-pk-field="show"></span>
				<?php endif; ?>
			</p>
		</div>
		<div class="pk-now-aside">
			<div class="pk-vu" aria-hidden="true" data-pk-vu>
				<span></span><span></span><span></span><span></span><span></span><span></span><span></span>
			</div>
			<p class="pk-listeners">
				<span data-pk-field="listeners"><?php echo esc_html( (string) $np['listeners'] ); ?></span>
				<?php esc_html_e( 'luisteraars', 'piratenkrakers' ); ?>
			</p>
		</div>
	</article>
</section>
