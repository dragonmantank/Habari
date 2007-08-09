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
	static $files= null;

	$success= false;
	$class_file = strtolower($class_name) . '.php';	

	// Are the files in the directory loaded?
	if( empty($files) ) { 
		$files = array();
		$dirs= array( HABARI_PATH . '/system', HABARI_PATH . '/user' );
	
		// iterate over the array of possible directories
		foreach ($dirs as $dir) {
			$glob = glob( $dir . '/classes/*.php' );
			if(count($glob)==0) continue;
			$fnames = array_map(create_function('$a', 'return strtolower(basename($a));'), $glob);
			$files = array_merge($files, array_combine($fnames, $glob));
		}
		// Proload the Site class to get the classes from the site directory
		if(isset($files['site.php'])) {
			require_once $files['site.php'];
		}
		if ( ($site_user_dir = Site::get_dir('user')) != HABARI_PATH . '/user' ) {
			// this is a site defined in /user/sites/x.y.z
			// so prepend that directory to the list of
			// directories to check for class files
			$glob = glob( $site_user_dir . '/classes/*.php' );
			$fnames = array_map(create_function('$a', 'return strtolower(basename($a));'), $glob);
			$files = array_merge($files, array_combine($fnames, $glob));
		}
	}

	if(isset($files[$class_file])) {
		require_once $files[$class_file];
		// If the class has a static member named __static(), execute it now, on initial load.
		if(class_exists($class_name, false) && method_exists($class_name, '__static') ) {
			call_user_func(array($class_name, '__static'));
		}
		$success= true;
	}

	if ( ! $success ) {
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
