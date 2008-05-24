<?php
class PageSubtitle extends Plugin
{
	/**
	 * Required plugin information
	 **/
	function info() {
		return array(
			'name' => 'Page Subtitle',
			'version' => '0.1',
			'url' => 'http://habariproject.org/',
			'author' => 'Habari Community',
			'authorurl' => 'http://habariproject.org/',
			'license' => 'Apache License 2.0',
			'description' => 'Allows a user to add a subtitle to pages.'
		);
	}

	/**
	 * Add the subtitle control to the publish page
	 *
	 * @param array $controls The publish controls
	 * @param Post $post The post being edited
	 * @return array The updated controls
	 **/
	public function filter_publish_controls ( $controls, $post )
	{
		if ( Controller::get_handler()->handler_vars['content_type'] == Post::type('page') ) {
			$controls['Settings'].= '<hr><div class="container"><p class="column span-5">Subtitle</p>		<p class="column span-14 last"><input type="text" name="subtitle" id="subtitle" class="styledformelement" value="' . $post->info->subtitle . '"></p></div>';
		}
		
		return $controls;
	}

	/**
	 * Handle creation of subtitle
	 * @param Post $post The post being created
	 **/
	public function action_post_insert_before( $post )
	{
		if ( $post->content_type == Post::type('page') ) {
			$post->info->subtitle= Controller::get_handler()->handler_vars['subtitle'];
		}
	}

	/**
	 * Handle update of subtitle
	 * @param Post $post The post being updated
	 **/
	public function action_post_update_before( $post )
	{
		if ( $post->content_type == Post::type('page') ) {
			$post->info->subtitle= Controller::get_handler()->handler_vars['subtitle'];
		}
	}

	/**
	 * Enable update notices to be sent using the Habari beacon
	 **/
	public function action_update_check()
	{
		Update::add( 'PageSubtitle', 'f5afa528-3b71-422f-bfd7-f361e3d54bda',  $this->info->version );
	}

}
?>
