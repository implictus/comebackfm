<?php
/**
 * Custom fields for DJs, shows and requests.
 *
 * @package PiratenKrakers
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register meta boxes.
 */
function pk_add_meta_boxes(): void {
	add_meta_box( 'pk_dj_meta', __( 'DJ-gegevens', 'piratenkrakers' ), 'pk_render_dj_meta', 'pk_dj', 'normal', 'high' );
	add_meta_box( 'pk_show_meta', __( 'Programmagegevens', 'piratenkrakers' ), 'pk_render_show_meta', 'pk_show', 'normal', 'high' );
	add_meta_box( 'pk_request_meta', __( 'Verzoekgegevens', 'piratenkrakers' ), 'pk_render_request_meta', 'pk_request', 'normal', 'high' );
}
add_action( 'add_meta_boxes', 'pk_add_meta_boxes' );

/**
 * DJ meta.
 */
function pk_render_dj_meta( WP_Post $post ): void {
	wp_nonce_field( 'pk_save_dj', 'pk_dj_nonce' );
	$alias   = get_post_meta( $post->ID, 'pk_alias', true );
	$role    = get_post_meta( $post->ID, 'pk_role', true );
	$fav     = get_post_meta( $post->ID, 'pk_favorite_music', true );
	$fb      = get_post_meta( $post->ID, 'pk_facebook', true );
	$ig      = get_post_meta( $post->ID, 'pk_instagram', true );
	$tt      = get_post_meta( $post->ID, 'pk_tiktok', true );
	$yt      = get_post_meta( $post->ID, 'pk_youtube', true );
	?>
	<p><label>Alias / bijnaam<br><input type="text" class="widefat" name="pk_alias" value="<?php echo esc_attr( $alias ); ?>"></label></p>
	<p><label>Rol (bijv. Avond-DJ)<br><input type="text" class="widefat" name="pk_role" value="<?php echo esc_attr( $role ); ?>"></label></p>
	<p><label>Favoriete muziek<br><textarea class="widefat" rows="3" name="pk_favorite_music"><?php echo esc_textarea( $fav ); ?></textarea></label></p>
	<p><label>Facebook-URL<br><input type="url" class="widefat" name="pk_facebook" value="<?php echo esc_attr( $fb ); ?>"></label></p>
	<p><label>Instagram-URL<br><input type="url" class="widefat" name="pk_instagram" value="<?php echo esc_attr( $ig ); ?>"></label></p>
	<p><label>TikTok-URL<br><input type="url" class="widefat" name="pk_tiktok" value="<?php echo esc_attr( $tt ); ?>"></label></p>
	<p><label>YouTube-URL<br><input type="url" class="widefat" name="pk_youtube" value="<?php echo esc_attr( $yt ); ?>"></label></p>
	<?php
}

/**
 * Show meta.
 */
function pk_render_show_meta( WP_Post $post ): void {
	wp_nonce_field( 'pk_save_show', 'pk_show_nonce' );
	$dj_id = absint( get_post_meta( $post->ID, 'pk_dj_id', true ) );
	$start = get_post_meta( $post->ID, 'pk_start', true );
	$end   = get_post_meta( $post->ID, 'pk_end', true );
	$days  = (array) get_post_meta( $post->ID, 'pk_weekdays', true );
	$genre  = get_post_meta( $post->ID, 'pk_genre_text', true );
	$active = pk_is_show_active( $post->ID );

	$djs = get_posts(
		array(
			'post_type'      => 'pk_dj',
			'posts_per_page' => 100,
			'orderby'        => 'title',
			'order'          => 'ASC',
			'post_status'    => 'publish',
		)
	);
	?>
	<p><label>DJ<br>
		<select name="pk_dj_id" class="widefat">
			<option value="0">— kies een DJ —</option>
			<?php foreach ( $djs as $dj ) : ?>
				<option value="<?php echo esc_attr( (string) $dj->ID ); ?>" <?php selected( $dj_id, $dj->ID ); ?>><?php echo esc_html( $dj->post_title ); ?></option>
			<?php endforeach; ?>
		</select>
	</label></p>
	<p><label>Begintijd<br><input type="time" name="pk_start" value="<?php echo esc_attr( $start ? $start : '18:00' ); ?>"></label>
	<label style="margin-left:12px">Eindtijd<br><input type="time" name="pk_end" value="<?php echo esc_attr( $end ? $end : '20:00' ); ?>"></label></p>
	<p>Dagen<br>
		<?php foreach ( pk_weekdays() as $num => $label ) : ?>
			<label style="margin-right:12px"><input type="checkbox" name="pk_weekdays[]" value="<?php echo esc_attr( (string) $num ); ?>" <?php checked( in_array( (string) $num, array_map( 'strval', $days ), true ) || in_array( $num, $days, true ) ); ?>> <?php echo esc_html( $label ); ?></label>
		<?php endforeach; ?>
	</p>
	<p><label>Genre<br><input type="text" class="widefat" name="pk_genre_text" value="<?php echo esc_attr( $genre ); ?>" placeholder="Piratenhits, levenslied, feest"></label></p>
	<p><label><input type="checkbox" name="pk_active" value="1" <?php checked( $active ); ?>> Programma is actief (zichtbaar in de gids en LIVE-detectie)</label></p>
	<?php
}

