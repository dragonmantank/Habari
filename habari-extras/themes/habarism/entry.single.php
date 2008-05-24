<?php include 'header.php'; ?>
<?php include 'sidebar.php'; ?>
	<div id="content">
		<div class="post" id="post-<?php echo $post->id; ?>">
			<div class="post_head">
				<h2><a href="<?php echo $post->permalink; ?>" title="<?php echo $post->title; ?>"><?php echo $post->title_out; ?></a></h2>
				<p class="post_head_meta"><?php echo $post->author->info->displayname; ?> &middot; <?php echo $post->pubdate_out; ?></p>
			</div>
			<div class="post_content">
				<?php echo $post->content_out; ?>
			</div>
			<div class="post_meta">
				<p>This entry was posted on <span class="date"><?php echo $post->pubdate_out; ?></span>
				<?php if ( is_array( $post->tags ) ) { ?>and is filed under <?php echo $post->tags_out; ?><?php } ?>.
				There is <a href="<?php echo $post->permalink; ?>#comments" title="Comments to this post"><?php echo $post->comments->approved->count; ?>
				<?php echo _n( 'Comment', 'Comments', $post->comments->approved->count ); ?></a> on this post. You can follow any responses to this entry through the <a href="<?php echo $post->comment_feed_link; ?>">comment feed</a>.</p>
			</div>
		</div>
		<?php include 'comments.php'; ?>
	</div>
<?php include 'footer.php'; ?>