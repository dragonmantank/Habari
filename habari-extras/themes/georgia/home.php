<?php include 'header.php'; ?>
<?php include 'sidebar.php'; ?>
<?php foreach ( $posts as $post ) { ?>
	<div class="post<?php if($post->status == 1) { echo ' draft'; } ?>" id="post-<?php echo $post->id; ?>">
		<p class="post_date"><?php echo $post->pubdate_out; ?></p>
		<h2 class="post_title"><a href="<?php echo $post->permalink; ?>" title="<?php echo $post->title; ?>"><?php echo $post->title_out; ?></a> <em>by</em> <?php echo $post->author->info->displayname; ?></h2>
		<div class="post_content">
			<?php $content = preg_replace('%^\s*<p>%i', '<p class="first_paragraph">', $post->content_out, 1); echo $content; ?>
		</div>
		<p class="post_meta"><?php if ( is_array( $post->tags ) ) { ?>Tags: <?php echo $post->tags_out; ?> | <?php } ?><a href="<?php echo $post->permalink; ?>#comments" title="Comments to this post"><?php echo $post->comments->approved->count; ?>
		<?php echo _n( 'Comment', 'Comments', $post->comments->approved->count ); ?></a></p>
	</div>
	<?php } ?>
	<div class="page_navigation">
		<p><?php $theme->prev_page_link(); ?> <?php $theme->page_selector( null, array( 'leftSide' => 2, 'rightSide' => 2 ) ); ?> <?php $theme->next_page_link(); ?></p>
	</div>
<?php include 'footer.php'; ?>
