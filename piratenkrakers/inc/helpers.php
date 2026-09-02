<?php
/**
 * Options, defaults and sanitization helpers.
 *
 * @package PiratenKrakers
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Default station options.
 */
function pk_default_options(): array {
	$fallback = PK_THEME_URI . '/assets/img/fallback-artwork.jpg';
	$logo     = PK_THEME_URI . '/assets/img/logo-mark.png';

	return array(
		'station_name'      => 'PiratenKrakers.nl',
		'tagline'           => 'Muziek uit het hart',
		'station_id'        => 'piratenkrakers',
		'default_stream'    => 'main',
		'update_interval'   => 12,
		'fallback_artwork'  => $fallback,
		'logo'              => $logo,
		'favicon'           => PK_THEME_URI . '/assets/img/favicon.svg',
		'og_image'          => PK_THEME_URI . '/assets/img/og-default.jpg',
		'player_volume'     => 80,
		'player_autoplay'   => false,
		'cache_bust_stream' => true,
		'sslverify'         => true,
		'color_gold'        => '#3B9EFF',
		'color_live'        => '#3B9EFF',
		'color_ink'         => '#070B16',
		'color_cream'       => '#F5F7FB',
		'social'            => array(
			'facebook'  => '',
			'instagram' => '',
			'tiktok'    => '',
			'youtube'   => '',
			'x'         => '',
		),
		'contact_email'     => '',
		'contact_phone'     => '',
		'contact_whatsapp'  => '',
		'contact_address'   => '',
		'streams'           => array(
			array(
				'id'                 => 'main',
				'name'               => 'PiratenKrakers Main',
				'enabled'            => true,
				/* VUL HIER JE ECHTE STREAM-URL IN (Icecast/Shoutcast/AzuraCast mount). */
				'stream_url'         => '',
				'format'             => 'mp3',
				'adapter'            => 'custom',
				/* VUL HIER JE ECHTE METADATA-ENDPOINT IN. */
				'metadata_url'       => '',
				'artwork_url'        => '',
				'azuracast_station'  => '',
				'mount'              => '',
				'sid'                => '1',
				'map_artist'         => 'artist',
				'map_title'          => 'title',
				'map_artwork'        => 'artwork',
				'map_dj'             => 'dj',
				'map_show'           => 'show',
				'map_listeners'      => 'listeners',
				'map_status'         => 'is_live',
				'listeners_url'      => '',
			),
			array(
				'id'                 => 'alt',
				'name'               => 'PiratenKrakers Alternatief',
				'enabled'            => false,
				'stream_url'         => '',
				'format'             => 'mp3',
				'adapter'            => 'custom',
				'metadata_url'       => '',
				'artwork_url'        => '',
				'azuracast_station'  => '',
				'mount'              => '',
				'sid'                => '1',
				'map_artist'         => 'artist',
				'map_title'          => 'title',
				'map_artwork'        => 'artwork',
				'map_dj'             => 'dj',
				'map_show'           => 'show',
				'map_listeners'      => 'listeners',
				'map_status'         => 'is_live',
				'listeners_url'      => '',
			),
		),
	);
}

/**
 * Whether a show is marked active (empty meta = active for backwards compatibility).
 */
function pk_is_show_active( int $post_id ): bool {
	$v = get_post_meta( $post_id, 'pk_active', true );
	return '' === $v || '1' === $v || 1 === $v || true === $v;
}

/**
 * Merged options.
 */
function pk_get_options(): array {
	$saved = get_option( PK_OPTION_KEY, array() );
	if ( ! is_array( $saved ) ) {
		$saved = array();
	}

	$old_tags = array( 'De ether is van ons', '24 uur per dag de beste piratenmuziek' );
	if ( in_array( $saved['tagline'] ?? '', $old_tags, true ) ) {
		$saved['tagline'] = 'Muziek uit het hart';
	}
	$old_accents = array( '#D4A84B', '#C81E2B' );
	if ( in_array( strtoupper( (string) ( $saved['color_gold'] ?? '' ) ), $old_accents, true ) ) {
		$saved['color_gold'] = '#3B9EFF';
		$saved['color_live']  = '#3B9EFF';
		$saved['color_ink']   = '#070B16';
		$saved['color_cream'] = '#F5F7FB';
	}

	$options = wp_parse_args( $saved, pk_default_options() );

	if ( empty( $options['streams'] ) || ! is_array( $options['streams'] ) ) {
		$options['streams'] = pk_default_options()['streams'];
	}

	if ( empty( $options['social'] ) || ! is_array( $options['social'] ) ) {
		$options['social'] = pk_default_options()['social'];
	}

	return $options;
}

/**
 * Single option with default fallback.
 *
 * @param string $key     Option key.
 * @param mixed  $default Default.
 * @return mixed
 */
