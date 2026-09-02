<?php
/**
 * Sticky radio player.
 *
 * @package PiratenKrakers
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$np      = PK_Radio_Engine::now_playing();
$art     = $np['artwork'] ?: pk_get_option( 'fallback_artwork' );
$streams = pk_get_enabled_streams();
$current = pk_default_stream_id();
$multi   = count( $streams ) > 1;
?>
<div class="pk-player" id="pk-player-root" data-pk-player role="region" aria-label="<?php esc_attr_e( 'Radio speler', 'piratenkrakers' ); ?>">
	<div class="pk-player-inner">
		<figure class="pk-player-art">
			<img src="<?php echo esc_url( $art ); ?>" alt="" width="64" height="64" data-pk-artwork decoding="async">
		</figure>
		<div class="pk-player-now">
			<p class="pk-player-kicker">
				<span class="pk-live-dot" data-pk-live-dot aria-hidden="true"></span>
				<span data-pk-live-label><?php esc_html_e( 'LIVE', 'piratenkrakers' ); ?></span>
			</p>
			<p class="pk-player-track">
				<strong data-pk-field="artist"><?php echo esc_html( $np['artist'] ); ?></strong>
				<span class="pk-sep">—</span>
				<span data-pk-field="title"><?php echo esc_html( $np['title'] ); ?></span>
			</p>
			<p class="pk-player-sub">
				<span data-pk-field="dj"><?php echo esc_html( $np['dj'] ); ?></span>
				<?php if ( $np['show'] ) : ?>
					<span class="pk-sep">—</span>
					<span data-pk-field="show"><?php echo esc_html( $np['show'] ); ?></span>
				<?php endif; ?>
			</p>
		</div>
		<div class="pk-vu pk-vu--player" aria-hidden="true" data-pk-vu>
			<span></span><span></span><span></span><span></span><span></span>
		</div>
		<div class="pk-player-controls">
			<?php if ( $multi ) : ?>
				<button class="pk-iconbtn" type="button" data-pk-stream-prev aria-label="<?php esc_attr_e( 'Vorige stream', 'piratenkrakers' ); ?>">‹</button>
			<?php endif; ?>
			<button class="pk-play pk-play--sm" type="button" data-pk-play aria-label="<?php esc_attr_e( 'Afspelen', 'piratenkrakers' ); ?>">
				<span class="pk-play-icon" data-pk-play-icon aria-hidden="true"></span>
			</button>
			<?php if ( $multi ) : ?>
				<button class="pk-iconbtn" type="button" data-pk-stream-next aria-label="<?php esc_attr_e( 'Volgende stream', 'piratenkrakers' ); ?>">›</button>
				<label class="pk-player-stream">
					<span class="screen-reader-text"><?php esc_html_e( 'Kies stream', 'piratenkrakers' ); ?></span>
					<select data-pk-stream>
						<?php foreach ( $streams as $id => $stream ) : ?>
							<option value="<?php echo esc_attr( $id ); ?>" <?php selected( $current, $id ); ?>><?php echo esc_html( $stream['name'] ); ?></option>
						<?php endforeach; ?>
					</select>
				</label>
			<?php endif; ?>
			<button class="pk-iconbtn" type="button" data-pk-mute aria-pressed="false" aria-label="<?php esc_attr_e( 'Dempen', 'piratenkrakers' ); ?>">
				<span class="pk-icon pk-icon-volume" data-pk-mute-icon aria-hidden="true"></span>
			</button>
			<label class="pk-volume">
				<span class="screen-reader-text"><?php esc_html_e( 'Volume', 'piratenkrakers' ); ?></span>
				<input type="range" min="0" max="1" step="0.01" value="0.8" data-pk-volume>
			</label>
			<p class="pk-player-listeners" title="<?php esc_attr_e( 'Luisteraars', 'piratenkrakers' ); ?>">
				<span data-pk-field="listeners"><?php echo esc_html( (string) $np['listeners'] ); ?></span>
			</p>
		</div>
	</div>
	<p class="pk-player-offline" data-pk-offline-msg hidden><?php esc_html_e( 'PiratenKrakers is momenteel offline', 'piratenkrakers' ); ?></p>
	<div class="pk-live-region screen-reader-text" data-pk-live-region aria-live="polite"></div>
</div>
