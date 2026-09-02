<?php
/**
 * REST API for the radio engine.
 *
 * Frontend contract (stable):
 *   GET  /wp-json/pk/v1/now-playing
 *   GET  /wp-json/pk/v1/now-playing/{stream}
 *   GET  /wp-json/pk/v1/streams
 *   GET  /wp-json/pk/v1/status
 *   GET  /wp-json/pk/v1/status/{stream}
 *   GET  /wp-json/pk/v1/schedule
 *   GET  /wp-json/pk/v1/djs
 *   POST /wp-json/pk/v1/request
 *
 * @package PiratenKrakers
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register routes.
 */
function pk_register_rest(): void {
	$ns = PK_Radio_Engine::REST_NAMESPACE;

	register_rest_route(
		$ns,
		'/now-playing',
		array(
			'methods'             => 'GET',
			'callback'            => static function () {
				return rest_ensure_response( PK_Radio_Engine::now_playing() );
			},
			'permission_callback' => '__return_true',
		)
	);

	register_rest_route(
		$ns,
		'/now-playing/(?P<stream>[a-zA-Z0-9_-]+)',
		array(
			'methods'             => 'GET',
			'callback'            => static function ( WP_REST_Request $request ) {
				return rest_ensure_response( PK_Radio_Engine::now_playing( (string) $request['stream'] ) );
			},
			'permission_callback' => '__return_true',
		)
	);

	register_rest_route(
		$ns,
		'/streams',
		array(
			'methods'             => 'GET',
			'callback'            => static function () {
				return rest_ensure_response(
					array(
						'default' => pk_default_stream_id(),
						'streams' => PK_Radio_Engine::public_streams(),
					)
				);
			},
			'permission_callback' => '__return_true',
		)
	);

	register_rest_route(
		$ns,
		'/status',
		array(
			'methods'             => 'GET',
			'callback'            => static function () {
				return rest_ensure_response( PK_Radio_Engine::status() );
			},
			'permission_callback' => '__return_true',
		)
	);

	register_rest_route(
		$ns,
		'/status/(?P<stream>[a-zA-Z0-9_-]+)',
		array(
			'methods'             => 'GET',
			'callback'            => static function ( WP_REST_Request $request ) {
				return rest_ensure_response( PK_Radio_Engine::status( (string) $request['stream'] ) );
			},
			'permission_callback' => '__return_true',
		)
	);

	register_rest_route(
		$ns,
		'/schedule',
		array(
			'methods'             => 'GET',
			'callback'            => static function () {
				return rest_ensure_response( PK_Radio_Engine::public_schedule() );
			},
			'permission_callback' => '__return_true',
		)
	);

	register_rest_route(
		$ns,
		'/djs',
		array(
			'methods'             => 'GET',
			'callback'            => static function () {
				return rest_ensure_response( PK_Radio_Engine::public_djs() );
			},
			'permission_callback' => '__return_true',
		)
	);

	register_rest_route(
		$ns,
		'/request',
		array(
			'methods'             => 'POST',
			'callback'            => 'pk_rest_submit_request',
			'permission_callback' => '__return_true',
		)
	);
}
add_action( 'rest_api_init', 'pk_register_rest' );
