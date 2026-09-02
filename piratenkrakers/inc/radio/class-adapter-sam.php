<?php
/**
 * SAM Broadcaster / generic now-playing adapter.
 *
 * Accepts JSON (artist, title, listeners, albumart) or SAM XML
 * (<SONG><ARTIST>…</ARTIST><TITLE>…</TITLE></SONG>).
 *
 * @package PiratenKrakers
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class PK_Adapter_SAM extends PK_Adapter_HTTP implements PK_Metadata_Adapter {
	public function fetch( array $stream, array $options ): array {
		$url = pk_sanitize_url( $stream['metadata_url'] ?? '' );
		if ( '' === $url ) {
			return array( 'offline' => true, 'is_live' => false );
		}

		$response = wp_remote_get(
			$url,
			array(
				'timeout'   => 4,
				'sslverify' => ! empty( $options['sslverify'] ),
				'headers'   => array(
					'Accept'     => 'application/json, application/xml, text/xml, */*',
					'User-Agent' => 'PiratenKrakers-RadioEngine/' . PK_VERSION,
				),
			)
		);
		if ( is_wp_error( $response ) ) {
			return array( 'offline' => true, 'is_live' => false );
		}
		$code = wp_remote_retrieve_response_code( $response );
		if ( $code < 200 || $code >= 300 ) {
			return array( 'offline' => true, 'is_live' => false );
		}

		$body = wp_remote_retrieve_body( $response );
		$data = json_decode( $body, true );
		if ( is_array( $data ) ) {
			$artist = $data['artist'] ?? $data['Artist'] ?? $data['songArtist'] ?? '';
			$title  = $data['title'] ?? $data['Title'] ?? $data['songTitle'] ?? $data['song'] ?? '';
			if ( is_string( $title ) && '' === $artist && str_contains( $title, ' - ' ) ) {
				$parts  = explode( ' - ', $title, 2 );
				$artist = $parts[0];
				$title  = $parts[1];
			}
			$art = $data['albumart'] ?? $data['artwork'] ?? $data['cover'] ?? $data['picture'] ?? '';
			$lis = $data['listeners'] ?? $data['audience'] ?? $data['unique'] ?? 0;
			return array(
				'artist'    => is_scalar( $artist ) ? (string) $artist : '',
				'title'     => is_scalar( $title ) ? (string) $title : '',
				'artwork'   => is_scalar( $art ) ? pk_sanitize_url( (string) $art ) : '',
				'listeners' => is_numeric( $lis ) ? (int) $lis : 0,
				'is_live'   => true,
				'offline'   => false,
			);
		}

		if ( function_exists( 'simplexml_load_string' ) ) {
			$xml = @simplexml_load_string( $body ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
			if ( $xml ) {
				$artist = (string) ( $xml->ARTIST ?? $xml->artist ?? $xml->SONG->ARTIST ?? '' );
				$title  = (string) ( $xml->TITLE ?? $xml->title ?? $xml->SONG->TITLE ?? '' );
				return array(
					'artist'  => $artist,
					'title'   => $title,
					'is_live' => true,
					'offline' => false,
				);
			}
		}

		return array( 'offline' => true, 'is_live' => false );
	}
}
