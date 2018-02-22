<?php
/**
 * The template for displaying comments.
 *
 * The area of the page that contains both current comments
 * and the comment form.
 *
 * @package thim
 */
/*
 * If the current post is protected by a password and
 * the visitor has not yet entered the password we will
 * return early without loading the comments.
 */
if ( post_password_required() ) {
	return;
}
?>

<div id="comments" class="comments-area">

	<?php // You can start editing here -- including this comment!  ?>

	<?php if ( have_comments() ) : ?>
		<h2 class="comment-reply-title">
			<?php
			echo esc_attr( get_comments_number() );
			if ( get_comments_number() != '1' ) {
				echo _e( ' comments', 'thim' );
			} else {
				echo _e( ' comment', 'thim' );
			}
			?>
		</h2>

		<?php if ( get_comment_pages_count() > 1 && get_option( 'page_comments' ) ) : // are there comments to navigate through  ?>
			<nav id="comment-nav-above" class="comment-navigation" role="navigation">
				<h3 class="screen-reader-text"><?php _e( 'Comment navigation', 'thim' ); ?></h3>
				<div class="nav-previous"><?php previous_comments_link( __( '&larr; Older Comments', 'thim' ) ); ?></div>
				<div class="nav-next"><?php next_comments_link( __( 'Newer Comments &rarr;', 'thim' ) ); ?></div>
			</nav><!-- #comment-nav-above -->
		<?php endif; // check for comment navigation  ?>

		<ol class="comment-list">
			<?php wp_list_comments( 'style=li&&type=comment&avatar_size=80&callback=thim_comment' ); ?>
		</ol><!-- .comment-list -->

		<?php if ( get_comment_pages_count() > 1 && get_option( 'page_comments' ) ) : // are there comments to navigate through  ?>
			<nav id="comment-nav-below" class="comment-navigation" role="navigation">
				<h3 class="screen-reader-text"><?php _e( 'Comment navigation', 'thim' ); ?></h3><div class="nav-previous"><?php previous_comments_link( __( '&larr; Older Comments', 'thim' ) ); ?></div>
				<div class="nav-next"><?php next_comments_link( __( 'Newer Comments &rarr;', 'thim' ) ); ?></div>
			</nav><!-- #comment-nav-below -->
		<?php endif; // check for comment navigation  ?>

	<?php endif; // have_comments() ?>

	<?php
	// If comments are closed and there are comments, let's leave a little note, shall we?
	if ( !comments_open() && '0' != get_comments_number() && post_type_supports( get_post_type(), 'comments' ) ) :
		?>
		<p class="no-comments"><?php _e( 'Comments are closed.', 'thim' ); ?></p>
	<?php endif; ?>
	<?php $comment_args = array( 'title_reply'          => __('LEAVE A COMMENT:','thim'),
								 'id_form'              => 'commentform',
								 'id_submit'            => 'submit',
								 'class_submit'         => 'submit btn hide',
								 'name_submit'          => 'submit',
								 'title_reply_to'       => __( 'Reply to %s', 'thim' ),
								 'cancel_reply_link'    => __( 'Cancel Reply', 'thim' ),
								 'label_submit'         => __( 'Send', 'thim' ),
								 'data-toggle'          => 'validator',
								 'comment_notes_before' => '',
								 'fields'               => apply_filters( 'comment_form_default_fields', array(
									 'author' => '<p class="comment-form-author col-md-6 col-sm-12"> <input class="form-control" id="author" name="author" type="text" placeholder="' . __( 'Your name (required)', 'thim' ) . '" value="' . esc_attr( $commenter['comment_author'] ) . '" size="30" /></p>',
									 'email'  => '<p class="comment-form-email col-md-6 col-sm-12"> <input class="form-control" id="email" name="email" type="email" placeholder="' . __( 'Your e-mail (required)', 'thim' ) . '" value="' . esc_attr( $commenter['comment_author_email'] ) . '" size="30" /></p>',
									 'url'    => '' ) ),
								 'comment_field'        => '<p class="col-sm-12"> <textarea class="form-control" id="comment" name="comment" placeholder="' . __( 'Your comment (required)', 'thim' ) . '" cols="45" rows="8" aria-required="true"></textarea>' .
									 '</p>',
								 'comment_notes_after'  => '',

	);
	echo '<div class="row">';
	function so_comment_button() {
		echo '<span class="submit-comment"><input name="submit" class="button btn" type="submit" value="' . __( 'Send', 'thim' ) . '" /><span class="arrow"><svg xmlns="http://www.w3.org/2000/svg" width="36" height="12" viewBox="-30 0 52 12"><path fill="#fff " d="M22 6l-6-6v5h-46v2h46v5l6-6z"></path></svg></span></span>';

	}

	add_action( 'comment_form', 'so_comment_button' );
	comment_form( $comment_args );
	echo '</div>';
	?>

</div><!-- #comments -->
