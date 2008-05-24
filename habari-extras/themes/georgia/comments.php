<?php // Do not delete these lines
if ( ! defined('HABARI_PATH' ) ) { die( _t('Please do not load this page directly. Thanks!') ); }
?>
	<div class="comments">
		<h3><?php echo $post->comments->moderated->count; ?> Responses to <em><?php echo $post->title; ?></em></h3>
   		<ol id="commentlist">
			<?php 
			if ( $post->comments->moderated->count ) {
				foreach ( $post->comments->moderated as $comment ) {
					$class= 'class="comment';
					if ( $comment->status == Comment::STATUS_UNAPPROVED ) {
						$class.= ' unapproved';
					}
					$class.= '"';
					?>
     						<li id="comment-<?php echo $comment->id; ?>" <?php echo $class; ?>>
								<h2 class="comment_author"><a href="<?php echo $comment->url; ?>" rel="external"><?php echo $comment->name; ?></a> <em>at</em> <a href="#comment-<?php echo $comment->id; ?>" title="Time of this comment"><?php echo $comment->date; ?></a></h2>
      							<div class="comment_content">
       								<?php echo $comment->content_out; ?>
								</div>
     						</li>

					<?php 
					}
				}
			else { ?>
     				<li><?php _e('There are currently no comments.'); ?></li>
			<?php } ?>
    		</ol>
<?php if ( ! $post->info->comments_disabled ) { include_once( 'commentform.php' ); } ?>    