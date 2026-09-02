<?php
/**
 * Fallback index.
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
		<h1 class="pk-pagehead-title"><?php echo esc_html( wp_get_document_title() ); ?></h1>
	</header>
	<div class="pk-grid pk-grid--news">
		<?php if ( have_posts() ) : ?>
			<?php
			while ( have_posts() ) :
				the_post();
				get_template_part( 'template-parts/card-news' );
			endwhile;
			?>
		<?php else : ?>
			<p class="pk-empty"><?php esc_html_e( 'Niets gevonden.', 'piratenkrakers' ); ?></p>
		<?php endif; ?>
	</div>
	<?php pk_pagination(); ?>
</main>
<?php
get_footer();
