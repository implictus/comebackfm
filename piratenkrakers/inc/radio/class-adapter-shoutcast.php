<?php
/**
 * SHOUTcast v2 JSON stats adapter.
 *
 * metadata_url example:
 *   http://host:8000/stats?sid=1&json=1
 *
 * @package PiratenKrakers
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class PK_Adapter_Shoutcast extends PK_Adapter_HTTP implements PK_Metadata_Adapter {
	public function fetch( array $stream, array $options ): array {
		$url = pk_sanitize_url( $stream['metadata_url'] ?? '' );
		if ( '' === $url ) {
			return array( 'offline' => true, 'is_live' => false );
		}

		if ( ! str_contains( $url, 'json=' ) ) {
			$sid = sanitize_text_field( (string) ( $stream['sid'] ?? '1' ) );
			$url = add_query_arg(
				array(
					'sid'  => $sid,
					'json' => '1',
				),
				$url
			);
		}

		$data = $this->get_json( $url, $options );
		if ( null === $data ) {
			return array( 'offline' => true, 'is_live' => false );
		}

		$song   = (string) ( $data['songtitle'] ?? $data['song'] ?? '' );
		$artist = '';
		$title  = $song;
		if ( str_contains( $song, ' - ' ) ) {
			$parts  = explode( ' - ', $song, 2 );
			$artist = $parts[0];
			$title  = $parts[1];
		}

		$online = isset( $data['streamstatus'] ) ? (int) $data['streamstatus'] === 1 : true;

		return array(
			'artist'    => $artist,
			'title'     => $title,
			'artwork'   => '',
			'dj'        => '',
			'show'      => (string) ( $data['servertitle'] ?? '' ),
			'listeners' => isset( $data['currentlisteners'] ) ? (int) $data['currentlisteners'] : 0,
			'bitrate'   => isset( $data['bitrate'] ) ? (int) $data['bitrate'] : null,
			'genre'     => (string) ( $data['servergenre'] ?? '' ),
			'is_live'   => $online,
			'offline'   => ! $online,
		);
	}
}
