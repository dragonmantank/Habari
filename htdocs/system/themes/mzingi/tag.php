<?php $theme->display ( 'header' ); ?>
<!--begin content-->
	<div id="content">
		<!--begin primary content-->
		<div id="primaryContent" class="span-15 append-2">
			<!--begin loop-->
			<!--returns tag name in heading-->
			<h2 class="prepend-2"><?php _e('Posts Tagged with'); ?> <?php echo $theme->tag; ?></h2>
			<?php foreach ( $posts as $post ) { ?>
				<div id="post-<?php echo $post->id; ?>" class="<?php echo $post->statusname; ?>">
						<h2 class="prepend-2"><a href="<?php echo $post->permalink; ?>" title="<?php echo $post->title; ?>"><?php echo $post->title_out; ?></a></h2>
					<div class="entry">
					<?php echo $post->pubdate->out('F j, Y'); ?> -	<?php echo $post->content_excerpt; ?>
					</div>
					<div class="entryMeta">

						<?php if ( is_array( $post->tags ) ) { ?>
						<div class="tags"><?php _e('Tagged:'); ?> <?php echo $post->tags_out; ?></div>
						<?php } ?>
						<div class="commentCount"><a href="<?php echo $post->permalink; ?>" title="<?php _e('Comments on this post'); ?>"><?php echo $post->comments->approved->count; ?> <?php echo _n( 'Comment', 'Comments', $post->comments->approved->count ); ?></a></div>
					</div><br>
					<?php if ( $loggedin ) { ?>
					<a href="<?php echo $post->editlink; ?>" title="<?php _e('Edit post'); ?>"><?php _e('Edit'); ?></a>
					<?php } ?>
				</div>
			<?php } ?>
			<!--end loop-->
			<div id="pagenav">
				<?php $theme->prev_page_link('&laquo; ' . _t('Newer Results')); ?> <?php $theme->page_selector( null, array( 'leftSide' => 2, 'rightSide' => 2 ) ); ?> <?php $theme->next_page_link('&raquo; ' . _t('Older Results')); ?>
			</div>
			</div>

		<!--end primary content-->
		<?php $theme->display ( 'sidebar' ); ?>
	</div>
	<!--end content-->
	<?php $theme->display ( 'footer' ); ?>
