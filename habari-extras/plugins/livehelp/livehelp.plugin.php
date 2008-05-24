<?php

/**
 * Live Help Plugin
 *
 **/

class LiveHelp extends Plugin
{
	/**
	 * function info
	 * Returns information about this plugin
	 * @return array Plugin info array
	 **/
	function info()
	{
		return array (
			'name' => 'Live Help',
			'url' => 'http://habariproject.org/',
			'author' => 'Habari Community',
			'authorurl' => 'http://habariproject.org/',
			'version' => '0.11',
			'description' => 'Allows users to connect to #habari on IRC from within the admin.',
			'license' => 'Apache License 2.0',
		);
	}

	/**
	 * Add the Live Help page to the admin menu
	 *
	 * @param array $menus The main admin menu
	 * @return array The altered admin menu
	 */
	function filter_adminhandler_post_loadplugins_main_menu( $menus )
	{
		$menus['livehelp'] =  array( 'url' => URL::get( 'admin', 'page=livehelp'), 'title' => _t('Live Help'), 'text' => _t('Live Help'), 'selected' => false );
		return $menus;
	}

	/**
	 * On plugin init, add the admin_livehelp template to the admin theme
	 */
	function action_init()
	{
		$this->add_template('livehelp', dirname(__FILE__) . '/livehelp.php');
	}

	public function action_add_template_vars( $theme )
	{
		if ($theme->admin_page == 'livehelp') {
			$user= User::identify();
			$nick= $user->username;
			$nick= $nick == 'admin' ? substr($user->email, 0, strpos($user->email, '@')) : $nick;
			$theme->assign('nick', $nick);
		}
	}

	public function action_admin_header( $theme ) {
		if ($theme->admin_page == 'livehelp') {
			Stack::add('admin_stylesheet', array($this->get_url() . '/livehelp.css', 'screen'));
		}
	}

	/**
	 * Implement the update notification feature
	 */
	public function action_update_check()
	{
		Update::add( 'Live Help', 'c2413ab2-7c79-4f92-b008-18e3d8e05b64',  $this->info->version );
	}

}

?>
