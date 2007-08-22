<?php

/**
 * Undelete Class
 * 
 * This class provides undelete functionality for posts and comments, and
 * provides a trashcan interface for restoring items.
 *
 **/

class Undelete extends Plugin
{
	/**
	 * function info
	 * Returns information about this plugin
	 * @return array Plugin info array
	 **/
	function info()
	{
		return array (
			'name' => 'Undelete',
			'url' => 'http://skippy.net',
			'author' => 'Scott Merill',
			'authorurl' => 'http://skippy.net',
			'version' => '1.0',
			'description' => 'Stores deleted items in a virtual trashcan to support undelete functionality.',
			'license' => 'Apache License 2.0',
		);
	}

	/**
	 * function action_plugin_activation
	 * adds the "deleted" status type to the poststatus table
	 * when this plugin is activated.
	**/
	public function action_plugin_activation()
	{
		Post::add_new_status( 'deleted' );
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
	 * function filter_allow_post_delete
	 * This function is executed when the filter "before_post_delete" is 
	 * called just before a post is to be deleted.
	 * This filter should return a boolean value to indicate whether
	 * the post should be deleted or not.
	 * @param Boolean Whether to delete the post or not
	 * @param Post The post object to potentially delete
	 * @return Boolean Whether to delete the post or not
	 **/	 	 	 	 	
	function filter_allow_post_delete( $result, $post )
	{
		// all we need to do is set the post status to "deleted"
		// and then return false.  The Post::delete() method will
		// see the false return value, and simply return, leaving
		// the post in the database.
		// However, we should capture the current status and save
		// it in a postinfo record, so that undelete can restore
		// it to that status
		$post->info->prior_status= $post->status;
		$post->status= Post::status('deleted');
		$post->update();
		return false;
	}

	/**
	 * function undelete_post
	 * This function reverts a post's status from 'deleted' to whatever
	 * it previously was.
	**/
	function undelete_post( $post_id )
	{
		$post= Post::get( array( 'id' => $post_id ) );
		$post->status= $post->info->prior_status;
		unset( $post->info->prior_status );
		$post->update();
	}

	function undelete_css()
	{
		echo '#primarycontent .draft { background-color: #ffc; }';
		echo '#primarycontent .deleted { background-color: #933; text-decoration: line-through; }';
	}
}
