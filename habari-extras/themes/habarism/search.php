<?php include 'header.php'; ?>
<?php include 'sidebar.php'; ?>
	<div id="content" class="with_title">
		<h3 class="page_title">Search results for <?php echo htmlspecialchars( $criteria ); ?></h3>
		<?php
		if( count( $posts ) != 0 ) {
		foreach ( $posts as $post ) { 
		?>
		<div class="post<?php if($post->status == 1) { echo ' draft'; } ?>" id="post-<?php echo $post->id; ?>">
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
				<?php echo _n( 'Comment', 'Comments', $post->comments->approved->count ); ?></a> on this post.</p>
			</div>
		</div>
		<?php } ?>
		<div class="page_navigation">
			<p><?php $theme->prev_page_link(); ?> <?php $theme->page_selector( null, array( 'leftSide' => 2, 'rightSide' => 2 ) ); ?> <?php $theme->next_page_link(); ?></p>
		</div>
		<?php } else { ?>
		<div class="post">
			<div class="post_head">
				<h2>No results</h2>
			</div>
			<div class="post_content">
				<p>No search results found</p>
			</div>
		</div>	
		<?php } ?>
	</div>
<?php include 'footer.php'; ?>