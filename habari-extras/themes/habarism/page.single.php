<?php include 'header.php'; ?>
<?php include 'sidebar.php'; ?>
	<div id="content">
		<div class="post" id="post-<?php echo $post->id; ?>">
			<div class="post_head">
				<h2><a href="<?php echo $post->permalink; ?>" title="<?php echo $post->title; ?>"><?php echo $post->title_out; ?></a></h2>
			</div>
			<div class="post_content">
				<?php echo $post->content_out; ?>
			</div>
		</div>
	</div>
<?php include 'footer.php'; ?>