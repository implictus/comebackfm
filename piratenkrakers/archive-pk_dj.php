<?php
/**
 * DJ overview.
 *
 * @package PiratenKrakers
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
get_header();
?>
<main class="pk-main" id="main">
	<header class="pk-pagehead">
		<p class="pk-kicker"><?php esc_html_e( 'De knoppen', 'piratenkrakers' ); ?></p>
		<h1><?php esc_html_e( "DJ's", 'piratenkrakers' ); ?></h1>
		<p class="pk-lede"><?php esc_html_e( 'Mensen achter de mixer. Geen algoritme, wel een stem.', 'piratenkrakers' ); ?></p>
	</header>
	<div class="pk-grid pk-grid--djs">
		<?php
		if ( have_posts() ) :
			while ( have_posts() ) :
				the_post();
				get_template_part( 'template-parts/card-dj' );
			endwhile;
		else :
			echo '<p class="pk-empty">Nog geen DJ’s. Voeg ze toe via PiratenKrakers → DJ’s.</p>';
		endif;
		?>
	</div>
	<?php pk_pagination(); ?>
</main>
<?php
get_footer();
