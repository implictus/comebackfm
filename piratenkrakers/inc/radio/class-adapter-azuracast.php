<?php
/**
 * AzuraCast nowplaying adapter.
 *
 * metadata_url examples:
 *   https://radio.example.com/api/nowplaying
 *   https://radio.example.com/api/nowplaying/station_shortcode
 *
 * @package PiratenKrakers
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class PK_Adapter_AzuraCast extends PK_Adapter_HTTP implements PK_Metadata_Adapter {
	public function fetch( array $stream, array $options ): array {
		$url = pk_sanitize_url( $stream['metadata_url'] ?? '' );
		if ( '' === $url ) {
			return array( 'offline' => true, 'is_live' => false );
		}

		$station = sanitize_text_field( (string) ( $stream['azuracast_station'] ?? '' ) );
		if ( $station && ! str_contains( $url, '/nowplaying/' ) ) {
			$url = untrailingslashit( $url ) . '/' . rawurlencode( $station );
		}

		$data = $this->get_json( $url, $options );
		if ( null === $data ) {
			return array( 'offline' => true, 'is_live' => false );
		}

		// Endpoint may return a list of stations.
		if ( isset( $data[0] ) && is_array( $data[0] ) ) {
			$picked = $data[0];
			if ( $station ) {
				foreach ( $data as $row ) {
					$short = $row['station']['shortcode'] ?? '';
					$id    = isset( $row['station']['id'] ) ? (string) $row['station']['id'] : '';
					if ( $short === $station || $id === $station ) {
						$picked = $row;
						break;
					}
				}
			}
			$data = $picked;
		}

		$live      = ! empty( $data['is_online'] );
		$np        = is_array( $data['now_playing'] ?? null ) ? $data['now_playing'] : array();
		$song      = is_array( $np['song'] ?? null ) ? $np['song'] : array();
		$listeners = $data['listeners']['current'] ?? ( $data['listeners'] ?? 0 );
		$dj        = $data['live']['streamer_name'] ?? '';
		$show      = $np['playlist'] ?? '';

		return array(
			'artist'    => (string) ( $song['artist'] ?? '' ),
			'title'     => (string) ( $song['title'] ?? '' ),
			'artwork'   => pk_sanitize_url( (string) ( $song['art'] ?? '' ) ),
			'dj'        => is_scalar( $dj ) ? (string) $dj : '',
			'show'      => is_scalar( $show ) ? (string) $show : '',
			'listeners' => is_numeric( $listeners ) ? (int) $listeners : 0,
			'is_live'   => (bool) $live,
			'offline'   => ! $live,
		);
	}
}
