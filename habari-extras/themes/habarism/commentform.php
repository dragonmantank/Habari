<?php // Do not delete these lines
if ( ! defined('HABARI_PATH' ) ) { die( _t('Please do not load this page directly. Thanks!') ); }
?>
      <h3 id="respond" class="comment_title">Leave a Reply</h3>
<?php
if ( Session::has_errors() ) {
	Session::messages_out();
}
?>
      <form action="<?php URL::out( 'submit_feedback', array( 'id' => $post->id ) ); ?>" method="post" id="commentform">
       <div id="comment-personaldetails">
        <p>
         <input type="text" name="name" id="name" value="<?php echo $commenter_name; ?>" size="22" tabindex="1">
         <label for="name">Name</label>
        </p>
        <p>
         <input type="text" name="email" id="email" value="<?php echo $commenter_email; ?>" size="22" tabindex="2">
         <label for="email">Mail (will not be published)</label>
        </p>
        <p>
         <input type="text" name="url" id="url" value="<?php echo $commenter_url; ?>" size="22" tabindex="3">
         <label for="url">Website</label>
        </p>
       </div>
       <p>
<textarea name="content" id="comment_content" cols="100" rows="10" tabindex="4">
<?php if ( isset( $details['content'] ) ) { echo $details['content']; } ?>
</textarea>
       </p>
       <p>
        <input name="submit" type="submit" id="submit" tabindex="5" value="Submit">
       </p>
       <div class="clear"></div>
      </form>
     </div>