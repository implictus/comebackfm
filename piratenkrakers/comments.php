<?php
/**
 * Comments.
 *
 * @package PiratenKrakers
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
if ( post_password_required() ) {
	return;
}
?>
<section class="pk-comments" id="comments">
	<?php if ( have_comments() ) : ?>
		<h2><?php esc_html_e( 'Reacties', 'piratenkrakers' ); ?></h2>
		<ol class="pk-comment-list">
			<?php
			wp_list_comments(
				array(
					'style'      => 'ol',
					'short_ping' => true,
					'avatar_size'=> 48,
				)
			);
			?>
		</ol>
	<?php endif; ?>
	<?php
	comment_form(
		array(
			'title_reply'          => __( 'Reageer', 'piratenkrakers' ),
			'label_submit'         => __( 'Plaats reactie', 'piratenkrakers' ),
			'comment_notes_before' => '',
		)
	);
	?>
</section>
