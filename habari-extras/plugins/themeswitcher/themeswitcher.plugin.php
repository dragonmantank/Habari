<?php

/**
 * ThemeSwitcher - Allows visitors to change the theme of the site.
 */

class ThemeSwitcher extends Plugin
{
	// True if template was shown, false otherwise
	private $shown= false;
	
	/**
	 * function info
	 * Returns information about this plugin
	 * @return array Plugin info array
	 **/
	function info()
	{
		return array (
			'name' => 'ThemeSwitcher',
			'url' => 'http://habariproject.org/',
			'author' => 'Habari Community',
			'authorurl' => 'http://habariproject.org/',
			'version' => '1.1',
			'description' => 'Allows visitors to change the theme of the site.',
			'license' => 'Apache License 2.0',
			'copyright' => '2008'
		);
	}
	
	function theme_header() {
		echo '<script src="' . Site::get_url('scripts') . '/jquery.js" type="text/javascript"></script>';
		echo <<< HEADER

<script type="text/javascript">
	$(document).ready(function(){
		$("input[@name='themeswitcher_submit']").hide();
		$("select[@name='theme_dir']").change( function() {
			$("form[@name='themeswitcher']").submit();
		});
	});
</script>

HEADER;
	}
	
	function action_init()
	{
		if (!empty($_GET['theme_dir']) || !empty($_POST['theme_dir'])) {
			$new_theme_dir= empty($_GET['theme_dir']) ? $_POST['theme_dir'] : $_GET['theme_dir'];
			$all_themes= Themes::get_all();
			if ( array_key_exists( $new_theme_dir, $all_themes ) ) {				
				if (!isset($_COOKIE['theme_dir']) || (isset($_COOKIE['theme_dir']) && ($_COOKIE['theme_dir'] != $new_theme_dir))) {					
					$_COOKIE['theme_dir']= $new_theme_dir; // Without this, the cookie isn't get in time to change the theme NOW.
					setcookie( 'theme_dir', $new_theme_dir );
				}
			}
		}
		
		$this->add_template('switcher', dirname(__FILE__) . '/themeswitcher.php');
	}

	/**
	 * Call $theme->switcher() in your theme to display the template where you want.
	 */
	function theme_switcher( $theme ) {
		if (!$this->shown) {
			$this->shown= true;
			return $theme->fetch('switcher');
		}
	}
	
	/**
	 * Failsafe, if $theme->switcher() was not called, display the template in the footer.
	 */
	function theme_footer( $theme ) {
		if (!$this->shown) {			
			$this->shown= true;
			return $theme->fetch('switcher');
		}
	}
	
	function filter_option_get_value($value, $name)
	{
		if (($name == 'theme_dir') && isset($_COOKIE['theme_dir'])) {
			return $_COOKIE['theme_dir'];
		}
		else {
			return $value;
		}
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
			$actions[]= 'Configure';
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
				case 'Configure':
					$themes= array_keys(Themes::get_all_data());
					$themes= array_combine($themes, $themes);
					$ui= new FormUI( 'themeswitcher' );
					$ts_s= $ui->add('select', 'selected_themes', 'Select themes to offer:', $themes, Options::get('themeswitcher:selected_themes'));
					$ts_s->multiple= true;
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