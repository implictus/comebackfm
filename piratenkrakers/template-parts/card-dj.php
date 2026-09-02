<?php
/**
 * DJ card.
 *
 * @package PiratenKrakers
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$role  = get_post_meta( get_the_ID(), 'pk_role', true );
$alias = get_post_meta( get_the_ID(), 'pk_alias', true );
$initials = mb_strtoupper( mb_substr( get_the_title(), 0, 1 ) );
?>
<article <?php post_class( 'pk-card pk-card-dj' ); ?>>
	<a class="pk-card-media pk-card-media--dj" href="<?php the_permalink(); ?>">
		<?php if ( has_post_thumbnail() ) : ?>
			<?php the_post_thumbnail( 'pk-dj', array( 'loading' => 'lazy', 'decoding' => 'async' ) ); ?>
		<?php else : ?>
			<span class="pk-avatar-fallback" aria-hidden="true"><?php echo esc_html( $initials ); ?></span>
		<?php endif; ?>
	</a>
	<div class="pk-card-body">
		<?php if ( $role ) : ?><p class="pk-card-meta"><?php echo esc_html( $role ); ?></p><?php endif; ?>
		<h3 class="pk-card-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
		<?php if ( $alias ) : ?><p class="pk-card-excerpt"><?php echo esc_html( $alias ); ?></p><?php endif; ?>
	</div>
</article>
