<?php include('header.php');?>


<div class="container navigator">
	<span class="older pct10"><a href="#" onclick="timeline.skipLoupeLeft();return false">&laquo; <?php _e('Older'); ?></a></span>
	<span class="currentposition pct15 minor"><?php _e('no results'); ?></span>
	<span class="search pct50">
		<input id="search" type="search" placeholder="<?php _e('Type and wait to search'); ?>" autosave="habaricontent" results="10" value="<?php echo htmlspecialchars($search_args); ?>">
	</span>
	<span class="filters pct15">&nbsp;
		<ul class="dropbutton special_search">	
			<?php foreach($special_searches as $text => $term): ?>
			<li><a href="#<?php echo $term; ?>" title="<?php printf( _t('Filter results for \'%s\''), $text ); ?>"><?php echo $text; ?></a></li>
			<?php endforeach; ?>
		</ul>
	</span>
	<span class="newer pct10"><a href="#" onclick="timeline.skipLoupeRight();return false"><?php _e('Newer'); ?> &raquo;</a></span>

	<div class="timeline">
		<div class="years">
			<?php $theme->display( 'timeline_items' )?>
		</div>

		<div class="track">
			<div class="handle">
				<span class="resizehandleleft"></span>
				<span class="resizehandleright"></span>
			</div>
		</div>

	</div>

</div>

<div class="container transparent item controls">

	<input type="hidden" name="nonce" id="nonce" value="<?php echo $wsse['nonce']; ?>">
	<input type="hidden" name="timestamp" id="timestamp" value="<?php echo $wsse['timestamp']; ?>">
	<input type="hidden" name="PasswordDigest" id="PasswordDigest" value="<?php echo $wsse['digest']; ?>">
	<span class="checkboxandselected pct30">
		<input type="checkbox" id="master_checkbox" name="master_checkbox">
		<label class="selectedtext minor none" for="master_checkbox"><?php _e('None selected'); ?></label>
	</span>
	<input type="button" value="<?php _e('Delete Selected'); ?>" class="delete button">
	
</div>


<div class="container posts">

<?php $theme->display('posts_items'); ?>

</div>


<div class="container transparent item controls">

	<input type="hidden" name="nonce" id="nonce" value="<?php echo $wsse['nonce']; ?>">
	<input type="hidden" name="timestamp" id="timestamp" value="<?php echo $wsse['timestamp']; ?>">
	<input type="hidden" name="PasswordDigest" id="PasswordDigest" value="<?php echo $wsse['digest']; ?>">
	<span class="checkboxandselected pct30">
		<input type="checkbox" id="master_checkbox_2" name="master_checkbox_2">
		<label class="selectedtext minor none" for="master_checkbox_2"><?php _e('None selected'); ?></label>
	</span>
	<input type="button" value="<?php _e('Delete Selected'); ?>" class="delete button">

</div>

<script type="text/javascript">
	itemManage.updateURL = habari.url.ajaxDelete;
	itemManage.fetchURL = "<?php echo URL::get('admin_ajax', array('context' => 'posts')) ?>";
	itemManage.fetchReplace = $('.posts');
	itemManage.inEdit = false;
</script>

<?php include('footer.php');?>
