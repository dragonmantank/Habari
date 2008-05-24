<?php

/**
 * Credit Due plugin, allows output of theme and plugin information for Habari sites.
 */

class CreditDue extends Plugin
{
	/**
	 * function info
	 * Returns information about this plugin
	 * @return array Plugin info array
	 **/
	function info()
	{
		return array (
			'name' => 'Credit Due',
			'url' => 'http://habariproject.org/',
			'author' => 'Habari Community',
			'authorurl' => 'http://habariproject.org/',
			'version' => '0.2',
			'description' => 'Allows for output of information for active themes and plugins',
			'license' => 'Apache License 2.0',
		);
	}

	/**
	 * function theme_show_credits
	 * retrieves information about the active theme and returns it for display by a theme.
	 * 
	 * Usage: This function is called using <?php $theme->show_credits(); ?>
	 * This loads the template creditdue.php (which can be copied to the theme directory and customized) and shows the theme and plugins in unordered lists
	 */
	public function theme_show_credits( $theme ) {

		$theme_credits= Themes::get_active();
		$plugin_credits= Plugins::get_active();

		#Utils::debug($theme_credits);
		#Utils::debug($plugin_credits);
		
		$theme->theme_credits= $theme_credits;
		$theme->plugin_credits= $plugin_credits;
		
		return $theme->fetch( 'creditdue' );
	}
	
	/**
	 * On plugin init, add the template included with this plugin to the available templates in the theme
	 */
	public function action_init()
	{
		$this->add_template('creditdue', dirname(__FILE__) . '/creditdue.php');
	}

}


?>