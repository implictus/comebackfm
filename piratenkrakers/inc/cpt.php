<?php
/**
 * Custom post types and taxonomies.
 *
 * @package PiratenKrakers
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register CPTs.
 */
function pk_register_cpts(): void {
	register_post_type(
		'pk_dj',
		array(
			'labels' => array(
				'name'               => __( "DJ's", 'piratenkrakers' ),
				'singular_name'      => __( 'DJ', 'piratenkrakers' ),
				'add_new'            => __( 'Nieuwe DJ', 'piratenkrakers' ),
				'add_new_item'       => __( 'DJ toevoegen', 'piratenkrakers' ),
				'edit_item'          => __( 'DJ bewerken', 'piratenkrakers' ),
				'new_item'           => __( 'Nieuwe DJ', 'piratenkrakers' ),
				'view_item'          => __( 'DJ bekijken', 'piratenkrakers' ),
				'search_items'       => __( "DJ's zoeken", 'piratenkrakers' ),
				'not_found'          => __( 'Geen DJ’s gevonden', 'piratenkrakers' ),
				'all_items'          => __( "Alle DJ's", 'piratenkrakers' ),
			),
			'public'             => true,
			'show_in_menu'       => false,
			'show_in_rest'       => true,
			'has_archive'        => 'djs',
			'rewrite'            => array( 'slug' => 'dj' ),
			'menu_icon'          => 'dashicons-microphone',
			'supports'           => array( 'title', 'editor', 'thumbnail', 'excerpt' ),
			'capability_type'    => 'post',
		)
	);

	register_post_type(
		'pk_show',
		array(
			'labels' => array(
				'name'               => __( "Programma's", 'piratenkrakers' ),
				'singular_name'      => __( 'Programma', 'piratenkrakers' ),
				'add_new'            => __( 'Nieuw programma', 'piratenkrakers' ),
				'add_new_item'       => __( 'Programma toevoegen', 'piratenkrakers' ),
				'edit_item'          => __( 'Programma bewerken', 'piratenkrakers' ),
				'all_items'          => __( "Alle programma's", 'piratenkrakers' ),
			),
			'public'             => true,
			'show_in_menu'       => false,
			'show_in_rest'       => true,
			'has_archive'        => 'programma',
			'rewrite'            => array( 'slug' => 'show' ),
			'menu_icon'          => 'dashicons-calendar-alt',
			'supports'           => array( 'title', 'editor', 'thumbnail', 'excerpt' ),
			'capability_type'    => 'post',
		)
	);

	register_post_type(
		'pk_request',
		array(
			'labels' => array(
				'name'               => __( 'Verzoekjes', 'piratenkrakers' ),
				'singular_name'      => __( 'Verzoekje', 'piratenkrakers' ),
				'edit_item'          => __( 'Verzoekje bekijken', 'piratenkrakers' ),
				'all_items'          => __( 'Alle verzoekjes', 'piratenkrakers' ),
			),
			'public'             => false,
			'show_ui'            => true,
			'show_in_menu'       => false,
			'show_in_rest'       => false,
			'has_archive'        => false,
			'supports'           => array( 'title', 'editor' ),
			'capability_type'    => 'post',
			'map_meta_cap'       => true,
		)
	);

	register_taxonomy(
		'pk_genre',
		array( 'pk_show' ),
		array(
			'labels' => array(
				'name'          => __( 'Genres', 'piratenkrakers' ),
				'singular_name' => __( 'Genre', 'piratenkrakers' ),
			),
			'public'            => true,
			'show_admin_column' => true,
			'show_in_rest'      => true,
			'hierarchical'      => false,
			'rewrite'           => array( 'slug' => 'genre' ),
		)
	);
}
add_action( 'init', 'pk_register_cpts' );
