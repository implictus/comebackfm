<?php
/**
 * Custom JSON metadata adapter.
 *
 * Point metadata_url at any JSON endpoint and map fields with dot-paths
 * (e.g. now_playing.song.artist).
 *
 * @package PiratenKrakers
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class PK_Adapter_Custom extends PK_Adapter_HTTP implements PK_Metadata_Adapter {
	public function fetch( array $stream, array $options ): array {
		$url  = pk_sanitize_url( $stream['metadata_url'] ?? '' );
		$data = $this->get_json( $url, $options );
		if ( null === $data ) {
			return array( 'offline' => true, 'is_live' => false );
		}

		$artist    = pk_dot_get( $data, (string) ( $stream['map_artist'] ?? 'artist' ) );
		$title     = pk_dot_get( $data, (string) ( $stream['map_title'] ?? 'title' ) );
		$artwork   = pk_dot_get( $data, (string) ( $stream['map_artwork'] ?? 'artwork' ) );
		$dj        = pk_dot_get( $data, (string) ( $stream['map_dj'] ?? 'dj' ) );
		$show      = pk_dot_get( $data, (string) ( $stream['map_show'] ?? 'show' ) );
		$listeners = pk_dot_get( $data, (string) ( $stream['map_listeners'] ?? 'listeners' ) );
		$status    = pk_dot_get( $data, (string) ( $stream['map_status'] ?? 'is_live' ) );

		$is_live = true;
		if ( null !== $status ) {
			$is_live = in_array( $status, array( true, 1, '1', 'true', 'online', 'live' ), true );
		}

		return array(
			'artist'    => is_scalar( $artist ) ? (string) $artist : '',
			'title'     => is_scalar( $title ) ? (string) $title : '',
			'artwork'   => is_scalar( $artwork ) ? pk_sanitize_url( (string) $artwork ) : '',
			'dj'        => is_scalar( $dj ) ? (string) $dj : '',
			'show'      => is_scalar( $show ) ? (string) $show : '',
			'listeners' => is_numeric( $listeners ) ? (int) $listeners : 0,
			'is_live'   => $is_live,
			'offline'   => ! $is_live,
		);
	}
}