/**
 * Request meta (read mostly).
 */
function pk_render_request_meta( WP_Post $post ): void {
	wp_nonce_field( 'pk_save_request', 'pk_request_nonce' );
	$name    = get_post_meta( $post->ID, 'pk_name', true );
	$place   = get_post_meta( $post->ID, 'pk_place', true );
	$song    = get_post_meta( $post->ID, 'pk_song', true );
	$phone   = get_post_meta( $post->ID, 'pk_phone', true );
	$status  = get_post_meta( $post->ID, 'pk_status', true ) ?: 'new';
	$consent = get_post_meta( $post->ID, 'pk_consent', true );
	$ip      = get_post_meta( $post->ID, 'pk_ip', true );
	?>
	<p><label>Naam<br><input type="text" class="widefat" name="pk_name" value="<?php echo esc_attr( $name ); ?>"></label></p>
	<p><label>Plaats<br><input type="text" class="widefat" name="pk_place" value="<?php echo esc_attr( $place ); ?>"></label></p>
	<p><label>Verzoeknummer<br><input type="text" class="widefat" name="pk_song" value="<?php echo esc_attr( $song ); ?>"></label></p>
	<p><label>Telefoon<br><input type="text" class="widefat" name="pk_phone" value="<?php echo esc_attr( $phone ); ?>"></label></p>
	<p><label>Status<br>
		<select name="pk_status">
			<option value="new" <?php selected( $status, 'new' ); ?>>Nieuw</option>
			<option value="approved" <?php selected( $status, 'approved' ); ?>>Goedgekeurd</option>
			<option value="played" <?php selected( $status, 'played' ); ?>>Gedraaid</option>
			<option value="spam" <?php selected( $status, 'spam' ); ?>>Spam</option>
		</select>
	</label></p>
	<p>Toestemming publicatie: <strong><?php echo $consent ? 'ja' : 'nee'; ?></strong></p>
	<p>IP: <code><?php echo esc_html( $ip ); ?></code></p>
	<?php
}

/**
 * Save DJ.
 */
function pk_save_dj_meta( int $post_id ): void {
	if ( ! isset( $_POST['pk_dj_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['pk_dj_nonce'] ) ), 'pk_save_dj' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	$fields = array( 'pk_alias', 'pk_role', 'pk_favorite_music' );
	foreach ( $fields as $field ) {
		if ( isset( $_POST[ $field ] ) ) {
			update_post_meta( $post_id, $field, sanitize_textarea_field( wp_unslash( $_POST[ $field ] ) ) );
		}
	}
	foreach ( array( 'pk_facebook', 'pk_instagram', 'pk_tiktok', 'pk_youtube' ) as $url_field ) {
		if ( isset( $_POST[ $url_field ] ) ) {
			update_post_meta( $post_id, $url_field, pk_sanitize_url( wp_unslash( $_POST[ $url_field ] ) ) );
		}
	}
}
add_action( 'save_post_pk_dj', 'pk_save_dj_meta' );

/**
 * Save show.
 */
function pk_save_show_meta( int $post_id ): void {
	if ( ! isset( $_POST['pk_show_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['pk_show_nonce'] ) ), 'pk_save_show' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	update_post_meta( $post_id, 'pk_dj_id', isset( $_POST['pk_dj_id'] ) ? absint( $_POST['pk_dj_id'] ) : 0 );
	update_post_meta( $post_id, 'pk_start', isset( $_POST['pk_start'] ) ? sanitize_text_field( wp_unslash( $_POST['pk_start'] ) ) : '' );
	update_post_meta( $post_id, 'pk_end', isset( $_POST['pk_end'] ) ? sanitize_text_field( wp_unslash( $_POST['pk_end'] ) ) : '' );
	update_post_meta( $post_id, 'pk_genre_text', isset( $_POST['pk_genre_text'] ) ? sanitize_text_field( wp_unslash( $_POST['pk_genre_text'] ) ) : '' );

	$days = array();
	if ( isset( $_POST['pk_weekdays'] ) && is_array( $_POST['pk_weekdays'] ) ) {
		foreach ( wp_unslash( $_POST['pk_weekdays'] ) as $day ) { // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			$n = absint( $day );
			if ( $n >= 1 && $n <= 7 ) {
				$days[] = $n;
			}
		}
	}
	update_post_meta( $post_id, 'pk_weekdays', $days );
	update_post_meta( $post_id, 'pk_active', isset( $_POST['pk_active'] ) ? '1' : '0' );
}
add_action( 'save_post_pk_show', 'pk_save_show_meta' );

/**
 * Save request.
 */
function pk_save_request_meta( int $post_id ): void {
	if ( ! isset( $_POST['pk_request_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['pk_request_nonce'] ) ), 'pk_save_request' ) ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}
	foreach ( array( 'pk_name', 'pk_place', 'pk_song', 'pk_phone', 'pk_status' ) as $field ) {
		if ( isset( $_POST[ $field ] ) ) {
			update_post_meta( $post_id, $field, sanitize_text_field( wp_unslash( $_POST[ $field ] ) ) );
		}
	}
}
add_action( 'save_post_pk_request', 'pk_save_request_meta' );
