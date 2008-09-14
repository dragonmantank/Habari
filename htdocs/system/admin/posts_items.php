<?php if(count($posts) != 0) :
	foreach($posts as $post) : ?>
<div class="item clear <?php echo $post->statusname; ?>" id="post_<?php echo $post->id; ?>">
	<div class="head clear">
		<span class="checkbox title pct35">
			<input type="checkbox" class="checkbox" name="checkbox_ids[<?php echo $post->id; ?>]" id="checkbox_ids[<?php echo $post->id; ?>]">
			<a href="<?php echo $post->permalink; ?>" class="title" title="<?php _e('Edit \'$s\'', $post->title ) ?>"><?php echo $post->title; ?></a>
		</span>
		<span class="state pct10"><a href="<?php URL::out('admin', array('page' => 'posts', 'type' => $post->content_type, 'status' => $post->status ) ); ?>" title="<?php _e('Search for other $s items', $post->statusname); ?>"><?php echo $post->statusname; ?></a></span>
		<span class="author pct20"><span class="dim"><?php _e('by'); ?></span> <a href="<?php URL::out('admin', array('page' => 'posts', 'user_id' => $post->user_id, 'type' => $post->content_type, 'status' => 'any') ); ?>" title="<?php _e('Search for other items by $s', $post->author->displayname ) ?>"><?php echo $post->author->displayname; ?></a></span>
		<span class="date pct15"><span class="dim"><?php _e('on'); ?></span> <a href="<?php URL::out('admin', array('page' => 'posts', 'type' => $post->content_type, 'year_month' => $post->pubdate->get('Y-m') ) ); ?>" title="<?php _e('Search for other items from $s', $post->pubdate->get('M, Y')); ?>"><?php $post->pubdate->out('M j, Y'); ?></a></span>
		<span class="time pct10"><span class="dim"><?php _e('at'); ?> <?php $post->pubdate->out('H:i'); ?></span></span>

		<ul class="dropbutton">
			<?php $actions= array(
				'edit' => array('url' => URL::get('admin', 'page=publish&slug=' . $post->slug), 'title' => sprintf( _t('Edit \'%s\''), $post->title ), 'label' => _t('Edit')),
				'view' => array('url' => $post->permalink, 'title' => sprintf( _t('View \'%s\''), $post->title ), 'label' => _t('View')),
				'remove' => array('url' => 'javascript:itemManage.remove('. $post->id . ', \'post\');', 'title' => _t('Delete this item'), 'label' => _t('Delete'))
			);
			$actions= Plugins::filter('post_actions', $actions, $post);
			foreach($actions as $action):
			?>
				<li><a href="<?php echo $action['url']; ?>" title="<?php echo $action['title']; ?>"><?php echo $action['label']; ?></a></li>
			<?php endforeach; ?>
		</ul>
	</div>

	<span class="content" ><?php echo substr( strip_tags( $post->content ), 0, 250); ?>&hellip;</span>
</div>

<?php 	endforeach;
else : ?>
<div class="message none">
	<p><?php _e('No posts could be found to match the query criteria.'); ?></p>
</div>
<?php endif; ?>