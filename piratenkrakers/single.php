<?php
/**
 * News article.
 *
 * @package PiratenKrakers
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
get_header();
the_post();
$related = get_posts(
	array(
		'post_type'      => 'post',
		'posts_per_page' => 3,
		'post__not_in'   => array( get_the_ID() ),
		'category__in'   => wp_list_pluck( get_the_category(), 'term_id' ),
	)
);
if ( ! $related ) {
	$related = get_posts( array( 'post_type' => 'post', 'posts_per_page' => 3, 'post__not_in' => array( get_the_ID() ) ) );
}
?>
<main class="pk-main pk-main--single" id="main">
	<article <?php post_class( 'pk-article' ); ?>>
		<header class="pk-pagehead">
			<p class="pk-kicker"><?php echo esc_html( get_the_date() ); ?>
				<?php
				$cats = get_the_category();
				if ( $cats ) {
					echo ' · ' . esc_html( $cats[0]->name );
				}
				?>
			</p>
			<h1><?php the_title(); ?></h1>
		</header>
		<?php if ( has_post_thumbnail() ) : ?>
			<figure class="pk-article-hero"><?php the_post_thumbnail( 'pk-hero' ); ?></figure>
		<?php endif; ?>
		<div class="pk-prose"><?php the_content(); ?></div>
		<?php comments_template(); ?>
	</article>
	<?php if ( $related ) : ?>
		<section class="pk-section">
			<div class="pk-section-head">
				<h2><?php esc_html_e( 'Meer nieuws', 'piratenkrakers' ); ?></h2>
			</div>
			<div class="pk-grid pk-grid--news">
				<?php
				foreach ( $related as $post ) {
					setup_postdata( $GLOBALS['post'] = $post ); // phpcs:ignore
					get_template_part( 'template-parts/card-news' );
				}
				wp_reset_postdata();
				?>
			</div>
		</section>
	<?php endif; ?>
</main>
<?php
get_footer();
