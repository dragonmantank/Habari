<?php $theme->display ( 'header' ); ?>
<!--begin content-->
	<div id="content">
		<!--begin primary content-->
		<div id="primaryContent">
			<!--begin loop-->
			<h2>Results for search of "<?php echo htmlspecialchars( $criteria ); ?>"</h2>
			<?php if (isset($post)) : ?>
<?php foreach ( $posts as $post ): ?>
				<div id="post-<?php echo $post->id; ?>" class="<?php echo $post->statusname; ?>">
						<h2><a href="<?php echo $post->permalink; ?>" title="<?php echo $post->title; ?>"><?php echo $post->title_out; ?></a></h2>
					<div class="entry">
					<?php echo $post->pubdate_out; ?> -	<?php echo $post->content_excerpt; ?>
					</div>
					<div class="entryMeta">
						
						<?php if ( is_array( $post->tags ) ) { ?>
						<div class="tags">Tagged: <?php echo $post->tags_out; ?></div>
						<?php } ?>
						<div class="commentCount"><a href="<?php echo $post->permalink; ?>" title="Comments on this post"><?php echo $post->comments->approved->count; ?> <?php echo _n( 'Comment', 'Comments', $post->comments->approved->count ); ?></a></div>
					</div><br>
					<?php if ( $user ) { ?>
					<a href="<?php URL::out( 'admin', 'page=publish&slug=' . $post->slug); ?>" title="Edit post">Edit</a>
					<?php } ?>
				</div>
<?php endforeach; ?>
			<!--end loop-->
			<div id="pagenav">
				<?php $theme->prev_page_link('&laquo; Newer Results'); ?> <?php $theme->page_selector( null, array( 'leftSide' => 2, 'rightSide' => 2 ) ); ?> <?php $theme->next_page_link('&raquo; Older Results'); ?>
			<?php else: ?>
				<p><em>No results for <?php echo htmlspecialchars( $criteria ); ?></em></p>
			<?php endif; ?>
			</div>
			</div>
			
		<!--end primary content-->
		<?php $theme->display ( 'sidebar' ); ?>
	</div>
	<!--end content-->
	<?php $theme->display ( 'footer' ); ?>