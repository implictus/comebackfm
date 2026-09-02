<?php
/**
 * Frontend and admin assets.
 *
 * @package PiratenKrakers
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Enqueue frontend assets.
 */
function pk_enqueue_assets(): void {
	$ver = PK_VERSION;

	wp_enqueue_style(
		'pk-main',
		pk_asset( 'css/main.css' ),
		array(),
		$ver
	);

	wp_enqueue_script(
		'pk-radio-engine',
		pk_asset( 'js/radio-engine.js' ),
		array(),
		$ver,
		true
	);

	wp_enqueue_script(
		'pk-player',
		pk_asset( 'js/player.js' ),
		array( 'pk-radio-engine' ),
		$ver,
		true
	);

	wp_enqueue_script(
		'pk-theme',
		pk_asset( 'js/theme.js' ),
		array( 'pk-player' ),
		$ver,
		true
	);

	$options = pk_get_options();
	$streams = array();
	foreach ( pk_get_enabled_streams() as $id => $stream ) {
		$streams[ $id ] = array(
			'id'     => $id,
			'name'   => $stream['name'] ?? $id,
			'url'    => $stream['stream_url'] ?? '',
			'format' => $stream['format'] ?? 'mp3',
		);
	}

	$config = array(
		'restNowPlaying'   => esc_url_raw( rest_url( 'pk/v1/now-playing' ) ),
		'restStreams'      => esc_url_raw( rest_url( 'pk/v1/streams' ) ),
		'restStatus'       => esc_url_raw( rest_url( 'pk/v1/status' ) ),
		'restNonce'        => wp_create_nonce( 'wp_rest' ),
		'requestNonce'     => wp_create_nonce( 'pk_request' ),
		'ajaxUrl'          => admin_url( 'admin-ajax.php' ),
		'homeUrl'          => home_url( '/' ),
		'stationName'      => (string) $options['station_name'],
		'tagline'          => (string) $options['tagline'],
		'defaultStream'    => pk_default_stream_id(),
		'updateInterval'   => max( 5, absint( $options['update_interval'] ) ) * 1000,
		'volume'           => min( 100, max( 0, absint( $options['player_volume'] ) ) ) / 100,
		'autoplay'         => ! empty( $options['player_autoplay'] ),
		'cacheBust'        => ! empty( $options['cache_bust_stream'] ),
		'fallbackArtwork'  => esc_url_raw( (string) $options['fallback_artwork'] ),
		'logo'             => esc_url_raw( (string) $options['logo'] ),
		'streams'          => $streams,
		'i18n'             => array(
			'live'            => __( 'LIVE', 'piratenkrakers' ),
			'offline'         => __( 'PiratenKrakers is momenteel offline', 'piratenkrakers' ),
			'nowPlaying'      => __( 'Nu op de radio', 'piratenkrakers' ),
			'play'            => __( 'Afspelen', 'piratenkrakers' ),
			'pause'           => __( 'Pauzeren', 'piratenkrakers' ),
			'mute'            => __( 'Dempen', 'piratenkrakers' ),
			'unmute'          => __( 'Geluid aan', 'piratenkrakers' ),
			'listeners'       => __( 'luisteraars', 'piratenkrakers' ),
			'unknownArtist'   => __( 'PiratenKrakers.nl', 'piratenkrakers' ),
			'unknownTitle'    => __( 'Live radio', 'piratenkrakers' ),
			'notConfigured'   => __( 'Stream nog niet ingesteld.', 'piratenkrakers' ),
		),
	);

	wp_add_inline_script(
		'pk-radio-engine',
		'window.PK_RADIO_CONFIG = ' . pk_json( $config ) . ';',
		'before'
	);
}
add_action( 'wp_enqueue_scripts', 'pk_enqueue_assets' );

/**
 * Preload critical assets.
 */
function pk_resource_hints(): void {
	$logo = pk_get_option( 'logo', pk_asset( 'img/logo-mark.png' ) );
	echo '<link rel="preload" as="image" href="' . esc_url( $logo ) . '">' . "\n";
	echo '<link rel="preload" as="font" type="font/woff2" href="' . esc_url( pk_asset( 'fonts/outfit-latin.woff2' ) ) . '" crossorigin>' . "\n";
	echo '<link rel="preload" as="font" type="font/woff2" href="' . esc_url( pk_asset( 'fonts/caveat-latin.woff2' ) ) . '" crossorigin>' . "\n";
}
add_action( 'wp_head', 'pk_resource_hints', 1 );

/**
 * Runtime CSS variables from branding options.
 */
function pk_runtime_css(): void {
	$accent = pk_sanitize_hex( pk_get_option( 'color_gold' ), '#3B9EFF' );
	$live   = pk_sanitize_hex( pk_get_option( 'color_live' ), '#3B9EFF' );
	$ink    = pk_sanitize_hex( pk_get_option( 'color_ink' ), '#070B16' );
	$cream  = pk_sanitize_hex( pk_get_option( 'color_cream' ), '#F5F7FB' );
	?>
	<style id="pk-runtime-brand">
		:root{
			--pk-blue: <?php echo esc_html( $accent ); ?>;
			--pk-live: <?php echo esc_html( $live ); ?>;
			--pk-ink: <?php echo esc_html( $ink ); ?>;
			--pk-cream: <?php echo esc_html( $cream ); ?>;
		}
	</style>
	<?php
}
add_action( 'wp_head', 'pk_runtime_css', 20 );

/**
 * Favicons.
 */
function pk_favicons(): void {
	$favicon = pk_get_option( 'favicon', pk_asset( 'img/favicon.svg' ) );
	$touch   = pk_asset( 'img/apple-touch-icon.png' );
	echo '<link rel="icon" href="' . esc_url( $favicon ) . '" type="image/svg+xml">' . "\n";
	echo '<link rel="icon" href="' . esc_url( pk_asset( 'img/favicon-32.png' ) ) . '" sizes="32x32">' . "\n";
	echo '<link rel="apple-touch-icon" href="' . esc_url( $touch ) . '">' . "\n";
	echo '<link rel="manifest" href="' . esc_url( home_url( '/?pk_manifest=1' ) ) . '">' . "\n";
	echo '<meta name="theme-color" content="#070B16">' . "\n";
	echo '<meta name="color-scheme" content="dark">' . "\n";
}
add_action( 'wp_head', 'pk_favicons', 2 );

/**
 * Lightweight web app manifest (PWA-ready stub).
 */
function pk_maybe_manifest(): void {
	if ( empty( $_GET['pk_manifest'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		return;
	}

	$options = pk_get_options();
	$manifest = array(
		'name'             => $options['station_name'],
		'short_name'       => 'PiratenKrakers',
		'description'      => $options['tagline'],
		'start_url'        => home_url( '/' ),
		'display'          => 'standalone',
		'background_color' => '#070B16',
		'theme_color'      => '#070B16',
		'lang'             => 'nl',
		'icons'            => array(
			array(
				'src'   => pk_asset( 'img/icon-192.png' ),
				'sizes' => '192x192',
				'type'  => 'image/png',
			),
			array(
				'src'   => pk_asset( 'img/icon-512.png' ),
				'sizes' => '512x512',
				'type'  => 'image/png',
			),
		),
	);

	nocache_headers();
	header( 'Content-Type: application/manifest+json; charset=utf-8' );
	echo wp_json_encode( $manifest );
	exit;
}
add_action( 'template_redirect', 'pk_maybe_manifest', 0 );
