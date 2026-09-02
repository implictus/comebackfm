<?php
/**
 * Seed pages, menu, DJs, shows and news on first activation.
 *
 * @package PiratenKrakers
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class PK_Demo_Content {

	public static function install(): void {
		if ( get_option( 'pk_demo_installed' ) ) {
			return;
		}

		$home_id     = self::page( 'Home', '', '', 'home' );
		$live_id     = self::page( 'Live', self::live_content(), 'templates/page-live.php', 'live' );
		$verzoek_id  = self::page( 'Verzoekjes', self::verzoek_intro(), 'templates/page-verzoekjes.php', 'verzoekjes' );
		$contact_id  = self::page( 'Contact', self::contact_content(), 'templates/page-contact.php', 'contact' );
		$news_id     = self::page( 'Nieuws', '', '', 'nieuws' );

		if ( $home_id ) {
			update_option( 'show_on_front', 'page' );
			update_option( 'page_on_front', $home_id );
		}
		if ( $news_id ) {
			update_option( 'page_for_posts', $news_id );
		}

		$djs   = self::djs();
		self::shows( $djs );
		self::posts();

		$items = array();
		if ( $home_id ) {
			$items[] = array( 'title' => 'Home', 'url' => home_url( '/' ), 'object' => 'page', 'id' => $home_id );
		}
		if ( $live_id ) {
			$items[] = array( 'title' => 'Live', 'url' => get_permalink( $live_id ), 'object' => 'page', 'id' => $live_id );
		}
		$items[] = array( 'title' => 'Programma', 'url' => home_url( '/programma/' ), 'object' => '', 'id' => 0 );
		$items[] = array( 'title' => "DJ's", 'url' => home_url( '/djs/' ), 'object' => '', 'id' => 0 );
		if ( $verzoek_id ) {
			$items[] = array( 'title' => 'Verzoekjes', 'url' => get_permalink( $verzoek_id ), 'object' => 'page', 'id' => $verzoek_id );
		}
		if ( $news_id ) {
			$items[] = array( 'title' => 'Nieuws', 'url' => get_permalink( $news_id ), 'object' => 'page', 'id' => $news_id );
		}
		if ( $contact_id ) {
			$items[] = array( 'title' => 'Contact', 'url' => get_permalink( $contact_id ), 'object' => 'page', 'id' => $contact_id );
		}

		self::create_menu( 'PiratenKrakers', 'primary', $items );
		self::create_menu(
			'PiratenKrakers footer',
			'footer',
			array(
				array( 'title' => 'Live', 'url' => $live_id ? get_permalink( $live_id ) : home_url( '/live/' ), 'id' => $live_id ),
				array( 'title' => 'Programma', 'url' => home_url( '/programma/' ), 'id' => 0 ),
				array( 'title' => 'Verzoekjes', 'url' => $verzoek_id ? get_permalink( $verzoek_id ) : home_url( '/verzoekjes/' ), 'id' => $verzoek_id ),
				array( 'title' => 'Contact', 'url' => $contact_id ? get_permalink( $contact_id ) : home_url( '/contact/' ), 'id' => $contact_id ),
			)
		);

		update_option( 'pk_demo_installed', 1 );
		flush_rewrite_rules();
	}

	protected static function page( string $title, string $content, string $template, string $slug ): int {
		$existing = get_page_by_path( $slug );
		if ( $existing instanceof WP_Post ) {
			if ( $template ) {
				update_post_meta( $existing->ID, '_wp_page_template', $template );
			}
			return (int) $existing->ID;
		}

		$id = wp_insert_post(
			array(
				'post_title'   => $title,
				'post_name'    => $slug,
				'post_status'  => 'publish',
				'post_type'    => 'page',
				'post_content' => $content,
			)
		);
		if ( is_wp_error( $id ) ) {
			return 0;
		}
		if ( $template ) {
			update_post_meta( $id, '_wp_page_template', $template );
		}
		return (int) $id;
	}

	/**
	 * @return array<string,int>
	 */
	protected static function djs(): array {
		$people = array(
			'nachtvlucht' => array(
				'name' => 'DJ Nachtvlucht',
				'role' => 'Avond- en nachtradio',
				'bio'  => "Als de snelweg leegloopt, gaat bij Nachtvlucht de knop omhoog. Warme piratenhits, verzoekjes van de nachtdienst en een stem die je tot de oprit thuis brengt.\n\nDraait sinds de late jaren in schuurtjes, studios en nu live op PiratenKrakers.nl.",
				'fav'  => 'Piratenhits, levenslied, nachtelijke polka',
				'alias'=> 'De stem van na tienen',
			),
			'draaibaas'   => array(
				'name' => 'De Draaibaas',
				'role' => 'Ochtend',
				'bio'  => "Koffie, claxons en de eerste kraker van de dag. De Draaibaas trapt de ether aan met feest, groeten en nummers die de werkbus laten meezingen.",
				'fav'  => 'Feest, Nederlandstalig, smartlappen met pit',
				'alias'=> 'Koning van de ochtend',
			),
			'achterhoek'  => array(
				'name' => 'DJ Achterhoek',
				'role' => 'Middag',
				'bio'  => "Vanuit de Achterhoek, voor het hele land. Accordeon, drums en een dosis nuchterheid. DJ Achterhoek houdt de middag gezellig zonder saai te worden.",
				'fav'  => 'Streekhits, feest, classic pirates',
				'alias'=> 'De streekkraker',
			),
			'mieke'       => array(
				'name' => 'Mieke van de Mixer',
				'role' => 'Verzoekjes',
				'bio'  => "Stuur je groeten in, Mieke draait ze. Verjaardagen, nachtdiensten, de buurman die jarig is — als het op de radio moet, komt het bij haar terecht.",
				'fav'  => 'Verzoekplaten, levenslied, golden pirates',
				'alias'=> 'De groetjesmachine',
			),
			'sterren'     => array(
				'name' => 'DJ Sterrennacht',
				'role' => 'Weekend nachten',
				'bio'  => "Zaterdagavond tot de zon. Sterrennacht mixt feest met nostalgie en laat de ether knetteren tot de kerken weer open gaan.",
				'fav'  => 'Feest, disco-piraten, nachtelijke krakers',
				'alias'=> 'Weekendvuur',
			),
			'vinyl'       => array(
				'name' => 'De Platendraaier',
				'role' => 'Vinylzondag',
				'bio'  => "Geen MP3-tjes, wel groeven. De Platendraaier tilt echte platen op de draaitafel en vertelt het verhaal erbij.",
				'fav'  => 'Vinyl, classics, rare parels',
				'alias'=> 'Meester van de groef',
			),
			'dennis'      => array(
				'name' => 'DJ Dennis',
				'role' => 'Ochtend',
				'bio'  => 'De vroege start. Koffie, files en de eerste krakers van de dag.',
				'fav'  => 'Ochtendhits, Nederlandstalig',
				'alias'=> 'De wekker van de ether',
			),
			'linda'       => array(
				'name' => 'DJ Linda',
				'role' => 'Voormiddag',
				'bio'  => 'Warm, helder en altijd een groet voor onderweg.',
				'fav'  => 'Piratenhits, levenslied',
				'alias'=> 'Stem van de voormiddag',
			),
			'patrick'     => array(
				'name' => 'DJ Patrick',
				'role' => 'Middag',
				'bio'  => 'De middagmix: verzoekjes, streekhits en een dosis volume.',
				'fav'  => 'Feest, middagkrakers',
				'alias'=> 'Middagmix',
			),
			'mark'        => array(
				'name' => 'DJ Mark',
				'role' => 'Drive time',
				'bio'  => 'Van werk naar huis, met de knoppen open.',
				'fav'  => 'Drive time, feest',
				'alias'=> 'De thuisrit',
			),
			'nightbeat'   => array(
				'name' => 'DJ Nightbeat',
				'role' => 'Non-stop nacht',
				'bio'  => 'Als de studio stilvalt, blijft Nightbeat draaien. Non-stop tot de zon.',
				'fav'  => 'Non-stop hits',
				'alias'=> 'De nachtploeg',
			),
		);

		$ids = array();
		foreach ( $people as $slug => $dj ) {
			$existing = get_page_by_path( $slug, OBJECT, 'pk_dj' );
			if ( $existing instanceof WP_Post ) {
				$ids[ $slug ] = $existing->ID;
				continue;
			}
			$id = wp_insert_post(
				array(
					'post_type'    => 'pk_dj',
					'post_status'  => 'publish',
					'post_title'   => $dj['name'],
					'post_name'    => $slug,
					'post_content' => $dj['bio'],
					'post_excerpt' => $dj['alias'],
				)
			);
			if ( is_wp_error( $id ) ) {
				continue;
			}
			update_post_meta( $id, 'pk_alias', $dj['alias'] );
			update_post_meta( $id, 'pk_role', $dj['role'] );
			update_post_meta( $id, 'pk_favorite_music', $dj['fav'] );
			$ids[ $slug ] = (int) $id;
		}
		return $ids;
	}

	/**
	 * @param array<string,int> $djs DJ ids.
	 */
	protected static function shows( array $djs ): void {
		$items = array(
			array( 'De Vroege Start', 'dennis', '06:00', '09:00', array( 1, 2, 3, 4, 5 ), 'Ochtendhits', 'Koffie, files en de eerste krakers.' ),
			array( 'Voormiddag Show', 'linda', '09:00', '12:00', array( 1, 2, 3, 4, 5 ), 'Piratenhits', 'Warm en helder, de hele voormiddag.' ),
			array( 'Middagmix', 'patrick', '12:00', '15:00', array( 1, 2, 3, 4, 5 ), 'Feest', 'Verzoekjes en volume in de middag.' ),
			array( 'Drive Time', 'mark', '15:00', '18:00', array( 1, 2, 3, 4, 5 ), 'Drive time', 'Van werk naar huis, knoppen open.' ),
			array( 'De Avondploeg', 'nachtvlucht', '18:00', '22:00', array( 1, 2, 3, 4, 5 ), 'Piratenhits', 'Warm, luid en vertrouwd. De klassieke avond van PiratenKrakers.' ),
			array( 'Nachtradio', 'nachtvlucht', '22:00', '02:00', array( 1, 2, 3, 4, 5 ), 'Nachtelijke krakers', 'Voor de nachtdienst, de chauffeurs en wie nog niet wil slapen.' ),
			array( 'Non Stop Hits', 'nightbeat', '02:00', '06:00', array( 1, 2, 3, 4, 5 ), 'Non-stop', 'De studio slaapt, de platen niet.' ),
			array( 'Vrijdagavond Feest', 'sterren', '20:00', '00:00', array( 5 ), 'Feest', 'Het weekend gaat open. Volume omhoog.' ),
			array( 'Zaterdag Krakers', 'sterren', '12:00', '18:00', array( 6 ), 'Feest', 'Middag vol piratenhits en verzoekjes.' ),
			array( 'Sterrennacht', 'sterren', '20:00', '03:00', array( 6 ), 'Nachtfeest', 'Tot de zon. Geen pardon.' ),
			array( 'Vinylzondag', 'vinyl', '10:00', '14:00', array( 7 ), 'Vinyl classics', 'Echte groeven, echte verhalen.' ),
			array( 'Zondagmiddag Groeten', 'mieke', '14:00', '18:00', array( 7 ), 'Verzoekjes', 'De zondagtafel: koffie, cake en jouw nummer.' ),
			array( 'Zondagavond', 'nachtvlucht', '18:00', '23:00', array( 7 ), 'Piratenhits', 'De week uitluiden zoals het hoort.' ),
		);

		foreach ( $items as $row ) {
			$slug = sanitize_title( $row[0] );
			if ( get_page_by_path( $slug, OBJECT, 'pk_show' ) ) {
				continue;
			}
			$id = wp_insert_post(
				array(
					'post_type'    => 'pk_show',
					'post_status'  => 'publish',
					'post_title'   => $row[0],
					'post_name'    => $slug,
					'post_content' => $row[6],
					'post_excerpt' => $row[5],
				)
			);
			if ( is_wp_error( $id ) ) {
				continue;
			}
			update_post_meta( $id, 'pk_dj_id', $djs[ $row[1] ] ?? 0 );
			update_post_meta( $id, 'pk_start', $row[2] );
			update_post_meta( $id, 'pk_end', $row[3] );
			update_post_meta( $id, 'pk_weekdays', $row[4] );
			update_post_meta( $id, 'pk_genre_text', $row[5] );
		}
	}

	protected static function posts(): void {
		$posts = array(
			array(
				'PiratenKrakers.nl is in de ether',
				"De knoppen staan aan. PiratenKrakers.nl is live: muziek uit het hart, zoals je die kent van de nachtelijke FM, nu op internet.\n\nVerzoekjes in, volume omhoog, groeten naar de nachtdienst.",
			),
			array(
				'Weekend: Sterrennacht tot de zon',
				"Zaterdagavond draait DJ Sterrennacht door tot de kerken weer open gaan. Feest, nostalgie en de nummers die je in geen enkele playlist vindt — behalve hier.\n\nZet de player vast klaar.",
			),
			array(
				'Stuur je verzoekje in',
				"Jarig, nachtdienst, of gewoon zin in dat ene nummer? Mieke van de Mixer draait verzoekjes elke middag en zondagmiddag.\n\nNaam, plaats, nummer. Wij doen de rest.",
			),
		);
		foreach ( $posts as $i => $row ) {
			$slug = sanitize_title( $row[0] );
			if ( get_page_by_path( $slug, OBJECT, 'post' ) ) {
				continue;
			}
			wp_insert_post(
				array(
					'post_type'    => 'post',
					'post_status'  => 'publish',
					'post_title'   => $row[0],
					'post_name'    => $slug,
					'post_content' => $row[1],
					'post_date'    => gmdate( 'Y-m-d H:i:s', time() - ( $i * 86400 ) ),
				)
			);
		}
	}

	protected static function create_menu( string $name, string $location, array $items ): void {
		$menu_id = wp_create_nav_menu( $name );
		if ( is_wp_error( $menu_id ) ) {
			return;
		}
		foreach ( $items as $item ) {
			$args = array(
				'menu-item-title'  => $item['title'],
				'menu-item-url'    => $item['url'],
				'menu-item-status' => 'publish',
				'menu-item-type'   => ! empty( $item['id'] ) ? 'post_type' : 'custom',
			);
			if ( ! empty( $item['id'] ) ) {
				$args['menu-item-object']    = 'page';
				$args['menu-item-object-id'] = $item['id'];
				$args['menu-item-type']      = 'post_type';
			}
			wp_update_nav_menu_item( $menu_id, 0, $args );
		}
		$locations              = get_theme_mod( 'nav_menu_locations', array() );
		$locations[ $location ] = $menu_id;
		set_theme_mod( 'nav_menu_locations', $locations );
	}

	protected static function verzoek_intro(): string {
		return '';
	}

	protected static function live_content(): string {
		return "Luister live naar PiratenKrakers.nl. De player hierboven is dezelfde als onderaan de site — één stream, geen gedoe.";
	}

	protected static function contact_content(): string {
		return "Wil je een groet, een samenwerkingsvraag of een technische melding? Stuur een bericht via het formulier of mail ons. We zijn een piratenstation, geen callcenter — maar we lezen alles.";
	}

}
