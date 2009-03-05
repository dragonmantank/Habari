<?php
/**
 * @package Habari
 *
 */

/**
 * Habari Plugins Class
 *
 * Provides an interface for the code to access plugins
 */
class Plugins
{
	private static $hooks = array();
	private static $plugins = array();
	private static $plugin_files = array();
	private static $plugin_classes = array();

	/**
	 * function __construct
	 * A private constructor method to prevent this class from being instantiated.
	 * Don't ever create this class as an object for any reason.  It is not a singleton.
	 **/
	private function __construct()
	{
	}

	/**
	 * function register
	 * Registers a plugin action for possible execution
	 * @param mixed A reference to the function to register by string or array(object, string)
	 * @param string Usually either 'filter' or 'action' depending on the hook type.
	 * @param string The plugin hook to register
	 * @param hex An optional execution priority, in hex.  The lower the priority, the earlier the function will execute in the chain.  Default value = 8.
	**/
	public static function register( $fn, $type, $hook, $priority = 8 )
	{
		// add the plugin function to the appropriate array
		$index = array($type, $hook, $priority);

		$ref =& self::$hooks;

		foreach( $index as $bit ) {
		    if(!isset($ref["{$bit}"])) {
		    	$ref["{$bit}"] = array();
		    }
		    $ref =& $ref["{$bit}"];
		}

		$ref[] = $fn;
		ksort(self::$hooks[$type][$hook]);
	}

	/**
	 * function act
	 * Call to execute a plugin action
	 * @param string The name of the action to execute
	 * @param mixed Optional arguments needed for action
	 **/
	public static function act()
	{
		$args = func_get_args();
		$hookname = array_shift($args);
		if ( ! isset( self::$hooks['action'][$hookname] ) ) {
			return false;
		}
		foreach ( self::$hooks['action'][$hookname] as $priority ) {
			foreach ( $priority as $action ) {
				// $action is an array of object reference
				// and method name
				call_user_func_array( $action, $args );
			}
		}
	}

	/**
	 * Call to execute a plugin filter
	 * @param string The name of the filter to execute
	 * @param mixed The value to filter.
	 **/
	public static function filter()
	{
		list( $hookname, $return ) = func_get_args();
		if ( ! isset( self::$hooks['filter'][$hookname] ) ) {
			return $return;
		}

		$filterargs = array_slice(func_get_args(), 2);
		foreach ( self::$hooks['filter'][$hookname] as $priority ) {
			foreach ( $priority as $filter ) {
				// $filter is an array of object reference and method name
				$callargs = $filterargs;
				array_unshift( $callargs, $return );
				$return = call_user_func_array( $filter, $callargs );
			}
		}
		return $return;
	}

	/**
	 * Call to execute an XMLRPC function
	 * @param string The name of the filter to execute
	 * @param mixed The value to filter.
	 **/
	public static function xmlrpc()
	{
		list( $hookname, $return ) = func_get_args();
		if ( ! isset( self::$hooks['xmlrpc'][$hookname] ) ) {
			return false;
		}
		$filterargs = array_slice(func_get_args(), 2);
		foreach ( self::$hooks['xmlrpc'][$hookname] as $priority ) {
			foreach ( $priority as $filter ) {
				// $filter is an array of object reference and method name
				return call_user_func_array( $filter, $filterargs );
			}
		}
		return false;
	}

	/**
	 * Call to execute a theme function
	 * @param string The name of the filter to execute
	 * @param mixed The value to filter
	 * @return The filtered value
	 */
	public static function theme()
	{
		$filter_args = func_get_args();
		$hookname = array_shift($filter_args);

		$filtersets = array();
		if(!isset(self::$hooks['theme'][$hookname])) {
			return array();
		}

		$return = array();
		foreach ( self::$hooks['theme'][$hookname] as $priority ) {
			foreach ( $priority as $filter ) {
				// $filter is an array of object reference and method name
				$callargs = $filter_args;
				if(is_array($filter)) {
					if(is_string($filter[0])) {
						$module = $filter[0];
					}
					else {
						$module = get_class($filter[0]);
					}
				}
				else {
					$module = $filter;
				}
				$return[$module] = call_user_func_array( $filter, $callargs );
			}
		}
		array_unshift($filter_args, 'theme_call_' . $hookname, $return);
		$result = call_user_func_array(array('Plugins', 'filter'), $filter_args);
		return $result;
	}

