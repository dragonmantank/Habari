<?php
class Thickbox extends Plugin {
	
	/**
	 * Required Plugin Informations
	 */
	public function info() {
		return array(
			'name' => 'Thickbox',
			'version' => '0.1',
			'url' => 'http://habariproject.org/',
			'author' =>	'Habari Community',
			'authorurl' => 'http://habariproject.org/',
			'license' => 'Apache License 2.0',
			'description' => 'Adds Thickbox functionality to your theme.',
			'copyright' => '2008'
		);
	}
	
	/**
	 * Adds needed files to the theme stacks (javascript and stylesheet)
	 */
	public function action_init() {
		Stack::add( 'template_header_javascript', Site::get_url('scripts') . '/jquery.js', 'jquery' );
		Stack::add( 'template_header_javascript', Site::get_url('user') . '/plugins/thickbox/thickbox-compressed.js', 'thickbox-js' );
		Stack::add( 'template_stylesheet', array( Site::get_url('user') . '/plugins/thickbox/thickbox.css', 'screen,projector'), 'thickbox-css' );
	}
	
}
?>
