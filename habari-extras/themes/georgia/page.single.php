<?php include 'header.php'; ?>
<?php include 'sidebar.php'; ?>
	<div class="post" id="post-<?php echo $post->id; ?>">
		<h2 class="post_title"><a href="<?php echo $post->permalink; ?>" title="<?php echo $post->title; ?>"><?php echo $post->title_out; ?></a></h2>
		<div class="post_content">
			<?php $content = preg_replace('%^\s*<p>%i', '<p class="first_paragraph">', $post->content_out, 1); echo $content; ?>
		</div>
	</div>
<?php include 'footer.php'; ?>