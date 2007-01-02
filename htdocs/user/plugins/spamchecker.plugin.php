<?php

/**
 * SpamChecker Class
 * 
 * This class implements first round spam checking.
 *
 **/

class SDMSpamCheck extends Plugin
{
	/**
	 * function info
	 * Returns information about this plugin
	 * @return array Plugin info array
	 **/
	function info()
	{
		return array (
			'name' => 'SDM Spam Check',
			'url' => 'http://skippy.net',
			'author' => 'Scott Merill',
			'authorurl' => 'http://skippy.net',
			'version' => '1.0',
			'description' => 'Silently discards obvious comment spam.',
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
	 * function filter_add_comment
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
	function filter_add_comment( $comment )
	{
		// This plugin ignores non-comments
		if($comment->type != Comment::COMMENT) {
			return $comment;
		}

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

	    // are there more than 8 URLs posted?  If so, it's almost certainly spam
	    if ( 3 <= preg_match_all( "/a href=/", strtolower( $comment->content ), $matches ) ) 
		{
			$comment->status = Comment::STATUS_SPAM;
	   	}

	    // otherwise everything looks good, so continue processing the comment
	    return $comment;
	}
}