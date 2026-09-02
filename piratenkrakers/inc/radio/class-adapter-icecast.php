<?php
/**
 * Icecast status-json.xsl adapter.
 *
 * metadata_url example:
 *   https://stream.example.com/status-json.xsl
 *
 * Optional mount: /listen/piratenkrakers/radio.mp3
 *
 * @package PiratenKrakers
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class PK_Adapter_Icecast extends PK_Adapter_HTTP implements PK_Metadata_Adapter {
	public function fetch( array $stream, array $options ): array {
		$url  = pk_sanitize_url( $stream['metadata_url'] ?? '' );
		$data = $this->get_json( $url, $options );
		if ( null === $data ) {
			return array( 'offline' => true, 'is_live' => false );
		}

		$source = $data['icestats']['source'] ?? $data['source'] ?? null;
		if ( null === $source ) {
			return array( 'offline' => true, 'is_live' => false );
		}

		if ( isset( $source[0] ) && is_array( $source[0] ) ) {
			$mount   = (string) ( $stream['mount'] ?? '' );
			$picked  = $source[0];
			if ( $mount ) {
				foreach ( $source as $row ) {
					$listen = (string) ( $row['listenurl'] ?? $row['server_name'] ?? '' );
					if ( str_contains( $listen, $mount ) ) {
						$picked = $row;
						break;
					}
				}
			}
			$source = $picked;
		}

		if ( ! is_array( $source ) ) {
			return array( 'offline' => true, 'is_live' => false );
		}

		$title  = (string) ( $source['title'] ?? $source['yp_currently_playing'] ?? '' );
		$artist = (string) ( $source['artist'] ?? '' );

		if ( '' === $artist && str_contains( $title, ' - ' ) ) {
			$parts  = explode( ' - ', $title, 2 );
			$artist = $parts[0];
			$title  = $parts[1];
		}

		return array(
			'artist'    => $artist,
			'title'     => $title,
			'artwork'   => '',
			'dj'        => '',
			'show'      => (string) ( $source['server_description'] ?? '' ),
			'listeners' => isset( $source['listeners'] ) ? (int) $source['listeners'] : 0,
			'bitrate'   => isset( $source['bitrate'] ) ? (int) $source['bitrate'] : null,
			'genre'     => (string) ( $source['genre'] ?? '' ),
			'is_live'   => true,
			'offline'   => false,
		);
	}
}
