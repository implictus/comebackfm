<?php
/**
 * Default page.
 *
 * @package PiratenKrakers
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
get_header();
?>
<main class="pk-main" id="main">
	<?php
	while ( have_posts() ) :
		the_post();
		?>
		<article class="pk-article">
			<header class="pk-pagehead">
				<h1><?php the_title(); ?></h1>
			</header>
			<div class="pk-prose"><?php the_content(); ?></div>
		</article>
	<?php endwhile; ?>
</main>
<?php
get_footer();
