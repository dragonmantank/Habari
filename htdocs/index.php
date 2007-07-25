<?php
/**
 * Habari index.php
 *
 * Handles all incoming requests, connects to the database, sets the locale, etc.
 *
 * @package Habari
 */

// version check and die if below requirement
if ( ! version_compare( PHP_VERSION, '5.1.0', '>=' ) ) {
	die ( 'Habari needs PHP 5.1.x or higher to run. You are currently running PHP ' . PHP_VERSION . '.' );
}

// set our constant
define( 'HABARI_PATH', dirname( __FILE__ ) );

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
	$success= false;
	$class_file = strtolower($class_name) . '.php';	
	
	$dirs= array(  HABARI_PATH . '/user', HABARI_PATH . '/system' );

	if(class_exists('Site'))
	{
		if ( Site::is('multi') )
		{
			// this is a site defined in /user/sites/x.y.z
			// so prepend that directory to the list of
			// directories to check for class files
			array_unshift( $dirs, Site::get_dir('user') );
		}
	}
	
		// iterate over the array of possible directories
		foreach ($dirs as $dir)
		{
		if(file_exists($dir . '/classes/' . $class_file))
		{
			require_once $dir . '/classes/' . $class_file;
			$success= true;
			break;
	}
	}
	
	// still no luck? maybe we want a database driver
	if ( ! $success)
	{
		preg_match( '/(\w+):?connection/i', strtolower($class_name), $captures );		
		if ( isset( $captures[0]) ) {			
			require_once(HABARI_PATH . "/system/schema/$captures[1]/connection.php" );
			$success= true;
		}
	}

	if ( ! $success )
	{		
		die( 'Could not include class file ' . $class_file );
	}
}
// Up the error reporting
error_reporting(E_ALL);
// Install our own error handler
Error::handle_errors();

// find and load the config.php file
$config = Site::get_dir('config_file');

// Load the config
if ( file_exists($config) ) {
	require_once $config;
	if ( ! isset($db_connection) ) {
		$installer= new InstallHandler();
		$installer->begin_install();
	}
	/*
	 * This is a quick hack to load the DB tables if someone
	 * already has a config.php set up, but no tables...
	 *
	 * @todo  make an decent solution to this... involves an upgrade plan.
	 */

	if (DB::connect()) {
		if (! @ Options::get('installed')) {
			$installer= new InstallHandler();
			$installer->begin_install();
		}
	}
	else {
		$installer= new InstallHandler();
		$installer->begin_install();
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

// We have a database connection.  Check the version and upgrade if needed.
if ( Version::requires_upgrade() ) {
	$installer= new InstallHandler();
	$installer->upgrade_db();
}

// XXX this is probably not the best place to put this
header( 'Content-Type: text/html;charset=utf-8' );

// Set the locale
Locale::set( 'en-us' );

// include all plugins here, so that they have global scope
foreach ( Plugins::list_active() as $file )
{
	include_once( $file );
	// execute this plugin's load() method
	Plugins::load( $file );
}
Plugins::act('plugins_loaded');
Plugins::act('init');

// parse and handle the request
Controller::parse_request();
CronTab::run_cron();
Controller::dispatch_request();

// flush the contents of output buffering
ob_flush();
?>
