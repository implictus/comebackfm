<?php
/**
 * News index (page_for_posts).
 *
 * @package PiratenKrakers
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
get_header();
?>
<main class="pk-main pk-main--archive" id="main">
	<header class="pk-pagehead">
		<p class="pk-kicker"><?php esc_html_e( 'Studiojournaal', 'piratenkrakers' ); ?></p>
		<h1><?php echo esc_html( get_the_title( get_option( 'page_for_posts' ) ) ?: __( 'Nieuws', 'piratenkrakers' ) ); ?></h1>
	</header>
	<div class="pk-grid pk-grid--news">
		<?php
		if ( have_posts() ) :
			while ( have_posts() ) :
				the_post();
				get_template_part( 'template-parts/card-news' );
			endwhile;
		else :
			echo '<p class="pk-empty">Nog geen nieuws.</p>';
		endif;
		?>
	</div>
	<?php pk_pagination(); ?>
</main>
<?php
get_footer();
