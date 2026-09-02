<?php
/**
 * PiratenKrakers radio engine.
 *
 * Server-side source of truth for now-playing data. The theme UI consumes
 * the normalized payload via REST (`/wp-json/pk/v1/...`) and never talks
 * to Icecast/AzuraCast/Shoutcast directly.
 *
 * Replace this class or an adapter to swap the radio backend without
 * rebuilding templates.
 *
 * @package PiratenKrakers
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class PK_Radio_Engine {

	public const REST_NAMESPACE = 'pk/v1';

	/**
	 * Adapter map.
	 *
	 * @return array<string,class-string<PK_Metadata_Adapter>>
	 */
	public static function adapters(): array {
		$adapters = array(
			'custom'    => PK_Adapter_Custom::class,
			'azuracast' => PK_Adapter_AzuraCast::class,
			'icecast'   => PK_Adapter_Icecast::class,
			'shoutcast' => PK_Adapter_Shoutcast::class,
			'sam'       => PK_Adapter_SAM::class,
		);

		/**
		 * Filter available metadata adapters.
		 *
		 * @param array<string,class-string<PK_Metadata_Adapter>> $adapters Adapters.
		 */
		return apply_filters( 'pk_radio_adapters', $adapters );
	}

	/**
	 * Empty / fallback now-playing payload.
	 */
	public static function empty_payload( string $stream_id = 'main', bool $offline = false ): array {
		$options = pk_get_options();
		$stream  = pk_get_stream( $stream_id );
		$from_guide  = self::current_from_guide();
		$configured  = ! empty( $stream['stream_url'] );
		$demo        = ! $configured;
		$offline_msg = __( 'PiratenKrakers is momenteel offline', 'piratenkrakers' );

		return array(
			'station'     => (string) $options['station_name'],
			'stream_id'   => $stream_id,
			'stream_name' => $stream['name'] ?? $options['station_name'],
			'is_live'     => ! $offline,
			'offline'     => $offline,
			'demo'        => $demo,
			'status'      => $offline ? 'offline' : ( $demo ? 'demo' : 'unknown' ),
			'configured'  => $configured,
			'artist'      => (string) $options['station_name'],
			'title'       => $offline ? $offline_msg : __( 'Live radio', 'piratenkrakers' ),
			'song'        => '',
			'artwork'     => (string) $options['fallback_artwork'],
			'dj'          => $from_guide['dj'] ?? '',
			'show'        => $from_guide['show'] ?? '',
			'dj_id'       => $from_guide['dj_id'] ?? 0,
			'show_id'     => $from_guide['show_id'] ?? 0,
			'dj_url'      => $from_guide['dj_url'] ?? '',
			'show_url'    => $from_guide['show_url'] ?? '',
			'show_start'  => $from_guide['start'] ?? '',
			'show_end'    => $from_guide['end'] ?? '',
			'listeners'   => 0,
			'bitrate'     => null,
			'genre'       => $from_guide['genre'] ?? '',
			'updated_at'  => gmdate( 'c' ),
			'source'      => $demo ? 'demo' : 'fallback',
		);
	}

	/**
	 * Now playing for a stream (cached).
	 */
	public static function now_playing( ?string $stream_id = null ): array {
		$stream_id = $stream_id ? sanitize_key( $stream_id ) : pk_default_stream_id();
		$stream    = pk_get_stream( $stream_id );
		if ( ! $stream ) {
			return self::empty_payload( $stream_id, true );
		}

		$interval = max( 5, absint( pk_get_option( 'update_interval', 12 ) ) );
		$cache    = 'pk_np_' . md5( $stream_id );
		$cached   = get_transient( $cache );
		if ( is_array( $cached ) ) {
			return $cached;
		}

		$payload = self::fetch( $stream );
		set_transient( $cache, $payload, $interval );

		/**
		 * Fires after now-playing is refreshed.
		 *
		 * @param array $payload   Normalized payload.
		 * @param array $stream    Stream config.
		 */
		do_action( 'pk_now_playing_updated', $payload, $stream );

		return $payload;
	}

	/**
	 * Fetch + normalize + enrich.
	 */
	public static function fetch( array $stream ): array {
		$options   = pk_get_options();
		$stream_id = (string) ( $stream['id'] ?? 'main' );
		$has_url   = ! empty( $stream['stream_url'] );
		$has_meta  = ! empty( $stream['metadata_url'] );

		if ( ! $has_url && ! $has_meta ) {
			// Preview/demo: no stream yet. Use program guide + branded fallback, not an offline error.
			$payload               = self::empty_payload( $stream_id, false );
			$payload['status']     = 'demo';
			$payload['demo']       = true;
			$payload['configured'] = false;
			$payload['source']     = 'demo';
			$payload['is_live']    = ! empty( $payload['show'] );
			return $payload;
		}

		$adapter_key = sanitize_key( (string) ( $stream['adapter'] ?? 'custom' ) );
		$adapters    = self::adapters();
		$raw         = array();
		$source      = 'fallback';

		if ( $has_meta && isset( $adapters[ $adapter_key ] ) ) {
			try {
				$adapter = new $adapters[ $adapter_key ]();
				if ( $adapter instanceof PK_Metadata_Adapter ) {
					$raw    = $adapter->fetch( $stream, $options );
					$source = $adapter_key;
				}
			} catch ( Throwable $e ) {
				$raw = array( 'offline' => true, 'is_live' => false );
			}
		}

		$offline = ! empty( $raw['offline'] ) || ( isset( $raw['is_live'] ) && false === $raw['is_live'] );
		$payload = self::empty_payload( $stream_id, $offline );

		$artist = trim( (string) ( $raw['artist'] ?? '' ) );
		$title  = trim( (string) ( $raw['title'] ?? '' ) );

		if ( '' !== $artist ) {
			$payload['artist'] = $artist;
		}
		if ( '' !== $title ) {
			$payload['title'] = $title;
		}
		if ( '' !== $artist || '' !== $title ) {
			$payload['song'] = trim( $artist . ' – ' . $title, ' –' );
		}

		$art = pk_sanitize_url( (string) ( $raw['artwork'] ?? '' ) );
		if ( ! $art && ! empty( $stream['artwork_url'] ) ) {
			// Optional dedicated artwork endpoint returning JSON {url} or a direct image URL.
			$art = self::fetch_artwork( (string) $stream['artwork_url'], $options );
		}
		if ( $art ) {
			$payload['artwork'] = $art;
		}
		if ( ! $payload['artwork'] ) {
			$payload['artwork'] = (string) $options['fallback_artwork'];
		}

		if ( empty( $raw['listeners'] ) && ! empty( $stream['listeners_url'] ) ) {
			$raw['listeners'] = self::fetch_listeners( (string) $stream['listeners_url'], $options );
		}

		if ( ! empty( $raw['dj'] ) ) {
			$payload['dj'] = sanitize_text_field( (string) $raw['dj'] );
		}
		if ( ! empty( $raw['show'] ) ) {
			$payload['show'] = sanitize_text_field( (string) $raw['show'] );
		}
		if ( isset( $raw['listeners'] ) ) {
			$payload['listeners'] = max( 0, (int) $raw['listeners'] );
		}
		if ( isset( $raw['bitrate'] ) ) {
			$payload['bitrate'] = $raw['bitrate'];
		}
		if ( ! empty( $raw['genre'] ) ) {
			$payload['genre'] = sanitize_text_field( (string) $raw['genre'] );
		}

		$payload['is_live']    = ! $offline;
		$payload['offline']    = $offline;
		$payload['demo']       = false;
		$payload['status']     = $offline ? 'offline' : 'online';
		$payload['configured'] = $has_url;
		$payload['source']     = $source;
		$payload['updated_at'] = gmdate( 'c' );

		return self::enrich_from_guide( $payload );
	}

	/**
	 * Optional artwork endpoint (JSON with url/artwork/image, or a direct image URL).
	 */
	protected static function fetch_artwork( string $url, array $options ): string {
		$url = pk_sanitize_url( $url );
		if ( '' === $url ) {
			return '';
		}

		$path = (string) wp_parse_url( $url, PHP_URL_PATH );
		if ( preg_match( '/\.(jpe?g|png|webp|gif|svg)$/i', $path ) ) {
			return $url;
		}

		$response = wp_remote_get(
			$url,
			array(
				'timeout'   => 3,
				'sslverify' => ! empty( $options['sslverify'] ),
			)
		);
		if ( is_wp_error( $response ) ) {
			return '';
		}
		$body = wp_remote_retrieve_body( $response );
		$data = json_decode( $body, true );
		if ( ! is_array( $data ) ) {
			return '';
		}
		foreach ( array( 'url', 'artwork', 'art', 'image', 'cover' ) as $key ) {
			if ( ! empty( $data[ $key ] ) && is_scalar( $data[ $key ] ) ) {
				return pk_sanitize_url( (string) $data[ $key ] );
			}
		}
		return '';
	}

	/**
	 * Optional listeners endpoint (JSON {listeners|current|count} or a plain integer).
	 */
	protected static function fetch_listeners( string $url, array $options ): int {
		$url = pk_sanitize_url( $url );
		if ( '' === $url ) {
			return 0;
		}
		$response = wp_remote_get(
			$url,
			array(
				'timeout'   => 3,
				'sslverify' => ! empty( $options['sslverify'] ),
			)
		);
		if ( is_wp_error( $response ) ) {
			return 0;
		}
		$body = trim( wp_remote_retrieve_body( $response ) );
		if ( is_numeric( $body ) ) {
			return max( 0, (int) $body );
		}
		$data = json_decode( $body, true );
		if ( ! is_array( $data ) ) {
			return 0;
		}
		foreach ( array( 'listeners', 'current', 'count', 'unique', 'audience' ) as $key ) {
			if ( isset( $data[ $key ] ) && is_numeric( $data[ $key ] ) ) {
				return max( 0, (int) $data[ $key ] );
			}
			if ( isset( $data['listeners'][ $key ] ) && is_numeric( $data['listeners'][ $key ] ) ) {
				return max( 0, (int) $data['listeners'][ $key ] );
			}
		}
		return 0;
	}

	/**
	 * Fill DJ/show from the WordPress program guide when the stream API omits them.
	 */
	public static function enrich_from_guide( array $payload ): array {
		$guide = self::current_from_guide();
		foreach ( array( 'dj', 'show', 'genre', 'dj_id', 'show_id', 'dj_url', 'show_url' ) as $key ) {
			if ( empty( $payload[ $key ] ) && ! empty( $guide[ $key ] ) ) {
				$payload[ $key ] = $guide[ $key ];
			}
		}
		if ( empty( $payload['show_start'] ) && ! empty( $guide['start'] ) ) {
			$payload['show_start'] = $guide['start'];
		}
		if ( empty( $payload['show_end'] ) && ! empty( $guide['end'] ) ) {
			$payload['show_end'] = $guide['end'];
		}
		return $payload;
	}

	/**
	 * Current show from CPT schedule.
	 *
	 * @return array{dj?:string,show?:string,genre?:string,dj_id?:int,show_id?:int}
	 */
	public static function current_from_guide(): array {
		$day   = pk_current_weekday();
		$shows = get_posts(
			array(
				'post_type'      => 'pk_show',
				'posts_per_page' => 50,
				'post_status'    => 'publish',
				'no_found_rows'  => true,
			)
		);

		foreach ( $shows as $show ) {
			if ( ! pk_is_show_active( $show->ID ) ) {
				continue;
			}
			$days  = (array) get_post_meta( $show->ID, 'pk_weekdays', true );
			$days  = array_map( 'intval', $days );
			$start = (string) get_post_meta( $show->ID, 'pk_start', true );
			$end   = (string) get_post_meta( $show->ID, 'pk_end', true );
			if ( ! in_array( $day, $days, true ) || '' === $start || '' === $end ) {
				continue;
			}
			if ( ! pk_is_on_air( $start, $end ) ) {
				continue;
			}

			$dj_id = absint( get_post_meta( $show->ID, 'pk_dj_id', true ) );
			$dj    = $dj_id ? get_the_title( $dj_id ) : '';
			$genre = (string) get_post_meta( $show->ID, 'pk_genre_text', true );

			return array(
				'dj'      => $dj,
				'show'    => get_the_title( $show ),
				'genre'   => $genre,
				'dj_id'   => $dj_id,
				'show_id' => $show->ID,
				'dj_url'  => $dj_id ? get_permalink( $dj_id ) : '',
				'show_url'=> get_permalink( $show ),
				'start'   => $start,
				'end'     => $end,
			);
		}

		return array();
	}

	/**
	 * Public stream list for the frontend engine.
	 */
	public static function public_streams(): array {
		$out = array();
		foreach ( pk_get_enabled_streams() as $id => $stream ) {
			$out[] = array(
				'id'          => $id,
				'name'        => $stream['name'] ?? $id,
				'stream_url'  => $stream['stream_url'] ?? '',
				'format'      => $stream['format'] ?? 'mp3',
				'configured'  => ! empty( $stream['stream_url'] ),
			);
		}
		return $out;
	}

	/**
	 * Station status.
	 */
	public static function status( ?string $stream_id = null ): array {
		$np = self::now_playing( $stream_id );
		return array(
			'station'     => $np['station'],
			'stream_id'   => $np['stream_id'],
			'status'      => $np['status'],
			'is_live'     => $np['is_live'],
			'offline'     => $np['offline'],
			'demo'        => ! empty( $np['demo'] ),
			'configured'  => $np['configured'],
			'listeners'   => $np['listeners'],
			'updated_at'  => $np['updated_at'],
		);
	}

	/**
	 * Public schedule (all active shows, grouped by weekday).
	 */
	public static function public_schedule(): array {
		$days = pk_get_schedule();
		$out  = array();
		foreach ( pk_weekdays() as $num => $label ) {
			$items = array();
			foreach ( $days[ $num ] ?? array() as $show ) {
				$start = (string) get_post_meta( $show->ID, 'pk_start', true );
				$end   = (string) get_post_meta( $show->ID, 'pk_end', true );
				$dj_id = absint( get_post_meta( $show->ID, 'pk_dj_id', true ) );
				$items[] = array(
					'id'      => $show->ID,
					'name'    => get_the_title( $show ),
					'url'     => get_permalink( $show ),
					'start'   => $start,
					'end'     => $end,
					'dj'      => $dj_id ? get_the_title( $dj_id ) : '',
					'dj_id'   => $dj_id,
					'dj_url'  => $dj_id ? get_permalink( $dj_id ) : '',
					'live'    => pk_is_on_air( $start, $end ) && $num === pk_current_weekday(),
					'image'   => get_the_post_thumbnail_url( $show, 'pk-card' ) ?: '',
				);
			}
			$out[] = array(
				'weekday' => $num,
				'label'   => $label,
				'today'   => $num === pk_current_weekday(),
				'shows'   => $items,
			);
		}
		return array(
			'timezone' => wp_timezone_string(),
			'days'     => $out,
		);
	}

	/**
	 * Public DJ list.
	 */
	public static function public_djs(): array {
		$djs = get_posts(
			array(
				'post_type'      => 'pk_dj',
				'posts_per_page' => 50,
				'post_status'    => 'publish',
				'orderby'        => 'title',
				'order'          => 'ASC',
			)
		);
		$out = array();
		foreach ( $djs as $dj ) {
			$out[] = array(
				'id'    => $dj->ID,
				'name'  => get_the_title( $dj ),
				'url'   => get_permalink( $dj ),
				'role'  => (string) get_post_meta( $dj->ID, 'pk_role', true ),
				'image' => get_the_post_thumbnail_url( $dj, 'pk-dj' ) ?: '',
			);
		}
		return $out;
	}
}
