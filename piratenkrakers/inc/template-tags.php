<?php
/**
 * Template helpers used by theme files.
 *
 * @package PiratenKrakers
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Logo markup.
 */
function pk_the_logo( string $variant = 'header' ): void {
	$name      = pk_get_option( 'station_name', 'PiratenKrakers.nl' );
	$src       = pk_get_option( 'logo', pk_asset( 'img/logo-mark.png' ) );
	$custom_id = (int) get_theme_mod( 'custom_logo' );
	?>
	<a class="pk-logo pk-logo--<?php echo esc_attr( $variant ); ?>" href="<?php echo esc_url( home_url( '/' ) ); ?>">
		<?php if ( $custom_id && 'header' === $variant ) : ?>
			<?php echo wp_get_attachment_image( $custom_id, 'full', false, array( 'class' => 'pk-logo-mark custom-logo', 'alt' => '' ) ); ?>
		<?php else : ?>
			<img class="pk-logo-mark" src="<?php echo esc_url( $src ); ?>" alt="" width="48" height="48" decoding="async">
		<?php endif; ?>
		<span class="pk-logo-type">
			<span class="pk-logo-stack">
				<span class="pk-logo-piraten">PIRATEN</span>
				<span class="pk-logo-krakers">KRAKERS<span class="pk-logo-nl">.NL</span></span>
			</span>
		</span>
		<span class="screen-reader-text"><?php echo esc_html( $name ); ?></span>
	</a>
	<?php
}

/**
 * Social links.
 */
function pk_social_links( string $class = 'pk-social' ): void {
	$map = array(
		'facebook'  => 'Facebook',
		'instagram' => 'Instagram',
		'tiktok'    => 'TikTok',
		'youtube'   => 'YouTube',
		'x'         => 'X',
	);
	$social = (array) pk_get_option( 'social', array() );
	$items  = array();
	foreach ( $map as $key => $label ) {
		$url = pk_sanitize_url( $social[ $key ] ?? '' );
		if ( $url ) {
			$items[ $key ] = array( 'url' => $url, 'label' => $label );
		}
	}
	if ( ! $items ) {
		return;
	}
	echo '<ul class="' . esc_attr( $class ) . '">';
	foreach ( $items as $key => $item ) {
		echo '<li><a class="pk-social-link pk-social-link--' . esc_attr( $key ) . '" href="' . esc_url( $item['url'] ) . '" rel="noopener noreferrer" target="_blank"><span class="pk-social-icon" aria-hidden="true"></span><span class="screen-reader-text">' . esc_html( $item['label'] ) . '</span></a></li>';
	}
	echo '</ul>';
}

/**
 * Schedule grouped by weekday.
 *
 * @return array<int, array<int, WP_Post>>
 */
function pk_get_schedule(): array {
	$week  = pk_weekdays();
	$out   = array();
	foreach ( array_keys( $week ) as $day ) {
		$out[ $day ] = array();
	}

	$shows = get_posts(
		array(
			'post_type'      => 'pk_show',
			'posts_per_page' => 100,
			'post_status'    => 'publish',
			'orderby'        => 'title',
			'order'          => 'ASC',
		)
	);

	foreach ( $shows as $show ) {
		if ( ! pk_is_show_active( $show->ID ) ) {
			continue;
		}
		$days  = array_map( 'intval', (array) get_post_meta( $show->ID, 'pk_weekdays', true ) );
		$start = (string) get_post_meta( $show->ID, 'pk_start', true );
		foreach ( $days as $day ) {
			if ( isset( $out[ $day ] ) ) {
				$out[ $day ][] = $show;
			}
		}
		$show->pk_sort = pk_time_to_minutes( $start );
	}

	foreach ( $out as $day => $list ) {
		usort(
			$list,
			static function ( $a, $b ) {
				$sa = pk_time_to_minutes( (string) get_post_meta( $a->ID, 'pk_start', true ) );
				$sb = pk_time_to_minutes( (string) get_post_meta( $b->ID, 'pk_start', true ) );
				return $sa <=> $sb;
			}
		);
		$out[ $day ] = $list;
	}

	return $out;
}

/**
 * Shows for a DJ.
 *
 * @return WP_Post[]
 */
function pk_get_shows_for_dj( int $dj_id ): array {
	$posts = get_posts(
		array(
			'post_type'      => 'pk_show',
			'posts_per_page' => 20,
			'post_status'    => 'publish',
			'meta_key'       => 'pk_dj_id',
			'meta_value'     => $dj_id,
		)
	);
	return array_values(
		array_filter(
			$posts,
			static function ( $show ) {
				return pk_is_show_active( $show->ID );
			}
		)
	);
}

/**
 * Default nav items if no menu is assigned.
 */
function pk_fallback_menu(): void {
	$pages = array(
		'home'       => array( 'label' => __( 'Home', 'piratenkrakers' ), 'url' => home_url( '/' ) ),
		'live'       => array( 'label' => __( 'Live', 'piratenkrakers' ), 'url' => home_url( '/live/' ) ),
		'programma'  => array( 'label' => __( 'Programma', 'piratenkrakers' ), 'url' => get_post_type_archive_link( 'pk_show' ) ?: home_url( '/programma/' ) ),
		'djs'        => array( 'label' => __( "DJ's", 'piratenkrakers' ), 'url' => get_post_type_archive_link( 'pk_dj' ) ?: home_url( '/djs/' ) ),
		'verzoekjes' => array( 'label' => __( 'Verzoekjes', 'piratenkrakers' ), 'url' => home_url( '/verzoekjes/' ) ),
		'nieuws'     => array( 'label' => __( 'Nieuws', 'piratenkrakers' ), 'url' => get_permalink( get_option( 'page_for_posts' ) ) ?: home_url( '/nieuws/' ) ),
		'contact'    => array( 'label' => __( 'Contact', 'piratenkrakers' ), 'url' => home_url( '/contact/' ) ),
	);
	echo '<ul class="pk-nav-list">';
	foreach ( $pages as $item ) {
		echo '<li><a href="' . esc_url( $item['url'] ) . '">' . esc_html( $item['label'] ) . '</a></li>';
	}
	echo '</ul>';
}

/**
 * Pagination.
 */
function pk_pagination(): void {
	the_posts_pagination(
		array(
			'mid_size'  => 1,
			'prev_text' => __( 'Vorige', 'piratenkrakers' ),
			'next_text' => __( 'Volgende', 'piratenkrakers' ),
		)
	);
}
