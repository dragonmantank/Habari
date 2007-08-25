<?php

/**
 * SpamChecker Class
 * 
 * This class implements first round spam checking.
 *
 **/

class SpamChecker extends Plugin
{
	/**
	 * function info
	 * Returns information about this plugin
	 * @return array Plugin info array
	 **/
	function info()
	{
		return array (
			'name' => 'Spam Checker',
			'url' => 'http://habariproject.org/',
			'author' => 'Habari Community',
			'authorurl' => 'http://habariproject.org/',
			'version' => '1.0',
			'description' => 'Flags as spam obvious comment spam.',
			'license' => 'Apache License 2.0',
		);
	}
	
	/**
	 * function actions_plugins_loaded
	 * Executes after all plugins are loaded
	 **/	 	 	
	public function action_plugins_loaded()
	{
		//Utils::debug('ok');
	}
	
	/**
	 * function act_comment_insert_before
	 * This function is executed when the filter "add_comment" is applied to a Comment object.
	 * The parent class, Plugin, handles registering the filter and hook name using the 
	 * name of the function to determine where it will be applied.
	 * You can still register functions as hooks without using this method, but boy, is it handy.
	 * Note that other plugins may filter this data also, so the Comment object should pass
	 * through this function relatively unscathed.  The implication of this, for example
	 * is that you shouldn't return null from this function.	 	 
	 * @param Comment The comment that will be processed before storing it in the database.
	 * @return Comment The comment result to store.
	 **/	 	 	 	 	
	function act_comment_insert_before ( $comment )
	{
		// This plugin ignores non-comments
		if($comment->type != Comment::COMMENT) {
			return $comment;
		}

		// <script> is bad, mmmkay?
		 preg_replace( '#<script[^>]*>.*?</script>#si', '', $comment->content );

		// first, check the commenter's name
		// if it's only digits, then we can discard this comment
		if ( preg_match( "/^\d+$/", $comment->name ) ) {
			$comment->status = Comment::STATUS_SPAM;
		}

		// now look at the comment text
		// if it's digits only, discard it
		$textonly = strip_tags( $comment->content );

		if ( preg_match( "/^\d+$/", $textonly ) ) {
			$comment->status = Comment::STATUS_SPAM;
		}

		// is the content the single word "array"?
		if ( 'array' == strtolower( $textonly ) ) {
			$comment->status = Comment::STATUS_SPAM;
		}

		// is the conent the same as the name?
		if ( strtolower( $textonly ) == strtolower( $comment->name ) ) {
			$comment->status = Comment::STATUS_SPAM;
		}

		// a lot of spam starts with "<strong>some text...</strong>"
		if ( preg_match( "#^<strong>[^.]+\.\.\.</strong>#", $comment->content ) )
		{
			$comment->status = Comment::STATUS_SPAM;
		}

		// are there more than 3 URLs posted?  If so, it's almost certainly spam
		if ( 3 <= preg_match_all( "/a href=/", strtolower( $comment->content ), $matches ) ) 
		{
			$comment->status = Comment::STATUS_SPAM;
		}

		// otherwise everything looks good, so continue processing the comment
		return $comment;
	}
	
	/**
	 * These code functions will be used as soon as 
	 * you can hook rewrite rules to modify the output for
	 * a specific rule.
	 * 
	 * You would be able to use this for the comment form action:
	 * 	  URL::get( 'add_comment', array('id'=>$post->id, 'code'=>Plugins::filter('get_code', 0) );
	 * 
	 * And then verify that the URL used to submit the form 
	 * was valid before execution ever hit the comment filtering. 	 	 	 
	 **/	 	 	 	
	
	/**
	 * Get a 10-digit hex code that identifies the user submitting the comment
	 * @param A post id to which the comment will be submitted
	 * @param The IP address of the commenter
	 * @return A 10-digit hex code
	 **/	 	 	 	 
	public static function get_code($post_id, $ip = '')
	{
		if( $ip == '' ) {
			$ip = ip2long($_SERVER['REMOTE_ADDR']);
		}
		$code = substr(md5( $post_id . Options::get('GUID') . 'more salt' . $ip ), 0, 10);
		$code = Plugins::filter('comment_code', $code, $post_id, $ip);
		return $code;
	}
	
	/**
	 * Verify a 10-digit hex code that identifies the user submitting the comment
	 * @param A post id to which the comment has been submitted
	 * @param The IP address of the commenter
	 * @return True if the code is valid, false if not
	 **/	 	 	 	 
	public static function verify_code($suspect_code, $post_id, $ip = '')
	{
		if( $ip == '' ) {
			$ip = ip2long($_SERVER['REMOTE_ADDR']);
		}
		$code = substr(md5( $post_id . Options::get('GUID') . 'more salt' . $ip ), 0, 10);
		$code = Plugins::filter('comment_code', $code, $post_id, $ip);
		return ($suspect_code == $code);
	}

}