	/**
	 * Determine if any plugin implements the indicated theme hook
	 *
	 * @param string $hookname The name of the hook to check for
	 * @return boolean True if the hook is implemented
	 */
	public static function theme_implemented( $hookname )
	{
		return isset( self::$hooks['theme'][$hookname] );
	}

	/**
	 * function list_active
	 * Gets a list of active plugin filenames to be included
	 * @param boolean Whether to refresh the cached array.  Default FALSE
	 * @return array An array of filenames
	 **/
	public static function list_active( $refresh = false )
	{
		if ( ! empty( self::$plugin_files ) && ! $refresh )
		{
			return self::$plugin_files;
		}
		$plugins = Options::get( 'active_plugins' );
		if( is_array($plugins) ) {
			foreach( $plugins as $plugin ) {
				// add base path to stored path
				$plugin = HABARI_PATH . $plugin;

				if( file_exists( $plugin ) ) {
					self::$plugin_files[] = $plugin;
				}
			}
		}
		// make sure things work on Windows
		self::$plugin_files = array_map( create_function( '$s', 'return str_replace(\'\\\\\', \'/\', $s);' ), self::$plugin_files );
		return self::$plugin_files;
	}

	/**
	 * function get_active
	 * Returns the internally stored references to all loaded plugins
	 * @return array An array of plugin objects
	 **/
	public static function get_active()
	{
		return self::$plugins;
	}

	/**
	* Get references to plugin objects that implement a specific interface
	* @param string $interface The interface to check for
	* @return array An array of matching plugins
	*/
	public static function get_by_interface($interface)
	{
		return array_filter(self::$plugins, create_function('$a', 'return $a instanceof ' . $interface . ';'));
	}

	/**
	 * function list_all
	 * Gets a list of all plugin filenames that are available
	 * @return array An array of filenames
	 **/
	public static function list_all()
	{
		$plugins = array();
		$plugindirs = array( HABARI_PATH . '/system/plugins/', HABARI_PATH . '/3rdparty/plugins/', HABARI_PATH . '/user/plugins/' );
		if ( Site::CONFIG_LOCAL != Site::$config_type ) {
			// include site-specific plugins
			$plugindirs[] = Site::get_dir( 'config' ) . '/plugins/';
		}
		$dirs = array();
		foreach ( $plugindirs as $plugindir ) {
			if ( file_exists( $plugindir ) ) {
				$dirs = array_merge( $dirs, Utils::glob( $plugindir . '*', GLOB_ONLYDIR | GLOB_MARK ) );
			}
		}
		foreach ( $dirs as $dir ) {
			$dirfiles = Utils::glob( $dir . '*.plugin.php' );
			if ( ! empty( $dirfiles ) ) {
				$dirfiles = array_combine(
					// Use the basename of the file as the index to use the named plugin from the last directory in $dirs
					array_map( 'basename', $dirfiles ),
					// massage the filenames so that this works on Windows
					array_map( create_function( '$s', 'return str_replace(\'\\\\\', \'/\', $s);' ), $dirfiles )
				);
				$plugins = array_merge( $plugins, $dirfiles );
			}
		}
		ksort( $plugins );
		return $plugins;
	}

	/**
	 * Get classes that extend Plugin.
	 * @param $class string A class name
	 * @return boolean true if the class extends Plugin
	 **/
	public static function extends_plugin( $class )
	{
		$parents = class_parents( $class, false );
		return in_array( 'Plugin', $parents );
	}

