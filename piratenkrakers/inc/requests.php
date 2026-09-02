<?php
/**
 * Song request (verzoekje) handling.
 *
 * @package PiratenKrakers
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * REST + AJAX submit.
 *
 * @param WP_REST_Request|null $request Request.
 * @return WP_REST_Response|array
 */
function pk_rest_submit_request( $request = null ) {
	$params = array();
	if ( $request instanceof WP_REST_Request ) {
		$params = $request->get_json_params();
		if ( ! is_array( $params ) || ! $params ) {
			$params = $request->get_body_params();
		}
	} else {
		$params = wp_unslash( $_POST ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
	}

	$nonce = isset( $params['_wpnonce'] ) ? sanitize_text_field( (string) $params['_wpnonce'] ) : ( isset( $params['nonce'] ) ? sanitize_text_field( (string) $params['nonce'] ) : '' );
	if ( ! wp_verify_nonce( $nonce, 'pk_request' ) && ! wp_verify_nonce( $nonce, 'wp_rest' ) ) {
		return new WP_REST_Response( array( 'ok' => false, 'message' => __( 'Beveiligingscheck mislukt. Vernieuw de pagina.', 'piratenkrakers' ) ), 403 );
	}

	// Honeypot.
	$hp = isset( $params['website'] ) ? trim( (string) $params['website'] ) : '';
	if ( '' !== $hp ) {
		return new WP_REST_Response( array( 'ok' => true, 'message' => __( 'Bedankt! We hebben je verzoekje ontvangen.', 'piratenkrakers' ) ), 200 );
	}

	$ip    = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
	$stamp = 'pk_req_' . md5( $ip );
	if ( get_transient( $stamp ) ) {
		return new WP_REST_Response( array( 'ok' => false, 'message' => __( 'Je hebt zojuist al een verzoekje gestuurd. Probeer het zo opnieuw.', 'piratenkrakers' ) ), 429 );
	}

	$name    = isset( $params['name'] ) ? sanitize_text_field( (string) $params['name'] ) : '';
	$place   = isset( $params['place'] ) ? sanitize_text_field( (string) $params['place'] ) : '';
	$message = isset( $params['message'] ) ? sanitize_textarea_field( (string) $params['message'] ) : '';
	$song    = isset( $params['song'] ) ? sanitize_text_field( (string) $params['song'] ) : '';
	$phone   = isset( $params['phone'] ) ? sanitize_text_field( (string) $params['phone'] ) : '';
	$consent = ! empty( $params['consent'] );

	$errors = array();
	if ( strlen( $name ) < 2 ) {
		$errors[] = __( 'Vul je naam in.', 'piratenkrakers' );
	}
	if ( strlen( $place ) < 2 ) {
		$errors[] = __( 'Vul je plaats in.', 'piratenkrakers' );
	}
	if ( strlen( $song ) < 2 ) {
		$errors[] = __( 'Vul een verzoeknummer in.', 'piratenkrakers' );
	}
	if ( strlen( $message ) > 1000 ) {
		$errors[] = __( 'Je bericht is te lang.', 'piratenkrakers' );
	}
	if ( ! $consent ) {
		$errors[] = __( 'Geef toestemming om je verzoekje te gebruiken.', 'piratenkrakers' );
	}

	if ( $errors ) {
		return new WP_REST_Response( array( 'ok' => false, 'message' => implode( ' ', $errors ) ), 400 );
	}

	$post_id = wp_insert_post(
		array(
			'post_type'    => 'pk_request',
			'post_status'  => 'private',
			'post_title'   => wp_trim_words( $song . ' — ' . $name, 12, '' ),
			'post_content' => $message,
		),
		true
	);

	if ( is_wp_error( $post_id ) ) {
		return new WP_REST_Response( array( 'ok' => false, 'message' => __( 'Opslaan mislukt. Probeer het later opnieuw.', 'piratenkrakers' ) ), 500 );
	}

	update_post_meta( $post_id, 'pk_name', $name );
	update_post_meta( $post_id, 'pk_place', $place );
	update_post_meta( $post_id, 'pk_song', $song );
	update_post_meta( $post_id, 'pk_phone', $phone );
	update_post_meta( $post_id, 'pk_consent', $consent ? '1' : '' );
	update_post_meta( $post_id, 'pk_status', 'new' );
	update_post_meta( $post_id, 'pk_ip', $ip );

	set_transient( $stamp, 1, 45 );

	do_action( 'pk_request_submitted', $post_id, $params );

	return new WP_REST_Response(
		array(
			'ok'      => true,
			'message' => __( 'Bedankt! Je verzoekje is binnen. Misschien draaien we hem zo.', 'piratenkrakers' ),
		),
		200
	);
}

/**
 * AJAX fallback for hosts without pretty REST.
 */
function pk_ajax_submit_request(): void {
	$response = pk_rest_submit_request();
	if ( $response instanceof WP_REST_Response ) {
		wp_send_json( $response->get_data(), $response->get_status() );
	}
	wp_send_json( $response );
}
add_action( 'wp_ajax_pk_request', 'pk_ajax_submit_request' );
add_action( 'wp_ajax_nopriv_pk_request', 'pk_ajax_submit_request' );
