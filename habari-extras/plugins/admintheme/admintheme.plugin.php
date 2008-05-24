<?php

class AdminPlugin extends Plugin
{
	/**
	 * function info
	 * Returns information about this plugin
	 * @return array Plugin info array
	 **/
	public function info()
	{
		return array (
			'name' => 'Admin Theme',
			'url' => 'http://redalt.com/plugins/habari/admin',
			'author' => 'Owen Winkler',
			'authorurl' => 'http://asymptomatic.net/',
			'version' => '1.0',
			'description' => 'A replacement admin for Habari - put the required admin files and directories in the directory with this plugin',
			'license' => 'Apache License 2.0',
		);
	}

	public function filter_admin_theme_dir( $theme_dir )
	{
		$theme_dir = dirname( __FILE__ ) . '/';
		return $theme_dir;
	}

	public function action_add_template_vars( $theme )
	{
		$theme->admin_url = Site::get_url('user', TRUE) . 'plugins/' . basename(dirname(__FILE__)) . '/';
	}

}

?>