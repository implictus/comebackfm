<?php
/**
 * Template Name: Verzoekjes
 *
 * @package PiratenKrakers
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
get_header();
?>
<main class="pk-main pk-main--request" id="main">
	<header class="pk-pagehead">
		<p class="pk-kicker"><?php esc_html_e( 'Groeten & platen', 'piratenkrakers' ); ?></p>
		<h1><?php the_title(); ?></h1>
		<p class="pk-lede"><?php esc_html_e( 'Zet je naam, plaats en nummer erin. Misschien draait Mieke hem zo — of Nachtvlucht in de kleine uurtjes.', 'piratenkrakers' ); ?></p>
	</header>
	<form class="pk-form pk-form--request" id="pk-request-form" novalidate>
		<?php wp_nonce_field( 'pk_request', '_wpnonce' ); ?>
		<div class="pk-hp" aria-hidden="true">
			<label>Website <input type="text" name="website" tabindex="-1" autocomplete="off"></label>
		</div>
		<div class="pk-form-grid">
			<label>Naam *
				<input type="text" name="name" required maxlength="80" autocomplete="name">
			</label>
			<label>Plaats *
				<input type="text" name="place" required maxlength="80" autocomplete="address-level2">
			</label>
			<label class="pk-span-2">Verzoeknummer *
				<input type="text" name="song" required maxlength="160" placeholder="Artiest — Titel">
			</label>
			<label class="pk-span-2">Bericht / groet
				<textarea name="message" rows="4" maxlength="1000" placeholder="Groetjes aan de nachtdienst, gefeliciteerd met…"></textarea>
			</label>
			<label>Telefoon <span class="pk-opt">(optioneel)</span>
				<input type="tel" name="phone" maxlength="30" autocomplete="tel">
			</label>
			<label class="pk-check pk-span-2">
				<input type="checkbox" name="consent" value="1" required>
				Ik geef toestemming om mijn naam, plaats en verzoekje op de radio en website te gebruiken.
			</label>
		</div>
		<p class="pk-form-status" data-pk-form-status role="status"></p>
		<button class="pk-btn pk-btn-gold" type="submit"><?php esc_html_e( 'Verzoekje versturen', 'piratenkrakers' ); ?></button>
	</form>
</main>
<?php
get_footer();
