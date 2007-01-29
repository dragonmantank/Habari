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
 * We start up output buffering in order to take advantage
 * of output compression, as well as the ability to 
 * dynamically change HTTP headers after output has started.
 */
ob_start();

/**
 * function __autoload
 * Autoloads class files for undeclared classes.
 **/  
function __autoload($class_name) {
	$class_file = strtolower($class_name) . '.php';
	if(file_exists(HABARI_PATH . '/user/classes/' . $class_file))
		require_once HABARI_PATH . '/user/classes/' . $class_file;
	else if(file_exists(HABARI_PATH . '/system/classes/' . $class_file))
		require_once HABARI_PATH . '/system/classes/' . $class_file;
	else
		die( 'Could not include class file ' . $class_file );
}
error_reporting(E_ALL);
// Undo what magic_quotes_gpc might have wrought
Utils::revert_magic_quotes_gpc();

// find and load the config.php file
$config = Utils::get_config_dir() . '/config.php';

// Load the config
if ( file_exists($config) ) {
	require_once $config;
	if ( ! isset($db_connection) )
	{
		$installer= new InstallHandler();
		$installer->begin_install();
	}
	/*
	 * This is a quick hack to load the DB tables if someone
	 * already has a config.php set up, but no tables...
	 * 
	 * @todo  make an decent solution to this... involves an upgrade plan.
	 */
	if (DB::connect())
	{
		$sql= "SELECT COUNT(*) FROM " . $db_connection['prefix'] . "posts";
		if (! @ DB::query($sql))
		{
			$installer= new InstallHandler();
			$installer->begin_install();
		}
	}
} 
else
{
	/* 
	 * Fire up the InstallHandler to create the 
	 * configuration file in the proper directory
	 * and install the base database
	 */
	$installer= new InstallHandler();
	$installer->begin_install();
}

// Set the locale
Locale::set();

// Activate all plugins - remove this when we have a plugin admin UI
foreach(Plugins::list_all() as $plugin) {
	Plugins::activate_plugin($plugin);
}

// Load plugins
foreach( Plugins::list_active() as $file ) {
	//include_once($file);  // Include these files in the global namespace
}
Plugins::load();
Plugins::act('plugins_loaded');

// parse and handle the request
Controller::parse_request();
Controller::dispatch_request();

// flush the contents of output buffering
ob_flush();
?>