function pk_get_option( string $key, $default = null ) {
	$options = pk_get_options();
	if ( array_key_exists( $key, $options ) ) {
		return $options[ $key ];
	}
	return $default;
}

/**
 * Persist options (sanitized elsewhere).
 */
function pk_update_options( array $options ): bool {
	return update_option( PK_OPTION_KEY, $options, false );
}

/**
 * Enabled streams.
 */
function pk_get_streams(): array {
	$streams = pk_get_option( 'streams', array() );
	$out     = array();
	foreach ( (array) $streams as $stream ) {
		if ( empty( $stream['id'] ) ) {
			continue;
		}
		$out[ $stream['id'] ] = $stream;
	}
	return $out;
}

/**
 * Enabled streams only.
 */
function pk_get_enabled_streams(): array {
	return array_filter(
		pk_get_streams(),
		static function ( $stream ) {
			return ! empty( $stream['enabled'] );
		}
	);
}

/**
 * Single stream config.
 */
function pk_get_stream( string $id ): ?array {
	$streams = pk_get_streams();
	return $streams[ $id ] ?? null;
}

/**
 * Default / first enabled stream id.
 */
function pk_default_stream_id(): string {
	$id      = (string) pk_get_option( 'default_stream', 'main' );
	$enabled = pk_get_enabled_streams();
	if ( isset( $enabled[ $id ] ) ) {
		return $id;
	}
	if ( $enabled ) {
		return (string) array_key_first( $enabled );
	}
	return 'main';
}

/**
 * Read a nested array value via dot-path.
 *
 * @param mixed  $data Data.
 * @param string $path Dot path.
 * @return mixed|null
 */
function pk_dot_get( $data, string $path ) {
	if ( '' === $path ) {
		return null;
	}
	$parts = explode( '.', $path );
	$cur   = $data;
	foreach ( $parts as $part ) {
		if ( is_object( $cur ) ) {
			$cur = (array) $cur;
		}
		if ( ! is_array( $cur ) || ! array_key_exists( $part, $cur ) ) {
			return null;
		}
		$cur = $cur[ $part ];
	}
	return $cur;
}

/**
 * Allowed http(s) URL or empty.
 */
function pk_sanitize_url( $value ): string {
	$value = trim( (string) $value );
	if ( '' === $value ) {
		return '';
	}
	$url = esc_url_raw( $value );
	if ( ! $url ) {
		return '';
	}
	$scheme = wp_parse_url( $url, PHP_URL_SCHEME );
	if ( ! in_array( $scheme, array( 'http', 'https' ), true ) ) {
		return '';
	}
	return $url;
}

/**
 * Hex color or empty.
 */
function pk_sanitize_hex( $value, string $fallback = '' ): string {
	$color = sanitize_hex_color( (string) $value );
	return $color ? $color : $fallback;
}

/**
 * Weekday labels (1 = Monday).
 */
function pk_weekdays(): array {
	return array(
		1 => __( 'Maandag', 'piratenkrakers' ),
		2 => __( 'Dinsdag', 'piratenkrakers' ),
		3 => __( 'Woensdag', 'piratenkrakers' ),
		4 => __( 'Donderdag', 'piratenkrakers' ),
		5 => __( 'Vrijdag', 'piratenkrakers' ),
		6 => __( 'Zaterdag', 'piratenkrakers' ),
		7 => __( 'Zondag', 'piratenkrakers' ),
	);
}

/**
 * Current weekday 1-7 in site timezone.
 */
function pk_current_weekday(): int {
	$ts = current_time( 'timestamp' );
	$n  = (int) wp_date( 'N', $ts );
	return $n >= 1 && $n <= 7 ? $n : 1;
}

/**
 * Minutes from HH:MM.
 */
function pk_time_to_minutes( string $time ): int {
	$parts = explode( ':', $time );
	$h     = isset( $parts[0] ) ? absint( $parts[0] ) : 0;
	$m     = isset( $parts[1] ) ? absint( $parts[1] ) : 0;
	return ( $h * 60 ) + $m;
}

/**
 * Whether a show is currently on air, including overnight wrap.
 */
function pk_is_on_air( string $start, string $end, ?int $now = null ): bool {
	$now   = null === $now ? pk_time_to_minutes( wp_date( 'H:i' ) ) : $now;
	$start = pk_time_to_minutes( $start );
	$end   = pk_time_to_minutes( $end );

	if ( $start === $end ) {
		return true;
	}
	if ( $end > $start ) {
		return $now >= $start && $now < $end;
	}
	// Overnight, e.g. 22:00–02:00.
	return $now >= $start || $now < $end;
}

/**
 * Theme image URI helper.
 */
function pk_asset( string $relative ): string {
	return PK_THEME_URI . '/assets/' . ltrim( $relative, '/' );
}

/**
 * JSON encode for inline script.
 */
function pk_json( $data ): string {
	return wp_json_encode( $data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP );
}
