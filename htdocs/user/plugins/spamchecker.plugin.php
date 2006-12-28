<?php

/**
 * SpamChecker Class
 * 
 * This class is a demonstration of a Habari plugin.
 * It should probably not be distributed with the Habari source
 * unless it is significantly improved.
 * $LastChangedDate$
 * $Rev$   
 * $LastChangedBy$
 * $HeadURL$
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
			'url' => 'http://habariblog.org',
			'author' => 'Habari Developers',
			'authorurl' => 'http://habariblog.org',
			'version' => '1.0',
			'description' => 'Provides minimal spam checking as a sample plugin',
		);
	}	 	 	 	

	public function action_plugins_loaded()
	{
		Utils::debug('ok');
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
	function filter_add_comment($comment)
	{
		// This plugin ignores non-comments
		if($comment->type != Comment::COMMENT) {
			return $comment;
		}
		
		// Comments with more than one link are spam
		// When we implement real spam protection, this will look even more like a joke. 
		preg_match_all('/<\s*a\s.*?<\s*\/\s*a\s*>/', $comment->content, $matches);
		if(count($matches[0]) > 1) {
			$comment->status = Comment::STATUS_SPAM;
		}
		return $comment;
	}
	
}

?>