	/**
	 * function class_from_filename
	 * returns the class name from a plugin's filename
	 * @param string $file the full path to a plugin file
	 * @param bool $check_realpath whether or not to try realpath resolution
	 * @return string the class name
	**/
	public static function class_from_filename( $file, $check_realpath = false )
	{
		if ( $check_realpath ) {
			$file = realpath( $file );
		}
		if ( ! self::$plugin_classes ) {
			self::get_plugin_classes();
		}
		foreach ( self::$plugin_classes as $plugin ) {
			$class = new ReflectionClass( $plugin );
			$classfile = str_replace( '\\', '/', $class->getFileName() );
			if ( $classfile == $file ) {
				return $plugin;
			}
		}
		// if we haven't found the plugin class, try again with realpath resolution:
		if ($check_realpath) {
			// really can't find it
			return false;
		}
		else {
			return self::class_from_filename( $file, true );
		}
	}
	
	public static function get_plugin_classes()
	{
		$classes = get_declared_classes();
		self::$plugin_classes = array_filter( $classes, array( 'Plugins', 'extends_plugin' ) );
	}

	/**
	 * function load
	 * Initialize all loaded plugins by calling their load() method
	 * @param string $file the class name to load
	 * @param boolean $activate True if the plugin's load() method should be called	 
	 * @return Plugin The instantiated plugin class
	 **/
	public static function load( $file, $activate = true )
	{
		$class = Plugins::class_from_filename( $file );
		$plugin = new $class;
		if($activate) {
			self::$plugins[$plugin->plugin_id] = $plugin;
			$plugin->load();
		}
		return $plugin;
	}

	/**
	 * Returns a plugin id for the filename specified.
	 * Used to unify the way plugin ids are generated, rather than spreading the
	 * calls internal to this function over several files.
	 *
	 * @param string $file The filename to generate an id for
	 * @return string A plugin id.
	 */
	public static function id_from_file( $file )
	{
		$file = str_replace(array('\\', '/'), PATH_SEPARATOR, $file);
		return sprintf( '%x', crc32( $file ) );
	}

	/**
	 * Activates a plugin file
	 **/
	public static function activate_plugin( $file )
	{
		$ok = true;
		$ok = Plugins::filter('activate_plugin', $ok, $file); // Allow plugins to reject activation
		if($ok) {
			// strip base path from stored path
			$short_file = substr( $file, strlen( HABARI_PATH ) );
			$activated = Options::get( 'active_plugins' );
			if( !is_array( $activated ) || !in_array( $short_file, $activated ) ) {
				$activated[] = $short_file;
				Options::set( 'active_plugins', $activated );
				include_once($file);
				Plugins::get_plugin_classes();
				$plugin = Plugins::load($file);
				if(method_exists($plugin, 'action_plugin_activation')) {
					$plugin->action_plugin_activation( $file ); // For the plugin to install itself
				}
				Plugins::act('plugin_activated', $file); // For other plugins to react to a plugin install
				EventLog::log( _t( 'Activated Plugin: %s', array( self::$plugins[Plugins::id_from_file( $file )]->info->name ) ), 'notice', 'plugin', 'habari' );
			}
		}
		return $ok;
	}

	/**
	 * Deactivates a plugin file
	 **/
	public static function deactivate_plugin( $file )
	{
		$ok = true;
		$name = '';
		$ok = Plugins::filter('deactivate_plugin', $ok, $file);  // Allow plugins to reject deactivation
		if($ok) {
			// normalize directory separator
			$file = str_replace( '\\', '/', $file );
			// strip base path from stored path
			$short_file = substr( $file, strlen( HABARI_PATH ) );

			$activated = Options::get( 'active_plugins' );
			$index = array_search( $short_file, $activated );
			if ( is_array( $activated ) && ( FALSE !== $index ) )
			{
				// Get plugin name for logging
				$name = self::$plugins[Plugins::id_from_file( $file )]->info->name;
				if(method_exists(self::$plugins[Plugins::id_from_file( $file )], 'action_plugin_deactivation')) {
					self::$plugins[Plugins::id_from_file( $file )]->action_plugin_deactivation( $file ); // For the plugin to uninstall itself
				}
				unset($activated[$index]);
				Options::set( 'active_plugins', $activated );
				Plugins::act('plugin_deactivated', $file);  // For other plugins to react to a plugin uninstallation
				EventLog::log( _t( 'Deactivated Plugin: %s', array( $name ) ), 'notice', 'plugin', 'habari' );
			}
		}
		return $ok;
	}

