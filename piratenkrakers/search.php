<?php
/**
 * Search results.
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
		<p class="pk-kicker"><?php esc_html_e( 'Zoeken', 'piratenkrakers' ); ?></p>
		<h1><?php echo esc_html( sprintf( __( 'Resultaten voor “%s”', 'piratenkrakers' ), get_search_query() ) ); ?></h1>
		<?php get_search_form(); ?>
	</header>
	<div class="pk-grid pk-grid--news">
		<?php
		if ( have_posts() ) :
			while ( have_posts() ) :
				the_post();
				get_template_part( 'template-parts/card-news' );
			endwhile;
		else :
			echo '<p class="pk-empty">Niets gevonden. Probeer een andere term.</p>';
		endif;
		?>
	</div>
	<?php pk_pagination(); ?>
</main>
<?php
get_footer();
