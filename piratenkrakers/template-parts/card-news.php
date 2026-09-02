<?php
/**
 * News card.
 *
 * @package PiratenKrakers
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<article <?php post_class( 'pk-card pk-card-news' ); ?>>
	<a class="pk-card-media" href="<?php the_permalink(); ?>">
		<?php if ( has_post_thumbnail() ) : ?>
			<?php the_post_thumbnail( 'pk-card', array( 'loading' => 'lazy', 'decoding' => 'async' ) ); ?>
		<?php else : ?>
			<img src="<?php echo esc_url( pk_asset( 'img/hero-studio.jpg' ) ); ?>" alt="" loading="lazy" decoding="async">
		<?php endif; ?>
	</a>
	<div class="pk-card-body">
		<p class="pk-card-meta"><time datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>"><?php echo esc_html( get_the_date() ); ?></time>
			<?php
			$cats = get_the_category();
			if ( $cats ) {
				echo ' · ' . esc_html( $cats[0]->name );
			}
			?>
		</p>
		<h3 class="pk-card-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
		<p class="pk-card-excerpt"><?php echo esc_html( wp_trim_words( wp_strip_all_tags( get_the_excerpt() ), 22 ) ); ?></p>
	</div>
</article>
