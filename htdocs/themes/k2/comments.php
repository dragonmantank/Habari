<?php // Do not delete these lines
	if ('comments.php' == basename($_SERVER['SCRIPT_FILENAME']))
		die (__('Please do not load this page directly. Thanks!'));
	?>
<hr />
<div class="comments">
	<h4><span id="comments">Responses to </span><?php echo $post->title; ?></h4>
	<div class="metalinks">
		<span class="commentsrsslink"><a href="/<?php echo $post->slug; ?>/feed">Feed for this Entry</a></span>
	</div>
	<ol id="commentlist">
		<?php foreach ( $post->comments as $comment ) { ?>
			<li id="comment<?php echo $comment->id; ?>" class="comment">
			<a href="#comment-<?php echo $comment->id; ?>" class="counter" title="Permanent Link to this Comment"><?php echo $comment->id; ?></a>
			<span class="commentauthor">
				<a href="<?php echo $comment->url; ?>" rel="external"><?php echo $comment->name; ?></a>
			</span>

			<small class="comment-meta">
				<a href="#comment-<?php echo $comment->id; ?>" title="Time of this comment"><?php echo $comment->date; ?></a>									
			</small>

			<div class="comment-content">
				<?php echo $comment->content; ?>
			</div>
			</li>
		<?php } ?>
	</ol>
</div>
