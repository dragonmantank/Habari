<?php // Do not delete these lines
	if ('comments.php' == basename($_SERVER['SCRIPT_FILENAME']))
		die (_t('Please do not load this page directly. Thanks!'));
	?>
<hr />
<div class="comments">
	<h4><span id="comments"><?php echo $post->comments->approved->count; ?> Responses to </span><?php echo $post->title; ?></h4>
	<div class="metalinks">
		<span class="commentsrsslink"><a href="/<?php echo $post->slug; ?>/atom">Feed for this Entry</a></span>
		<span class="trackbacklink"><a href="<?php echo $post->slug; ?>/trackback">Trackback Address</a></span>
	</div>
	<ol id="commentlist">
		<?php 
			if( $post->comments->approved->count ) :
				foreach ( $post->comments->approved as $comment ) : 
		?>
			<li id="comment-<?php echo $comment->id; ?>" class="comment">
			<a href="#comment-<?php echo $comment->id; ?>" class="counter" title="Permanent Link to this Comment"><?php echo $comment->id; ?></a>
			<span class="commentauthor">
				<a href="<?php echo $comment->url; ?>" rel="external"><?php echo $comment->name; ?></a>
			</span>

			<small class="comment-meta">
				<a href="#comment-<?php echo $comment->id; ?>" title="Time of this comment"><?php echo $comment->date; ?></a>									
			</small>

			<div class="comment-content">
				<?php echo Format::autop($comment->content); ?>
			</div>
			</li>
		<?php 
				endforeach;
			else:
				_e('<li>There are currently no comments.</li>');
			endif; ?>
	</ol>
	<?php
	if ( ! $post->info->comments_disabled ) :
	$commenter= User::commenter(); ?>
	<div class="comments">
		<h4 id="respond" class="reply">Leave a Reply</h4>
			<form action="<?php URL::out('comment', array('id'=>$post->id) ); ?>" method="post" id="commentform">
				<div id="comment-personaldetails">
					<p>
						<input type="text" name="name" id="name" value="<?php echo $commenter['name']; ?>" size="22" tabindex="1" />
						<label for="name"><small><strong>Name</strong></small></label>
					</p>
					<p>
						<input type="text" name="email" id="email" value="<?php echo $commenter['email']; ?>" size="22" tabindex="2" />
						<label for="email"><small><strong>Mail</strong> (will not be published) </small></label>
					</p>
					<p>
						<input type="text" name="url" id="url" value="<?php echo $commenter['url']; ?>" size="22" tabindex="3" />
						<label for="url"><small><strong>Website</strong></small></label>
					</p>
				</div>
				<p><textarea name="content" id="content" cols="100%" rows="10" tabindex="4"></textarea></p>
				<p>
					<input name="submit" type="submit" id="submit" tabindex="5" value="Submit" />
				</p>
				<div class="clear"></div>
			</form>
	</div>
	<?php endif; ?>
	<hr />
</div>
