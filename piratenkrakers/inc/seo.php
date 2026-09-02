<?php
/**
 * SEO: Open Graph, Twitter cards, JSON-LD, canonical extras.
 *
 * @package PiratenKrakers
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Meta tags.
 */
function pk_seo_head(): void {
	$options = pk_get_options();
	$title   = wp_get_document_title();
	$desc    = pk_seo_description();
	$url     = pk_canonical_url();
	$image   = pk_seo_image();
	$name    = (string) $options['station_name'];

	echo '<link rel="canonical" href="' . esc_url( $url ) . '">' . "\n";
	echo '<meta name="description" content="' . esc_attr( $desc ) . '">' . "\n";

	echo '<meta property="og:site_name" content="' . esc_attr( $name ) . '">' . "\n";
	echo '<meta property="og:type" content="' . esc_attr( is_singular( 'post' ) ? 'article' : 'website' ) . '">' . "\n";
	echo '<meta property="og:title" content="' . esc_attr( $title ) . '">' . "\n";
	echo '<meta property="og:description" content="' . esc_attr( $desc ) . '">' . "\n";
	echo '<meta property="og:url" content="' . esc_url( $url ) . '">' . "\n";
	echo '<meta property="og:locale" content="nl_NL">' . "\n";
	if ( $image ) {
		echo '<meta property="og:image" content="' . esc_url( $image ) . '">' . "\n";
		echo '<meta property="og:image:width" content="1200">' . "\n";
		echo '<meta property="og:image:height" content="630">' . "\n";
	}

	echo '<meta name="twitter:card" content="summary_large_image">' . "\n";
	echo '<meta name="twitter:title" content="' . esc_attr( $title ) . '">' . "\n";
	echo '<meta name="twitter:description" content="' . esc_attr( $desc ) . '">' . "\n";
	if ( $image ) {
		echo '<meta name="twitter:image" content="' . esc_url( $image ) . '">' . "\n";
	}

	pk_schema_jsonld();
}
add_action( 'wp_head', 'pk_seo_head', 5 );

/**
 * Description.
 */
function pk_seo_description(): string {
	if ( is_singular() ) {
		$post = get_queried_object();
		if ( $post instanceof WP_Post ) {
			if ( has_excerpt( $post ) ) {
				return wp_strip_all_tags( get_the_excerpt( $post ) );
			}
			return wp_trim_words( wp_strip_all_tags( $post->post_content ), 32, '…' );
		}
	}
	if ( is_category() || is_tag() || is_tax() ) {
		$obj = get_queried_object();
		if ( $obj && ! empty( $obj->description ) ) {
			return wp_strip_all_tags( $obj->description );
		}
	}
	$tag = pk_get_option( 'tagline', 'Muziek uit het hart' );
	return $tag . ' — ' . __( 'Nederlandse piratenradio.', 'piratenkrakers' );
}

/**
 * Canonical URL.
 */
function pk_canonical_url(): string {
	if ( is_singular() ) {
		return get_permalink() ?: home_url( '/' );
	}
	global $wp;
	return home_url( trailingslashit( $wp->request ?? '' ) );
}

/**
 * Share image.
 */
function pk_seo_image(): string {
	if ( is_singular() && has_post_thumbnail() ) {
		$src = get_the_post_thumbnail_url( get_queried_object_id(), 'full' );
		if ( $src ) {
			return $src;
		}
	}
	return (string) pk_get_option( 'og_image', pk_asset( 'img/og-default.jpg' ) );
}

/**
 * JSON-LD RadioStation + optional Article.
 */
function pk_schema_jsonld(): void {
	$options = pk_get_options();
	$same    = array_values( array_filter( (array) $options['social'] ) );

	$station = array(
		'@context'    => 'https://schema.org',
		'@type'       => 'RadioStation',
		'name'        => $options['station_name'],
		'slogan'      => $options['tagline'],
		'url'         => home_url( '/' ),
		'logo'        => $options['logo'],
		'image'       => $options['og_image'],
		'inLanguage'  => 'nl-NL',
		'sameAs'      => $same,
	);

	if ( ! empty( $options['contact_email'] ) ) {
		$station['email'] = $options['contact_email'];
	}
	if ( ! empty( $options['contact_phone'] ) ) {
		$station['telephone'] = $options['contact_phone'];
	}

	echo '<script type="application/ld+json">' . wp_json_encode( $station, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . '</script>' . "\n";

	if ( is_singular( 'post' ) ) {
		$post    = get_queried_object();
		$article = array(
			'@context'        => 'https://schema.org',
			'@type'           => 'NewsArticle',
			'headline'        => get_the_title( $post ),
			'datePublished'   => get_the_date( 'c', $post ),
			'dateModified'    => get_the_modified_date( 'c', $post ),
			'mainEntityOfPage' => get_permalink( $post ),
			'author'          => array(
				'@type' => 'Organization',
				'name'  => $options['station_name'],
			),
			'publisher'       => array(
				'@type' => 'Organization',
				'name'  => $options['station_name'],
				'logo'  => array(
					'@type' => 'ImageObject',
					'url'   => $options['logo'],
				),
			),
		);
		if ( has_post_thumbnail( $post ) ) {
			$article['image'] = get_the_post_thumbnail_url( $post, 'full' );
		}
		echo '<script type="application/ld+json">' . wp_json_encode( $article, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . '</script>' . "\n";
	}
}
