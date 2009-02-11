<?php include('header.php'); ?>

<div class="create">

	<?php $form->out(); ?>

</div>

<script type="text/javascript">
$(document).ready(function(){
	$('.container').addClass('transparent');
	// If this post has been saved, add a delete button and a nonce for authorising deletes
	<?php if(isset($post->id) && ($post->id != '')) : ?>
	$('.container.buttons').prepend($('<input type="button" id="delete" class="button delete" tabindex="6" value="<?php _e('Delete'); ?>">'));
	$('#delete').click(function(){
		$('#create-content')
			.append($('<input type="hidden" name="nonce" value="<?php echo $wsse['nonce']; ?>"><input type="hidden" name="timestamp" value="<?php echo $wsse['timestamp']; ?>"><input type="hidden" name="digest" value="<?php echo $wsse['digest']; ?>">'))
			.attr('action', '<?php URL::out( 'admin', array('page' => 'delete_post', 'id' => $post->id )); ?>')
			.submit();
	});
	<?php endif; ?>

	// If the post hasn't been published, add a publish button
	<?php if(isset($statuses['published']) && $post->status != $statuses['published']) : ?>
	$('.container.buttons').prepend($('<input type="button" id="publish" class="button publish" tabindex="5" value="<?php _e('Publish'); ?>">'));
	$('#publish').click( function() {
		$('#status').val(<?php echo $statuses['published']; ?>);
	});
	<?php endif; ?>

	// Submit when the publish button is clicked.
	$('#publish').click( function() {
		$('#create-content').submit();
	});

	$('#create-content').submit(function(){
		initialCrc32 = crc32($('#content').val(), crc32($('#title').val()));
	});

	initialCrc32 = crc32($('#content').val(), crc32($('#title').val()));

	window.onbeforeunload = function(){
		if (initialCrc32 != crc32($('#content').val(), crc32($('#title').val())) ) {
			return '<?php
				// Note to translators: the 'new-line character' is an actual "\n" not a new-line character
				_e('You did not save the changes you made. \nLeaving this page will result in the lose of data.');
				?>';
		}
	};
});
</script>

<?php include('footer.php'); ?>
