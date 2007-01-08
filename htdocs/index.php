<?php
/**
 * Habari index.php
 * 
 * Handles all incoming requests, connects to the database, sets the locale, etc.
 *     
 * @package Habari
 */

 // version check and die if below requirement
 $version_array = explode( '.', phpversion());

 if ( $version_array[0] < 5 ) {
	die ( 'Habari is designed to run on PHP5 or higher. You are currently running PHP ' . PHP_VERSION);
}

// set our constant
define('HABARI_PATH', dirname(__FILE__));

/**
 * function __autoload
 * Autoloads class files for undeclared classes.
 **/  
function __autoload($class_name) {
	if(file_exists(HABARI_PATH . '/user/classes/' . strtolower($class_name) . '.php'))
		require_once HABARI_PATH . '/user/classes/' . strtolower($class_name) . '.php';
	else if(file_exists(HABARI_PATH . '/system/classes/' . strtolower($class_name) . '.php'))
		require_once HABARI_PATH . '/system/classes/' . strtolower($class_name) . '.php';
	else
		die( 'Could not include class file ' . strtolower($class_name) . '.php' );
}

// Register the error handler
Error::handle_errors(); 

// Undo what magic_quotes_gpc might have wrought
Utils::revert_magic_quotes_gpc();

// Load the config
if(file_exists(HABARI_PATH . '/config.php')) {
	require_once HABARI_PATH . '/config.php';
} else {
	die('There are no database connection details.  Please rename one of the sample configuration files (config.mysql, config.sqlite, ...) to config.php and edit the settings therein.');	
}

// Set the locale
Locale::set($locale);

// Connect to the database
DB::create( $db_connection['connection_string'], $db_connection['username'], $db_connection['password'], $db_connection['prefix'] );

// Install the database tables if they're not already installed
Installer::install();

// unset the $db_connection variable, since we don't need it any more
unset($db_connection);

// Activate all plugins - remove this when we have a plugin admin UI
foreach(Plugins::list_all() as $plugin) {
	Plugins::activate_plugin($plugin);
}

// Load plugins
foreach( Plugins::list_active() as $file ) {
	include_once($file);  // Include these files in the global namespace
}
Plugins::load();
Plugins::act('plugins_loaded');

// Figure out what the user requested and do something about it
$url = ( isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : $_SERVER['SCRIPT_NAME'] . ( isset($_SERVER['PATH_INFO']) ? $_SERVER['PATH_INFO'] : '') . ( (isset($_SERVER['QUERY_STRING']) && ($_SERVER['QUERY_STRING'] != '')) ? '?' . $_SERVER['QUERY_STRING'] : ''));
$url = new URL( $url );
$url->handle_request();
//Update::check('foo', 5);


?>