	/**
	 * Detects whether the plugins that exist have changed since they were last
	 * activated.
	 * @return boolean true if the plugins have changed, false if not.
	 **/
	public static function changed_since_last_activation()
	{
		$old_plugins = Options::get('plugins_present');
		//self::set_present();
		// If the plugin list was never stored, then they've changed.
		if(!is_array($old_plugins)) {
			return true;
		}
		// add base path onto stored path
		foreach( $old_plugins as $old_plugin ) {
			$old_plugin = HABARI_PATH . $old_plugin;
		}
		// If the file list is not identical, then they've changed.
		$new_plugin_files = Plugins::list_all();
		$old_plugin_files = array_map(create_function('$a', 'return $a["file"];'), $old_plugins);
		if(count(array_intersect($new_plugin_files, $old_plugin_files)) != count($new_plugin_files)) {
			return true;
		}
		// If the files are not identical, then they've changed.
		$old_plugin_checksums = array_map(create_function('$a', 'return $a["checksum"];'), $old_plugins);
		$new_plugin_checksums = array_map('md5_file', $new_plugin_files);
		if(count(array_intersect($old_plugin_checksums, $new_plugin_checksums)) != count($new_plugin_checksums)) {
			return true;
		}
		return false;
	}

	/**
	 * Stores the list of plugins that are present (not necessarily active) in
	 * the Options table for future comparison.
	 **/
	public static function set_present()
	{
		$plugin_files = Plugins::list_all();
		// strip base path
		foreach( $plugin_files as $plugin_file ) {
			$plugin_file = substr( $file, strlen( HABARI_PATH ) );
		}
		
		$plugin_data = array_map(create_function('$a', 'return array("file"=>$a, "checksum"=>md5_file($a));'), $plugin_files);
		Options::set('plugins_present', $plugin_data);
	}

	/**
	 * Verify if a plugin is loaded.
	 * You may supply an optional argument $version as a minimum version requirement.
	 *
	 * @param string $name Name or class name of the plugin to find.
	 * @param string $version Optional minimal version of the plugin.
	 * @return bool Returns true if name is found and version is equal or higher than required.
	 */
	public static function is_loaded( $name, $version = NULL )
	{
		foreach ( self::$plugins as $plugin ) {
			if ( strtolower($plugin->info->name) == strtolower($name) || $plugin instanceof $name || strtolower($plugin->info->guid) == strtolower($name)) {
				if ( isset( $version ) ) {
					return version_compare( $plugin->info->version, $version, '>=' );
				}
				else {
					return true;
				}
			}
		}
		return false;
	}

	/**
	 * Check the PHP syntax of every plugin available, activated or not.
	 *
	 * @see Utils::php_check_file_syntax()
	 * @return bool Returns true if all plugins were valid, return false if a plugin (or more) failed.
	 */
	public static function check_every_plugin_syntax()
	{
		$failed_plugins = array();
		$all_plugins = self::list_all();

		foreach ( $all_plugins as $file ) {
			$error = '';
			if ( !Utils::php_check_file_syntax( $file, $error ) ) {
				Session::error(sprintf( _t( 'Attempted to load the plugin file "%s", but it failed with syntax errors. <div class="reveal">%s</div>' ), basename( $file ), $error ));
				$failed_plugins[] = $file;
			}
		}

		Options::set( 'failed_plugins', $failed_plugins );
		Plugins::set_present();

		return ( count($failed_plugins) > 0 ) ? false : true;
	}
}

?>
