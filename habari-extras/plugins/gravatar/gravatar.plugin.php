<?php
/**
 * @package Habari
 * @subpackage Gravatar
 */

/**
 * All plugins must extend the Plugin class to be recognized.
 */
class Gravatar extends Plugin {

	/**
	 * Required method for all plugins.
	 *
	 * @return array Various informations about this plugin.
	 */
	public function info() {
		return array(
			'name' => 'Gravatar',
			'version' => '1.1',
			'url' => 'http://habariproject.org/',
			'author' =>	'Habari Community',
			'authorurl' => 'http://habariproject.org/',
			'license' => 'Apache License 2.0',
			'description' => 'Gravatar plugin for Habari',
			'copyright' => '2007'
		);
	}
	
	/**
	 * Return a URL to the author's Gravatar based on his e-mail address.
	 *
	 * @param object $comment The Comment object to build a Gravatar URL from.
	 * @return string URL to the author's Gravatar.
	 */
	public function filter_comment_gravatar( $out, $comment ) { 
		// The Gravar ID is an hexadecimal md5 hash of the author's e-mail address.
		$query_arguments= array( 'gravatar_id' => md5( $comment->email ) );
		// Retrieve the Gravatar options.
		$options= Options::get( 'gravatar:default', 'gravatar:size', 'gravatar:rating' );
		foreach ( $options as $key => $value ) {
			if ( $value != '' ) {
				// We only want "default, size, rating".
				list( $junk, $key )= explode( ':', $key );
				$query_arguments[$key]= $value;
			}
		}
		// Amperstands need to be encoded to &amp; for HTML to validate.
		$query= http_build_query( $query_arguments, '', '&amp;' );
		$url= "http://www.gravatar.com/avatar.php?" . $query;
		
		return $url;
	}
	
	/**
	 * Add our menu to the FormUI for plugins.
	 *
	 * @param array $actions Array of menu items for this plugin.
	 * @param string $plugin_id A unique plugin ID, it needs to match ours.
	 * @return array Original array with our added menu.
	 */
	public function filter_plugin_config( $actions, $plugin_id ) {
		if ( $plugin_id == $this->plugin_id ) { 
			$actions[]= 'Options';
		}
		
		return $actions;
	}
	
	/**
	 * Handle calls from FormUI actions.
	 * Show the form to manage the plugin's options.
	 *
	 * @param string $plugin_id A unique plugin ID, it needs to match ours.
	 * @param string $action The menu item the user clicked.
	 */
	public function action_plugin_ui( $plugin_id, $action ) {
		if ( $plugin_id == $this->plugin_id ) {
			switch ( $action ) {
				case 'Options':
					$ui= new FormUI( 'gravatar' );
					$g_s_d= $ui->add( 'text', 'default', '<dl><dt>Default Gravatar</dt><dd>An optional "default" parameter may follow that specifies the full, URL encoded URl, protocol included of a GIF, JPEG or PNG image that should be returned if either the request email address has no associated gravatar, or that gravatar has a rating higher than is allowed by the "rating" parameter.</dd></dl>', Options::get( 'gravatar:default' ) );
					$g_s_s= $ui->add( 'text', 'size', '<dl><dt>Size</dt><dd>An optional "size" parameter may follow that specifies the desired width and height of the gravatar. Valid vaues are from 1 to 80 inclusive. Any size other than 80 will cause the original gravatar image to be downsampled using bicubic resampling before output.</dd></dl>', Options::get( 'gravatar:size' ) );
					//mark size as required
					$g_s_s->add_validator( 'validate_required' );
					
					$g_s_r= $ui->add( 'select', 'rating', '<dl><dt>Rating</dt><dd>An optional "rating" parameter may follow with a value of [ G | PG | R | X ] that determines the highest rating (inclusive) that will be returned.</dd></dl>', array( 'G' => 'G', 'PG' => 'PG', 'R' => 'R', 'X' => 'X' ), Options::get( 'gravatar:rating' ) );
					$ui->on_success( array( $this, 'save_options' ) );
					$ui->out();
					break;
			}
		}
	}
	
	/**
	 * Fail-safe method to force options to be saved in Habari's options table.
	 *
	 * @return bool Return true to force options to be saved in Habari's options table.
	 */
	public function save_options( $ui ) {
		return true;
	}

}
?>
