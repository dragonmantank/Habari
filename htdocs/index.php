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
	if(file_exists(HABARI_PATH . '/user/classes/' . strtolower($class_name) . '.php'))
		require_once HABARI_PATH . '/user/classes/' . strtolower($class_name) . '.php';
	else if(file_exists(HABARI_PATH . '/system/classes/' . strtolower($class_name) . '.php'))
		require_once HABARI_PATH . '/system/classes/' . strtolower($class_name) . '.php';
	else
		die( 'Could not include class file ' . strtolower($class_name) . '.php' );
}
error_reporting(E_ALL);
// Undo what magic_quotes_gpc might have wrought
Utils::revert_magic_quotes_gpc();

// Load the config
if(file_exists(HABARI_PATH . '/config.php')) {
	require_once HABARI_PATH . '/config.php';
  /*
   * This is a quick hack to load the DB tables if someone
   * already has a config.php set up, but no tables...
   * 
   * @todo  make an decent solution to this... involves an upgrade plan.
   */
  if (DB::connect()) {
    $sql= "SELECT COUNT(*) FROM " . $db_connection['prefix'] . "posts";
    if (! @ DB::query($sql)) {
      $installer= new InstallHandler();
      $installer->begin_install();
    }
  }
} 
else {
  /* 
   * Fire up the InstallHandler to create the 
   * configuration file in the proper directory
   * and install the base database
   */
  $installer= new InstallHandler();
  $installer->begin_install();
  //exit;
}

// Set the locale
Locale::set();
//Installer::install();

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
/*$start_url= ( isset($_SERVER['REQUEST_URI']) 
      ? $_SERVER['REQUEST_URI'] 
      : $_SERVER['SCRIPT_NAME'] . 
        ( isset($_SERVER['PATH_INFO']) 
        ? $_SERVER['PATH_INFO'] 
        : '') . 
        ( (isset($_SERVER['QUERY_STRING']) && ($_SERVER['QUERY_STRING'] != '')) 
          ? '?' . $_SERVER['QUERY_STRING'] 
          : ''));

$url= new URL($start_url);
*/
Controller::parse_request();
Controller::dispatch_request();
ob_flush();
?>
