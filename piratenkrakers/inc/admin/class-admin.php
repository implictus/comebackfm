<?php
/**
 * PiratenKrakers admin: Radio, Branding, CPT shortcuts.
 *
 * @package PiratenKrakers
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class PK_Admin {

	public static function init(): void {
		add_action( 'admin_menu', array( __CLASS__, 'menu' ) );
		add_action( 'admin_init', array( __CLASS__, 'register' ) );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'assets' ) );
		add_action( 'admin_notices', array( __CLASS__, 'notices' ) );
		add_filter( 'manage_pk_request_posts_columns', array( __CLASS__, 'request_columns' ) );
		add_action( 'manage_pk_request_posts_custom_column', array( __CLASS__, 'request_column' ), 10, 2 );
		add_filter( 'manage_pk_show_posts_columns', array( __CLASS__, 'show_columns' ) );
		add_action( 'manage_pk_show_posts_custom_column', array( __CLASS__, 'show_column' ), 10, 2 );
	}

	public static function menu(): void {
		add_menu_page(
			'PiratenKrakers',
			'PiratenKrakers',
			'manage_options',
			'piratenkrakers',
			array( __CLASS__, 'page_radio' ),
			'dashicons-format-audio',
			3
		);

		add_submenu_page( 'piratenkrakers', __( 'Radio', 'piratenkrakers' ), __( 'Radio', 'piratenkrakers' ), 'manage_options', 'piratenkrakers', array( __CLASS__, 'page_radio' ) );
		add_submenu_page( 'piratenkrakers', __( "DJ's", 'piratenkrakers' ), __( "DJ's", 'piratenkrakers' ), 'edit_posts', 'edit.php?post_type=pk_dj' );
		add_submenu_page( 'piratenkrakers', __( "Programma's", 'piratenkrakers' ), __( "Programma's", 'piratenkrakers' ), 'edit_posts', 'edit.php?post_type=pk_show' );
		add_submenu_page( 'piratenkrakers', __( 'Verzoekjes', 'piratenkrakers' ), __( 'Verzoekjes', 'piratenkrakers' ), 'edit_posts', 'edit.php?post_type=pk_request' );
		add_submenu_page( 'piratenkrakers', __( 'Nieuws', 'piratenkrakers' ), __( 'Nieuws', 'piratenkrakers' ), 'edit_posts', 'edit.php' );
		add_submenu_page( 'piratenkrakers', __( 'Instellingen', 'piratenkrakers' ), __( 'Instellingen', 'piratenkrakers' ), 'manage_options', 'pk-branding', array( __CLASS__, 'page_branding' ) );
	}

	public static function register(): void {
		register_setting(
			'pk_radio',
			PK_OPTION_KEY,
			array(
				'type'              => 'array',
				'sanitize_callback' => array( __CLASS__, 'sanitize' ),
				'default'           => pk_default_options(),
			)
		);
	}

	public static function assets( string $hook ): void {
		if ( ! str_contains( $hook, 'piratenkrakers' ) && ! str_contains( $hook, 'pk-branding' ) ) {
			return;
		}
		wp_enqueue_media();
		wp_enqueue_style( 'wp-color-picker' );
		wp_enqueue_script( 'wp-color-picker' );
		wp_enqueue_style( 'pk-admin', pk_asset( 'css/admin.css' ), array(), PK_VERSION );
		wp_enqueue_script( 'pk-admin', pk_asset( 'js/admin.js' ), array( 'jquery', 'wp-color-picker' ), PK_VERSION, true );
	}

	public static function notices(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		$stream = pk_get_stream( pk_default_stream_id() );
		if ( $stream && empty( $stream['stream_url'] ) ) {
			$screen = get_current_screen();
			if ( $screen && str_contains( (string) $screen->id, 'piratenkrakers' ) ) {
				echo '<div class="notice notice-warning"><p><strong>PiratenKrakers:</strong> ' . esc_html__( 'Vul je echte stream-URL in onder Radio → Stream 1. Zonder die URL speelt de player niet.', 'piratenkrakers' ) . '</p></div>';
			}
		}
	}

	public static function sanitize( $input ): array {
		$defaults = pk_default_options();
		$current  = pk_get_options();
		$input    = is_array( $input ) ? $input : array();
		$out      = wp_parse_args( $input, $current );
		$out      = wp_parse_args( $out, $defaults );

		$out['station_name']      = sanitize_text_field( $out['station_name'] ?? $defaults['station_name'] );
		$out['tagline']           = sanitize_text_field( $out['tagline'] ?? $defaults['tagline'] );
		$out['station_id']        = sanitize_key( $out['station_id'] ?? 'piratenkrakers' );
		$out['default_stream']    = sanitize_key( $out['default_stream'] ?? 'main' );
		$out['update_interval']   = min( 120, max( 5, absint( $out['update_interval'] ?? 12 ) ) );
		$out['player_volume']     = min( 100, max( 0, absint( $out['player_volume'] ?? 80 ) ) );
		$out['player_autoplay']   = ! empty( $out['player_autoplay'] );
		$out['cache_bust_stream'] = ! empty( $out['cache_bust_stream'] );
		$out['sslverify']         = ! empty( $out['sslverify'] );
		$out['fallback_artwork']  = pk_sanitize_url( $out['fallback_artwork'] ?? '' ) ?: $defaults['fallback_artwork'];
		$out['logo']              = pk_sanitize_url( $out['logo'] ?? '' ) ?: $defaults['logo'];
		$out['favicon']           = pk_sanitize_url( $out['favicon'] ?? '' ) ?: $defaults['favicon'];
		$out['og_image']          = pk_sanitize_url( $out['og_image'] ?? '' ) ?: $defaults['og_image'];
		$out['color_gold']        = pk_sanitize_hex( $out['color_gold'] ?? '', $defaults['color_gold'] );
		$out['color_live']        = pk_sanitize_hex( $out['color_live'] ?? '', $defaults['color_live'] );
		$out['color_ink']         = pk_sanitize_hex( $out['color_ink'] ?? '', $defaults['color_ink'] );
		$out['color_cream']       = pk_sanitize_hex( $out['color_cream'] ?? '', $defaults['color_cream'] );
		$out['contact_email']     = sanitize_email( $out['contact_email'] ?? '' );
		$out['contact_phone']     = sanitize_text_field( $out['contact_phone'] ?? '' );
		$out['contact_whatsapp']  = sanitize_text_field( $out['contact_whatsapp'] ?? '' );
		$out['contact_address']   = sanitize_textarea_field( $out['contact_address'] ?? '' );

		$social = is_array( $out['social'] ?? null ) ? $out['social'] : array();
		foreach ( array( 'facebook', 'instagram', 'tiktok', 'youtube', 'x' ) as $net ) {
			$social[ $net ] = pk_sanitize_url( $social[ $net ] ?? '' );
		}
		$out['social'] = $social;

		$streams_in = is_array( $out['streams'] ?? null ) ? $out['streams'] : array();
		$streams    = array();
		foreach ( $streams_in as $stream ) {
			if ( ! is_array( $stream ) ) {
				continue;
			}
			$id = sanitize_key( $stream['id'] ?? '' );
			if ( '' === $id ) {
				continue;
			}
			$adapter = sanitize_key( $stream['adapter'] ?? 'custom' );
			if ( ! in_array( $adapter, array( 'custom', 'azuracast', 'icecast', 'shoutcast', 'sam' ), true ) ) {
				$adapter = 'custom';
			}
			$format = sanitize_key( $stream['format'] ?? 'mp3' );
			if ( ! in_array( $format, array( 'mp3', 'aac', 'ogg', 'opus' ), true ) ) {
				$format = 'mp3';
			}
			$streams[] = array(
				'id'                => $id,
				'name'              => sanitize_text_field( $stream['name'] ?? $id ),
				'enabled'           => ! empty( $stream['enabled'] ),
				'stream_url'        => pk_sanitize_url( $stream['stream_url'] ?? '' ),
				'format'            => $format,
				'adapter'           => $adapter,
				'metadata_url'      => pk_sanitize_url( $stream['metadata_url'] ?? '' ),
				'artwork_url'       => pk_sanitize_url( $stream['artwork_url'] ?? '' ),
				'azuracast_station' => sanitize_text_field( $stream['azuracast_station'] ?? '' ),
				'mount'             => sanitize_text_field( $stream['mount'] ?? '' ),
				'sid'               => sanitize_text_field( $stream['sid'] ?? '1' ),
				'map_artist'        => sanitize_text_field( $stream['map_artist'] ?? 'artist' ),
				'map_title'         => sanitize_text_field( $stream['map_title'] ?? 'title' ),
				'map_artwork'       => sanitize_text_field( $stream['map_artwork'] ?? 'artwork' ),
				'map_dj'            => sanitize_text_field( $stream['map_dj'] ?? 'dj' ),
				'map_show'          => sanitize_text_field( $stream['map_show'] ?? 'show' ),
				'map_listeners'     => sanitize_text_field( $stream['map_listeners'] ?? 'listeners' ),
				'map_status'        => sanitize_text_field( $stream['map_status'] ?? 'is_live' ),
				'listeners_url'     => pk_sanitize_url( $stream['listeners_url'] ?? '' ),
			);
		}
		if ( ! $streams ) {
			$streams = $defaults['streams'];
		}
		$out['streams'] = $streams;

		delete_transient( 'pk_np_' . md5( pk_default_stream_id() ) );
		foreach ( $streams as $stream ) {
			delete_transient( 'pk_np_' . md5( $stream['id'] ) );
		}

		return $out;
	}

	public static function page_radio(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		$options = pk_get_options();
		$streams = $options['streams'];
		?>
		<div class="wrap pk-admin">
			<h1>PiratenKrakers — Radio</h1>
			<p class="pk-admin-lead">Koppel hier je echte stream en metadata. Lege URL = demomodus: programmagids + fallback-artwork, geen offline-fout. Vul je Icecast, SHOUTcast, AzuraCast, SAM Broadcaster of eigen JSON in zonder het thema te wijzigen.</p>
			<form method="post" action="options.php" class="pk-admin-form">
				<?php settings_fields( 'pk_radio' ); ?>
				<table class="form-table" role="presentation">
					<tr>
						<th>Stationsnaam</th>
						<td><input class="regular-text" name="<?php echo esc_attr( PK_OPTION_KEY ); ?>[station_name]" value="<?php echo esc_attr( $options['station_name'] ); ?>"></td>
					</tr>
					<tr>
						<th>Slogan</th>
						<td><input class="regular-text" name="<?php echo esc_attr( PK_OPTION_KEY ); ?>[tagline]" value="<?php echo esc_attr( $options['tagline'] ); ?>"></td>
					</tr>
					<tr>
						<th>Station ID</th>
						<td><input class="regular-text" name="<?php echo esc_attr( PK_OPTION_KEY ); ?>[station_id]" value="<?php echo esc_attr( $options['station_id'] ); ?>">
						<p class="description">Interne sleutel, bijv. voor AzuraCast shortcode.</p></td>
					</tr>
					<tr>
						<th>Update-interval (sec)</th>
						<td><input type="number" min="5" max="120" name="<?php echo esc_attr( PK_OPTION_KEY ); ?>[update_interval]" value="<?php echo esc_attr( (string) $options['update_interval'] ); ?>"></td>
					</tr>
					<tr>
						<th>Standaardvolume</th>
						<td><input type="number" min="0" max="100" name="<?php echo esc_attr( PK_OPTION_KEY ); ?>[player_volume]" value="<?php echo esc_attr( (string) $options['player_volume'] ); ?>"></td>
					</tr>
					<tr>
						<th>Player</th>
						<td>
							<label><input type="checkbox" name="<?php echo esc_attr( PK_OPTION_KEY ); ?>[player_autoplay]" value="1" <?php checked( ! empty( $options['player_autoplay'] ) ); ?>> Autoplay proberen (browsers blokkeren dit meestal)</label><br>
							<label><input type="checkbox" name="<?php echo esc_attr( PK_OPTION_KEY ); ?>[cache_bust_stream]" value="1" <?php checked( ! empty( $options['cache_bust_stream'] ) ); ?>> Cache-buster op stream-URL bij play (springt naar live-edge)</label><br>
							<label><input type="checkbox" name="<?php echo esc_attr( PK_OPTION_KEY ); ?>[sslverify]" value="1" <?php checked( ! empty( $options['sslverify'] ) ); ?>> SSL verifiëren bij metadata-requests</label>
						</td>
					</tr>
					<tr>
						<th>Fallback artwork</th>
						<td>
							<input class="regular-text pk-media-url" name="<?php echo esc_attr( PK_OPTION_KEY ); ?>[fallback_artwork]" value="<?php echo esc_attr( $options['fallback_artwork'] ); ?>">
							<button type="button" class="button pk-media-pick">Kies afbeelding</button>
						</td>
					</tr>
					<tr>
						<th>Standaardstream</th>
						<td>
							<select name="<?php echo esc_attr( PK_OPTION_KEY ); ?>[default_stream]">
								<?php foreach ( $streams as $stream ) : ?>
									<option value="<?php echo esc_attr( $stream['id'] ); ?>" <?php selected( $options['default_stream'], $stream['id'] ); ?>><?php echo esc_html( $stream['name'] ); ?></option>
								<?php endforeach; ?>
							</select>
						</td>
					</tr>
				</table>

				<?php foreach ( $streams as $i => $stream ) : ?>
					<div class="pk-admin-card">
						<h2>Stream <?php echo esc_html( (string) ( $i + 1 ) ); ?> — <?php echo esc_html( $stream['name'] ); ?></h2>
						<p class="description">Vul hier je <strong>echte</strong> Icecast/Shoutcast/AzuraCast-URL’s in. Lege URL = deze stream speelt niet.</p>
						<input type="hidden" name="<?php echo esc_attr( PK_OPTION_KEY ); ?>[streams][<?php echo esc_attr( (string) $i ); ?>][id]" value="<?php echo esc_attr( $stream['id'] ); ?>">
						<table class="form-table" role="presentation">
							<tr>
								<th>Naam</th>
								<td><input class="regular-text" name="<?php echo esc_attr( PK_OPTION_KEY ); ?>[streams][<?php echo esc_attr( (string) $i ); ?>][name]" value="<?php echo esc_attr( $stream['name'] ); ?>"></td>
							</tr>
							<tr>
								<th>Actief</th>
								<td><label><input type="checkbox" name="<?php echo esc_attr( PK_OPTION_KEY ); ?>[streams][<?php echo esc_attr( (string) $i ); ?>][enabled]" value="1" <?php checked( ! empty( $stream['enabled'] ) ); ?>> Deze stream tonen in de player</label></td>
							</tr>
							<tr>
								<th>Stream-URL</th>
								<td>
									<input class="large-text code" name="<?php echo esc_attr( PK_OPTION_KEY ); ?>[streams][<?php echo esc_attr( (string) $i ); ?>][stream_url]" value="<?php echo esc_attr( $stream['stream_url'] ); ?>" placeholder="https://stream.piratenkrakers.nl/listen/piratenkrakers/radio.mp3">
									<p class="description">Directe audio-URL (mp3/aac), niet de luisterpagina.</p>
								</td>
							</tr>
							<tr>
								<th>Formaat</th>
								<td>
									<select name="<?php echo esc_attr( PK_OPTION_KEY ); ?>[streams][<?php echo esc_attr( (string) $i ); ?>][format]">
										<?php foreach ( array( 'mp3' => 'MP3', 'aac' => 'AAC', 'ogg' => 'Ogg', 'opus' => 'Opus' ) as $val => $label ) : ?>
											<option value="<?php echo esc_attr( $val ); ?>" <?php selected( $stream['format'], $val ); ?>><?php echo esc_html( $label ); ?></option>
										<?php endforeach; ?>
									</select>
								</td>
							</tr>
							<tr>
								<th>Metadata-adapter</th>
								<td>
									<select name="<?php echo esc_attr( PK_OPTION_KEY ); ?>[streams][<?php echo esc_attr( (string) $i ); ?>][adapter]">
										<option value="custom" <?php selected( $stream['adapter'], 'custom' ); ?>>Custom JSON (dot-paths)</option>
										<option value="azuracast" <?php selected( $stream['adapter'], 'azuracast' ); ?>>AzuraCast</option>
										<option value="icecast" <?php selected( $stream['adapter'], 'icecast' ); ?>>Icecast status-json.xsl</option>
										<option value="shoutcast" <?php selected( $stream['adapter'], 'shoutcast' ); ?>>SHOUTcast v2 JSON</option>
										<option value="sam" <?php selected( $stream['adapter'], 'sam' ); ?>>SAM Broadcaster / XML-JSON</option>
									</select>
								</td>
							</tr>
							<tr>
								<th>Metadata-URL</th>
								<td>
									<input class="large-text code" name="<?php echo esc_attr( PK_OPTION_KEY ); ?>[streams][<?php echo esc_attr( (string) $i ); ?>][metadata_url]" value="<?php echo esc_attr( $stream['metadata_url'] ); ?>" placeholder="https://radio.example.com/api/nowplaying/piratenkrakers">
									<p class="description">AzuraCast: <code>/api/nowplaying/station</code> · Icecast: <code>/status-json.xsl</code> · SHOUTcast: <code>/stats?sid=1&amp;json=1</code> · Custom: eigen JSON.</p>
								</td>
							</tr>
							<tr>
								<th>Artwork-URL (optioneel)</th>
								<td><input class="large-text code" name="<?php echo esc_attr( PK_OPTION_KEY ); ?>[streams][<?php echo esc_attr( (string) $i ); ?>][artwork_url]" value="<?php echo esc_attr( $stream['artwork_url'] ); ?>">
								<p class="description">JSON <code>{url|artwork|cover}</code> of directe afbeeldings-URL.</p></td>
							</tr>
							<tr>
								<th>Luisteraars-URL (optioneel)</th>
								<td><input class="large-text code" name="<?php echo esc_attr( PK_OPTION_KEY ); ?>[streams][<?php echo esc_attr( (string) $i ); ?>][listeners_url]" value="<?php echo esc_attr( $stream['listeners_url'] ?? '' ); ?>">
								<p class="description">JSON <code>{listeners|current|count}</code> of een kaal getal. Laat leeg als metadata dit al bevat.</p></td>
							</tr>
							<tr>
								<th>AzuraCast station</th>
								<td><input class="regular-text" name="<?php echo esc_attr( PK_OPTION_KEY ); ?>[streams][<?php echo esc_attr( (string) $i ); ?>][azuracast_station]" value="<?php echo esc_attr( $stream['azuracast_station'] ); ?>"></td>
							</tr>
							<tr>
								<th>Icecast mount</th>
								<td><input class="regular-text" name="<?php echo esc_attr( PK_OPTION_KEY ); ?>[streams][<?php echo esc_attr( (string) $i ); ?>][mount]" value="<?php echo esc_attr( $stream['mount'] ); ?>" placeholder="/radio.mp3"></td>
							</tr>
							<tr>
								<th>SHOUTcast SID</th>
								<td><input class="small-text" name="<?php echo esc_attr( PK_OPTION_KEY ); ?>[streams][<?php echo esc_attr( (string) $i ); ?>][sid]" value="<?php echo esc_attr( $stream['sid'] ); ?>"></td>
							</tr>
							<tr>
								<th>JSON-mapping (custom)</th>
								<td>
									<div class="pk-map-grid">
										<label>artist <input name="<?php echo esc_attr( PK_OPTION_KEY ); ?>[streams][<?php echo esc_attr( (string) $i ); ?>][map_artist]" value="<?php echo esc_attr( $stream['map_artist'] ); ?>"></label>
										<label>title <input name="<?php echo esc_attr( PK_OPTION_KEY ); ?>[streams][<?php echo esc_attr( (string) $i ); ?>][map_title]" value="<?php echo esc_attr( $stream['map_title'] ); ?>"></label>
										<label>artwork <input name="<?php echo esc_attr( PK_OPTION_KEY ); ?>[streams][<?php echo esc_attr( (string) $i ); ?>][map_artwork]" value="<?php echo esc_attr( $stream['map_artwork'] ); ?>"></label>
										<label>dj <input name="<?php echo esc_attr( PK_OPTION_KEY ); ?>[streams][<?php echo esc_attr( (string) $i ); ?>][map_dj]" value="<?php echo esc_attr( $stream['map_dj'] ); ?>"></label>
										<label>show <input name="<?php echo esc_attr( PK_OPTION_KEY ); ?>[streams][<?php echo esc_attr( (string) $i ); ?>][map_show]" value="<?php echo esc_attr( $stream['map_show'] ); ?>"></label>
										<label>listeners <input name="<?php echo esc_attr( PK_OPTION_KEY ); ?>[streams][<?php echo esc_attr( (string) $i ); ?>][map_listeners]" value="<?php echo esc_attr( $stream['map_listeners'] ); ?>"></label>
										<label>status <input name="<?php echo esc_attr( PK_OPTION_KEY ); ?>[streams][<?php echo esc_attr( (string) $i ); ?>][map_status]" value="<?php echo esc_attr( $stream['map_status'] ); ?>"></label>
									</div>
									<p class="description">Dot-paths, bijv. <code>now_playing.song.artist</code>.</p>
								</td>
							</tr>
						</table>
					</div>
				<?php endforeach; ?>

				<?php
				// Preserve branding fields when saving radio page.
				foreach ( array( 'logo', 'favicon', 'og_image', 'color_gold', 'color_live', 'color_ink', 'color_cream', 'contact_email', 'contact_phone', 'contact_whatsapp', 'contact_address' ) as $hidden ) {
					echo '<input type="hidden" name="' . esc_attr( PK_OPTION_KEY ) . '[' . esc_attr( $hidden ) . ']" value="' . esc_attr( (string) $options[ $hidden ] ) . '">';
				}
				foreach ( $options['social'] as $net => $url ) {
					echo '<input type="hidden" name="' . esc_attr( PK_OPTION_KEY ) . '[social][' . esc_attr( $net ) . ']" value="' . esc_attr( $url ) . '">';
				}
				submit_button( __( 'Radio-instellingen opslaan', 'piratenkrakers' ) );
				?>
			</form>
			<div class="pk-admin-card">
				<h2>Frontend API</h2>
				<p>De player praat alleen met WordPress, nooit rechtstreeks met je streamserver.</p>
				<ul>
					<li><code><?php echo esc_html( rest_url( 'pk/v1/now-playing' ) ); ?></code></li>
					<li><code><?php echo esc_html( rest_url( 'pk/v1/streams' ) ); ?></code></li>
					<li><code><?php echo esc_html( rest_url( 'pk/v1/status' ) ); ?></code></li>
					<li><code><?php echo esc_html( rest_url( 'pk/v1/schedule' ) ); ?></code></li>
					<li><code><?php echo esc_html( rest_url( 'pk/v1/djs' ) ); ?></code></li>
				</ul>
			</div>
		</div>
		<?php
	}

	public static function page_branding(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		$options = pk_get_options();
		?>
		<div class="wrap pk-admin">
			<h1>PiratenKrakers — Instellingen</h1>
			<form method="post" action="options.php" class="pk-admin-form">
				<?php settings_fields( 'pk_radio' ); ?>
				<table class="form-table">
					<tr>
						<th>Logo-URL</th>
						<td>
							<input class="regular-text pk-media-url" name="<?php echo esc_attr( PK_OPTION_KEY ); ?>[logo]" value="<?php echo esc_attr( $options['logo'] ); ?>">
							<button type="button" class="button pk-media-pick">Kies logo</button>
						</td>
					</tr>
					<tr>
						<th>Favicon-URL</th>
						<td>
							<input class="regular-text pk-media-url" name="<?php echo esc_attr( PK_OPTION_KEY ); ?>[favicon]" value="<?php echo esc_attr( $options['favicon'] ); ?>">
							<button type="button" class="button pk-media-pick">Kies favicon</button>
						</td>
					</tr>
					<tr>
						<th>Open Graph-afbeelding</th>
						<td>
							<input class="regular-text pk-media-url" name="<?php echo esc_attr( PK_OPTION_KEY ); ?>[og_image]" value="<?php echo esc_attr( $options['og_image'] ); ?>">
							<button type="button" class="button pk-media-pick">Kies afbeelding</button>
						</td>
					</tr>
					<tr>
						<th>Kleuren</th>
						<td>
							<label>Blauw / accent <input class="pk-color" name="<?php echo esc_attr( PK_OPTION_KEY ); ?>[color_gold]" value="<?php echo esc_attr( $options['color_gold'] ); ?>"></label>
							<label>LIVE <input class="pk-color" name="<?php echo esc_attr( PK_OPTION_KEY ); ?>[color_live]" value="<?php echo esc_attr( $options['color_live'] ); ?>"></label>
							<label>Achtergrond <input class="pk-color" name="<?php echo esc_attr( PK_OPTION_KEY ); ?>[color_ink]" value="<?php echo esc_attr( $options['color_ink'] ); ?>"></label>
							<label>Tekst <input class="pk-color" name="<?php echo esc_attr( PK_OPTION_KEY ); ?>[color_cream]" value="<?php echo esc_attr( $options['color_cream'] ); ?>"></label>
						</td>
					</tr>
					<tr>
						<th>Social media</th>
						<td>
							<?php foreach ( array( 'facebook' => 'Facebook', 'instagram' => 'Instagram', 'tiktok' => 'TikTok', 'youtube' => 'YouTube', 'x' => 'X / Twitter' ) as $key => $label ) : ?>
								<p><label><?php echo esc_html( $label ); ?><br>
								<input class="regular-text" name="<?php echo esc_attr( PK_OPTION_KEY ); ?>[social][<?php echo esc_attr( $key ); ?>]" value="<?php echo esc_attr( $options['social'][ $key ] ?? '' ); ?>" placeholder="https://"></label></p>
							<?php endforeach; ?>
						</td>
					</tr>
					<tr>
						<th>Contact</th>
						<td>
							<p><label>E-mail<br><input class="regular-text" name="<?php echo esc_attr( PK_OPTION_KEY ); ?>[contact_email]" value="<?php echo esc_attr( $options['contact_email'] ); ?>"></label></p>
							<p><label>Telefoon<br><input class="regular-text" name="<?php echo esc_attr( PK_OPTION_KEY ); ?>[contact_phone]" value="<?php echo esc_attr( $options['contact_phone'] ); ?>"></label></p>
							<p><label>WhatsApp<br><input class="regular-text" name="<?php echo esc_attr( PK_OPTION_KEY ); ?>[contact_whatsapp]" value="<?php echo esc_attr( $options['contact_whatsapp'] ); ?>"></label></p>
							<p><label>Adres<br><textarea class="large-text" rows="3" name="<?php echo esc_attr( PK_OPTION_KEY ); ?>[contact_address]"><?php echo esc_textarea( $options['contact_address'] ); ?></textarea></label></p>
						</td>
					</tr>
				</table>
				<?php
				foreach ( array( 'station_name', 'tagline', 'station_id', 'default_stream', 'update_interval', 'player_volume', 'fallback_artwork' ) as $hidden ) {
					echo '<input type="hidden" name="' . esc_attr( PK_OPTION_KEY ) . '[' . esc_attr( $hidden ) . ']" value="' . esc_attr( (string) $options[ $hidden ] ) . '">';
				}
				echo '<input type="hidden" name="' . esc_attr( PK_OPTION_KEY ) . '[player_autoplay]" value="' . ( ! empty( $options['player_autoplay'] ) ? '1' : '' ) . '">';
				echo '<input type="hidden" name="' . esc_attr( PK_OPTION_KEY ) . '[cache_bust_stream]" value="' . ( ! empty( $options['cache_bust_stream'] ) ? '1' : '' ) . '">';
				echo '<input type="hidden" name="' . esc_attr( PK_OPTION_KEY ) . '[sslverify]" value="' . ( ! empty( $options['sslverify'] ) ? '1' : '' ) . '">';
				foreach ( $options['streams'] as $i => $stream ) {
					foreach ( $stream as $k => $v ) {
						if ( is_array( $v ) ) {
							continue;
						}
						if ( 'enabled' === $k ) {
							if ( $v ) {
								echo '<input type="hidden" name="' . esc_attr( PK_OPTION_KEY ) . '[streams][' . esc_attr( (string) $i ) . '][enabled]" value="1">';
							}
							continue;
						}
						echo '<input type="hidden" name="' . esc_attr( PK_OPTION_KEY ) . '[streams][' . esc_attr( (string) $i ) . '][' . esc_attr( $k ) . ']" value="' . esc_attr( (string) $v ) . '">';
					}
				}
				submit_button( __( 'Branding opslaan', 'piratenkrakers' ) );
				?>
			</form>
		</div>
		<?php
	}

	public static function request_columns( array $cols ): array {
		$cols['pk_song']   = 'Nummer';
		$cols['pk_place']  = 'Plaats';
		$cols['pk_status'] = 'Status';
		return $cols;
	}

	public static function request_column( string $col, int $post_id ): void {
		if ( 'pk_song' === $col ) {
			echo esc_html( (string) get_post_meta( $post_id, 'pk_song', true ) );
		}
		if ( 'pk_place' === $col ) {
			echo esc_html( (string) get_post_meta( $post_id, 'pk_place', true ) );
		}
		if ( 'pk_status' === $col ) {
			echo esc_html( (string) get_post_meta( $post_id, 'pk_status', true ) );
		}
	}

	public static function show_columns( array $cols ): array {
		$cols['pk_when'] = 'Tijd';
		$cols['pk_dj']   = 'DJ';
		return $cols;
	}

	public static function show_column( string $col, int $post_id ): void {
		if ( 'pk_when' === $col ) {
			$start = get_post_meta( $post_id, 'pk_start', true );
			$end   = get_post_meta( $post_id, 'pk_end', true );
			$days  = (array) get_post_meta( $post_id, 'pk_weekdays', true );
			$labels = pk_weekdays();
			$dnames = array();
			foreach ( $days as $d ) {
				if ( isset( $labels[ (int) $d ] ) ) {
					$dnames[] = $labels[ (int) $d ];
				}
			}
			echo esc_html( implode( ', ', $dnames ) . ' ' . $start . '–' . $end );
		}
		if ( 'pk_dj' === $col ) {
			$dj = absint( get_post_meta( $post_id, 'pk_dj_id', true ) );
			echo $dj ? esc_html( get_the_title( $dj ) ) : '—';
		}
	}
}

PK_Admin::init();
