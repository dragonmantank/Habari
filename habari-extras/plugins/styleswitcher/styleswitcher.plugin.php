<?php
/**
 * @package Habari
 * @subpackage StyleSwitcher
 *
 * For this plugin to work, you need to add each stylesheet to the stack 'template_stylesheet_with_title'
 * Do not put the <link> yourself in the header, unless they are vital basics.
 *
 * Place at the location you want the select list the following call:
 * $theme->styleswitcher()
 *
 * Example of stack calls to put in your theme's theme.php:
 * Stack::add( 'template_stylesheet_with_title', array( 'style.css', 'screen', 'Default' ) );
 * Stack::add( 'template_stylesheet_with_title', array( 'style2.css', 'screen', 'Alternative' ) );
 *
 */

/**
 * All plugins must extend the Plugin class to be recognized.
 */
class StyleSwitcher extends Plugin {
	
	/**
	 * Required method for all plugins.
	 *
	 * @return array Various informations about this plugin.
	 */
	public function info() {
		return array(
			'name' => 'StyleSwitcher',
			'version' => '0.1',
			'url' => 'http://habariproject.org/',
			'author' =>	'Habari Community',
			'authorurl' => 'http://habariproject.org/',
			'license' => 'Apache License 2.0',
			'description' => 'Switch stylesheet without reloading, thanks to jQuery!',
			'copyright' => '2008'
		);
	}
	
	/**
	 * Add the Javascript file needed by this plugin to the theme's header.
	 */
	public function action_add_template_vars() {
		$jq_js_file= Site::get_url('scripts', TRUE) . 'jquery.js';
		$ss_js_file= Site::get_url('user', TRUE) . 'plugins/' . basename(dirname(__FILE__)) . '/styleswitcher.js';
		Stack::add( 'template_header_javascript', $jq_js_file );
		Stack::add( 'template_header_javascript', $ss_js_file );
	}
	
	public function theme_header() {
		$link= '<link rel="stylesheet" type="text/css" href="' . Site::get_url('theme', true) . '%s" media="%s" title="%s">';
		$output= Stack::get( 'template_stylesheet_with_title', $link."\r\n" );
		return $output;
	}
		
	public function theme_styleswitcher() {
		$output= array( '<select id="styleswitcher">' );
		$stacks= Stack::get_named_stack( 'template_stylesheet_with_title' );
		foreach( $stacks as $stack ) {
			$output[]= '<option value="' . $stack[2] . '">' . $stack[2] . '</option>';
		}
		$output[]= '</select>';
		print implode( "\r\n", $output );
	}
	
}

?>