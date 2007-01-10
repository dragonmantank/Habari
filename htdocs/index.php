<?php
/**
 * Habari index.php
 * 
 * Handles all incoming requests, connects to the database, sets the locale, etc.
 *     
 * @package Habari
 */
 
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

// Connect to the database or fail informatively
try {
	DB::create( $db_connection['connection_string'], $db_connection['username'], $db_connection['password'], $db_connection['prefix'] );
}
catch( Exception $e) {
	die( 'Could not connect to database using the supplied credentials.  Please check config.php for the correct values. Further information follows: ' .  $e->getMessage() );		
}

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

$start_url= ( isset($_SERVER['REQUEST_URI']) 
      ? $_SERVER['REQUEST_URI'] 
      : $_SERVER['SCRIPT_NAME'] . 
        ( isset($_SERVER['PATH_INFO']) 
        ? $_SERVER['PATH_INFO'] 
        : '') . 
        ( (isset($_SERVER['QUERY_STRING']) && ($_SERVER['QUERY_STRING'] != '')) 
          ? '?' . $_SERVER['QUERY_STRING'] 
          : ''));

$url= new URL($start_url);

// Create a new RewriteController to translate the incoming slug
$controller= new RewriteController();
$controller->dispatch_request();
?>
