<?php
/**
 * The template for displaying Comments.
 *
 * If the current post is protected by a password and
 * the visitor has not yet entered the password we will
 * return early without loading the comments.
 */
if ( post_password_required() )
	return; ?>
<div class="clearfix"></div>
<?php if ( comments_open() || get_comments_number() ) {?>
<div id="comments" class="comments-area">
	<?php if ( have_comments() ) : 	?>
    <h2 class="comments-title">
    	<?php
			$comments_number = get_comments_number();
			if ( '1' === $comments_number ) {
				/* translators: %s: post title */
				printf( esc_html(_x( 'One Reply to &ldquo;%s&rdquo;', 'comments title', 'mywiki' )), get_the_title() );
			} else {
				printf(
					/* translators: 1: number of comments, 2: post title */
					esc_html(_nx(
						'%1$s One thought on - %2$s',
						'%1$s thoughts on - %2$s',
						$comments_number,
						'comments title',
						'mywiki'
					)),
					esc_html(number_format_i18n( $comments_number )),
					get_the_title()
				);
			}
			?>		
	</h2>
    <ul><?php wp_list_comments( array( 'callback' => 'mywiki_comment', 'style' => 'ul' ) ); ?></ul>
       <?php paginate_comments_links();
       endif; // have_comments()
	$mywiki_args = array('comment_notes_after'=>'',
				  'comment_notes_before'=>'',
				  'title_reply' => __('LEAVE A COMMENT','mywiki'),
				  'label_submit' => __('Submit Comment','mywiki'),
				  'fields' => apply_filters( 'comment_form_default_fields', array(
						'author' =>
						  '<p class="comment-form-author">' .						  
						  '<input id="author" name="author" type="text" placeholder="'.__('Your Name','mywiki').'*" value="' . esc_attr( $commenter['comment_author'] ) .
						  '" size="30" /></p>',					
						'email' =>
						  '<p class="comment-form-email">'.
						  '<input id="email" name="email" type="text" placeholder="'.__('E-Mail','mywiki').'" value="' . esc_attr(  $commenter['comment_author_email'] ) .
						  '" size="30" /></p>'
						  )),
						  'comment_field' => '<p>' .
						  '<textarea id="comment" name="comment" placeholder="'.__('Comment','mywiki').'*" cols="45" rows="8" aria-required="true"></textarea>' .
						  '</p>',						 
    'comment_notes_after' => '',
				  ); ?>
	<?php comment_form($mywiki_args); ?>
</div><!-- #comments .comments-area -->
<?php } ?>