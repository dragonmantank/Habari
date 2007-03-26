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
	 **/	 	 	 	
	function summarize($text) {
		$count = 100;  // The number of words to display
		$maxparagraphs = 1;  // The maximum number of paragraphs to display
	
		preg_match_all('/<script.*?<\/script.*?>/', $text, $scripts);
		preg_replace('/<script.*?<\/script.*?>/', '', $text);
	
		$words = preg_split('/(<(?:\\s|".*?"|[^>])+>|\\s+)/', $text, $count + 1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
	
		$ellipsis = '';
		if(count($words) > $count * 2) {
			array_pop($words);
			$ellipsis = '...';
		}
		$output = '';
		
		$paragraphs = 0;
		
		$stack = array();
		foreach($words as $word) {
			if(preg_match('/<.*\/\\s*>$/', $word)) {
				// If the tag self-closes, do nothing.
				$output .= $word;
			}
			elseif( preg_match('/<[\\s\/]+/', $word)) {
				// If the tag ends, pop one off the stack (cheatingly assuming well-formed!)
				array_pop($stack);
				preg_match('/<\s*\/\s*(\\w+)/', $word, $tagn);
				switch($tagn[1]) {
				case 'br':
				case 'p':
				case 'div':
				case 'ol':
				case 'ul':
					$paragraphs++;
					if($paragraphs >= $maxparagraphs) {
						$output .= '...' . $word;
						$ellipsis = '';
						break 2;
					}
				}
				$output .= $word;
			}
			elseif( $word{0} == '<' ) {
				// If the tag begins, push it on the stack
				$stack[] = $word;
				$output .= $word;
			}
			else {
				$output .= $word;
			}
		}
		$output .= $ellipsis;
	
		if(count($stack) > 0) {
			preg_match('/<(\\w+)/', $stack[0], $tagn);
			$stack = array_reverse($stack);
			foreach($stack as $tag) {
				preg_match('/<(\\w+)/', $tag, $tagn);
				$output .= '</' . $tagn[1] . '>';
			}
		}
		foreach($scripts[0] as $script) {
			$output .= $script;
		}

		return $output;
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
