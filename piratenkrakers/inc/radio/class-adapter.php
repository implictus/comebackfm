<?php
/**
 * Metadata adapter contract.
 *
 * The radio engine talks only to adapters. The theme UI never fetches
 * Icecast/AzuraCast/Shoutcast directly. Swap or add adapters without
 * touching templates or CSS.
 *
 * @package PiratenKrakers
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

interface PK_Metadata_Adapter {
	/**
	 * Fetch raw-normalized metadata for a stream.
	 *
	 * @param array $stream Stream settings.
	 * @param array $options Global radio options.
	 * @return array{
	 *   artist?: string,
	 *   title?: string,
	 *   artwork?: string,
	 *   dj?: string,
	 *   show?: string,
	 *   listeners?: int,
	 *   is_live?: bool,
	 *   offline?: bool,
	 *   bitrate?: int|null,
	 *   genre?: string
	 * }
	 */
	public function fetch( array $stream, array $options ): array;
}

/**
 * Shared HTTP helper for adapters.
 */
abstract class PK_Adapter_HTTP {
	/**
	 * GET JSON (or decode JSON from body).
	 *
	 * @return array<string,mixed>|null
	 */
	protected function get_json( string $url, array $options, int $timeout = 4 ): ?array {
		if ( '' === $url ) {
			return null;
		}

		$response = wp_remote_get(
			$url,
			array(
				'timeout'     => $timeout,
				'redirection' => 2,
				'sslverify'   => ! empty( $options['sslverify'] ),
				'headers'     => array(
					'Accept'     => 'application/json, text/plain, */*',
					'User-Agent' => 'PiratenKrakers-RadioEngine/' . PK_VERSION,
				),
			)
		);

		if ( is_wp_error( $response ) ) {
			return null;
		}

		$code = wp_remote_retrieve_response_code( $response );
		if ( $code < 200 || $code >= 300 ) {
			return null;
		}

		$body = wp_remote_retrieve_body( $response );
		if ( '' === $body ) {
			return null;
		}

		$data = json_decode( $body, true );
		return is_array( $data ) ? $data : null;
	}
}
