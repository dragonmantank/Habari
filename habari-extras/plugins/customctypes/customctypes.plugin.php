<?php

/**
 * Custom Content Types Plugin Class
 *
 **/

class CustomCTypes extends Plugin
{
	/**
	 * function info
	 * Returns information about this plugin
	 * @return array Plugin info array
	 **/
	function info()
	{
		return array (
			'name' => 'Custom Content Types',
			'url' => 'http://habariproject.org/',
			'author' => 'Habari Community',
			'authorurl' => 'http://habariproject.org/',
			'version' => '1.0',
			'description' => 'Allows admins to create new content types.',
			'license' => 'Apache License 2.0',
		);
	}

	/**
	 * Add the Custom Sontent Types page to the admin menu
	 *
	 * @param array $menus The main admin menu
	 * @return array The altered admin menu
	 */
	function filter_adminhandler_post_loadplugins_main_menu( $menus )
	{
		$menus['admin']['submenu']['content_types'] =  array( 'caption' => _t( 'Custom Content Types' ), 'url' => URL::get( 'admin', 'page=admin_cctypes' ) );
		return $menus;
	}

	/**
	 * On plugin init, add the admin_cctypes template to the admin theme
	 */
	function action_init()
	{
		$this->add_template('admin_cctypes', dirname(__FILE__) . '/cctypes.php');
		$this->add_template('admin_cctype_publish', dirname(__FILE__) . '/cctype_publish.php');
	}

	/**
	 * Respond to get requests on the admin_cctypes template
	 *
	 * @param AdminHandler $handler The admin handler object
	 * @param Theme $theme The admin theme object
	 */
	function action_admin_theme_get_admin_cctypes( $handler, $theme )
	{
		$posttypes = Post::list_active_post_types();
		unset($posttypes['any']);
		$posttypes = array_flip($posttypes);
		$theme->posttypes = $posttypes;

		if($edit_type = Controller::get_var('edit_type')) {
			$theme->edit_type = $edit_type;
			$theme->edit_type_name = $posttypes[$edit_type];
		}
	}

	/**
	 * Respond to post requests on the admin_cctypes template
	 *
	 * @param AdminHandler $handler The admin handler object
	 * @param Theme $theme The admin theme object
	 */
	function action_admin_theme_post_admin_cctypes( $handler, $theme )
	{
		$action = Controller::get_var('cct_action');
		switch($action) {
			case 'addtype':
				Post::add_new_type($_POST['newtype']);
				$handled = Options::get('cctypes_types');
				if(!is_array($handled)) {
					$handled = array();
				}
				$handled[$_POST['newtype']] = $_POST['newtype'];
				array_unique($handled);
				Options::set('cctypes_types', $handled);
				Session::notice(_t('Added post type "'.$_POST['newtype'].'".'));
				break;
			case 'deletetype':
				Post::deactivate_post_type($_POST['deltype']);
				$handled = Options::get('cctypes_types');
				if(isset($handled[$_POST['deltype']])) {
					unset($handled[$_POST['deltype']]);
				}
				Options::set('cctypes_types', $handled);
				Session::notice(_t('Deactivated post type "'.$_POST['newtype'].'".'));
		}
		$this->action_admin_theme_get_admin_cctypes($handler, $theme);
		$theme->display( 'admin_cctypes' );
		exit;
	}

	/**
	 * Produce a URL that links to an editing page for a specific content type
	 *
	 * @param Theme $theme The admin theme object
	 * @param integer $type_id The type id of the post type to edit
	 * @return string The URL of the editing page for the requested post type.
	 */
	function theme_admin_edit_ctype_url($theme, $type_id)
	{
		return URL::get('admin', array('page' => 'admin_cctypes', 'edit_type' => $type_id));
	}


	/**
	 * Display a custom publish page for handled content types
	 *
	 * @param AdminHandler $handler The admin handler object
	 * @param Theme $theme The admin theme object
	 */
	function action_admin_theme_get_publish( $handler, $theme )
	{
		$handled = Options::get('cctypes_types');

		if ( isset( $handler->handler_vars['slug'] ) ) {
			$post= Post::get( array( 'slug' => $handler->handler_vars['slug'], 'status' => Post::status( 'any' ) ) );
			$ctype = Post::type_name($post->content_type);
		}
		else if ( isset( $handler->handler_vars['content_type'] ) ) {
			$ctype = $handler->handler_vars['content_type'];
		}

		if(isset($ctype) && in_array($ctype, $handled)) {
			$template_name = 'admin_publish_' . $ctype;
			if($theme->template_exists( $template_name )) {
				$theme->display( $template_name );
			}
			else {
				if ( isset( $post ) ) {
					$this->theme->newpost= false;
				}
				else {
					$post = new Post();
					$theme->newpost = true;
				}
				$theme->content_type= Post::type( $ctype );
				$theme->post = $post;

				$statuses= Post::list_post_statuses( false );
				unset( $statuses[array_search( 'any', $statuses )] );
				$statuses= Plugins::filter( 'admin_publish_list_post_statuses', $statuses );
				$theme->statuses= $statuses;
				$theme->wsse= Utils::WSSE();

				$controls= array(
					'Settings' => $theme->fetch( 'publish_settings' ),
				);
				$theme->controls = Plugins::filter( 'publish_controls', $controls, $post );
				$theme->display( 'admin_cctype_publish' );
			}
			exit;
		}
	}

	/**
	 * Handle posting of custom content types
	 *
	 * @param AdminHandler $handler The admin handler object
	 * @param Theme $theme The admin theme object
	 */
	function action_admin_theme_post_publish( $handler, $theme )
	{
		$handled = Options::get('cctypes_types');

		if ( isset( $handler->handler_vars['slug'] ) ) {
			$post= Post::get( array( 'slug' => $handler->handler_vars['slug'], 'status' => Post::status( 'any' ) ) );
			$ctype = Post::type_name($post->content_type);
		}
		else if ( isset( $handler->handler_vars['content_type'] ) ) {
			$ctype = Post::type_name($handler->handler_vars['content_type']);
		}

		if(isset($handled[$ctype])) {
Utils::debug($_POST);
			exit;
		}
	}

}

?>