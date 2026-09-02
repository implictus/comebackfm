<?php
/**
 * Generic archive.
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
		<p class="pk-kicker"><?php esc_html_e( 'Archief', 'piratenkrakers' ); ?></p>
		<h1><?php the_archive_title(); ?></h1>
		<?php the_archive_description( '<p class="pk-lede">', '</p>' ); ?>
	</header>
	<div class="pk-grid pk-grid--news">
		<?php
		if ( have_posts() ) :
			while ( have_posts() ) :
				the_post();
				get_template_part( 'template-parts/card-news' );
			endwhile;
		endif;
		?>
	</div>
	<?php pk_pagination(); ?>
</main>
<?php
get_footer();
