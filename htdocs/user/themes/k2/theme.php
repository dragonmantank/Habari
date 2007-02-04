<?php 

/**
 * A new Format class extends the possible formatting capabilities
 **/ 
class MyFormats extends Format
{
	/**
	 * Returns a shortened version of whatever is passed in.
	 * @param string $value A string to shorten
	 * @return string The string, shortened
	 * @todo Does not currently play nice with matching HTML tags that get cut off.
	 **/	 	 	 	
	function summarize($value) {
		list($ret) = explode("\n", wordwrap($value, 200));
		return $ret . (strlen($value) > strlen($ret) ? '...' : '');
	}
}

// Apply Format::autop() to post content... 
Format::apply('autop', 'post_content_out');
// Apply Format::tag_and_list() to post tags... 
Format::apply('tag_and_list', 'post_tags_out');
// Apply Format::nice_date() to post date...
Format::apply('nice_date', 'post_pubdate_out', 'F j, Y g:ia');

// Set a custom theme to use for all public page (UserThemeHandler) theme output
define('THEME_CLASS', 'CustomTheme');

/**
 * A custom theme class for the K2 theme.
 * Custom themes are not required for themes, but they are handy in letting you 
 * define your own output data and possibly even additional, non-standard templates.
 **/   
class CustomTheme extends Theme
{

	/**
	 * Get post data and forward it for display	
	 * Overrides Theme::display_posts() to summarize the content of a post when
	 * it is not being displayed by itself.
	 * 
	 * Note that the search results do not summarize because they call act_search()	 	 
	 **/	 	 	
	public function act_display_posts()
	{
		// Was the slug for this post not requested specifically? 
		if(!isset(Controller::get_handler()->handler_vars['slug'])) {
			// Apply MyFormats::summarize() to the post content on output...
			Format::apply('summarize', 'post_content_out'); 
		}
		parent::act_display_posts();
	}

}
?>
