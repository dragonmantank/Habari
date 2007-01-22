<?php

/**
 * Comment Filter Class
 * 
 * This class does some basic sanitizing on submitted comments
 *
 **/

class SDMCommentFilter extends Plugin
{
	/**
	 * function info
	 * Returns information about this plugin
	 * @return array Plugin info array
	 **/
	function info()
	{
		return array (
			'name' => 'SDM Comment Filter',
			'url' => 'http://skippy.net',
			'author' => 'Scott Merill',
			'authorurl' => 'http://skippy.net',
			'version' => '1.0',
			'description' => 'Removes nasty bits from submitted comments',
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
		if ( $comment->type != Comment::COMMENT ) {
			return $comment;
		}
		
		// for now, let's just remove any <script> tags that might exist
		preg_replace( '#<script[^>]*>.*?</script>#si', '', $comment->content );

	    return $comment;
	}
}
