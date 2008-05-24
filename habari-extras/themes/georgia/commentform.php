<?php // Do not delete these lines
if ( ! defined('HABARI_PATH' ) ) { die( _t('Please do not load this page directly. Thanks!') ); }
?>
	<h3 id="respond">Leave a Reply</h3>
<?php
if ( Session::has_errors() ) {
	Session::messages_out();
}
?>
	<form action="<?php URL::out( 'submit_feedback', array( 'id' => $post->id ) ); ?>" method="post" id="commentform">
		<div id="comment-personaldetails">
			<p class="clearfix">
				<label for="name">Name</label>
				<input type="text" name="name" id="name" value="<?php echo $commenter_name; ?>" size="22" tabindex="1">
			</p>
			<p class="clearfix">
				<label for="email">Mail</label>
				<input type="text" name="email" id="email" value="<?php echo $commenter_email; ?>" size="22" tabindex="2">
				<span>(will not be published)</span>
			</p>
			<p class="clearfix">
				<label for="url">Website</label>
				<input type="text" name="url" id="url" value="<?php echo $commenter_url; ?>" size="22" tabindex="3">
			</p>
		</div>
		<p>
			<textarea name="content" id="comment_content" cols="90" rows="10" tabindex="4"><?php if ( isset( $details['content'] ) ) { echo $details['content']; } ?></textarea>
		</p>
		<p><input name="submit" type="submit" id="submit" tabindex="5" value="Submit"></p>
	</form>
</div>