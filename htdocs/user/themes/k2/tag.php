<?php include 'header.php'; ?>
<!-- tag -->
<div class="content">
	<div id="primary">
		<div id="primarycontent" class="hfeed">
			<?php foreach ( $posts as $post ) { ?>
				<div id="post-<?php echo $post->id; ?>">
					<div class="entry-head">
						<h3 class="entry-title"><a href="<?php echo $post->permalink; ?>" title="<?php echo $post->title; ?>"><?php echo $post->title_out; ?></a></h3>
						<small class="entry-meta">
							<span class="chronodata"><abbr class="published"><?php echo $post->pubdate_out; ?></abbr></span>
							<span class="commentslink"><a href="<?php echo $post->permalink; ?>" title="Comments on this post"><?php echo $post->comments->approved->count; ?> <?php echo _n( 'Comment', 'Comments', $post->comments->approved->count ); ?></a></span>
							<?php if ( $user ) { ?>
							<span class="entry-edit"><a href="<?php URL::out( 'admin', 'page=publish&slug=' . $post->slug); ?>" title="Edit post">Edit</a></span>
							<?php } ?>
							<?php if ( is_array( $post->tags ) ) { ?>
							<span class="entry-tags"><?php echo $post->tags_out; ?></span>
							<?php } ?>
						</small>
					</div>
					<div class="entry-content">
						<?php echo $post->content_out; ?>
					</div>
				</div>
			<?php } ?>
		</div>
		<div id="page-selector">
			<strong>Page:</strong> <?php echo Utils::page_selector( isset( $page ) ? $page : 1, Utils::archive_pages( $posts->count_all() ), 'display_posts_by_tag', array( 'tag' => $tag ) ); ?>
		</div>
	</div>
	<hr>
	<div class="secondary">
		<div id="search">
			<h2>Search</h2>
			<?php include 'searchform.php'; ?>
		</div>	
		<div class="sb-about">
			<h2>About</h2>
			<p><?php Options::out('about'); ?></p>
		</div>
		<div class="sb-user">
			<h2>User</h2>
			<?php include 'loginform.php'; ?>
		</div>	
	</div>
	<div class="clear"></div>
</div>
<?php include 'footer.php'; ?>
