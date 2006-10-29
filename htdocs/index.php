<?php
// set out constant
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

// Load the config
if(file_exists(HABARI_PATH . '/config.php')) {
	require_once HABARI_PATH . '/config.php';
} else {
	die('There are no database connection details.  Please rename one of the sample configuration files (config.mysql, config.sqlite, ...) to config.php and edit the settings therein.');	
}

// Connect to the database or fail informatively
try {
	$db = new habari_db( $db_connection['connection_string'], $db_connection['username'], $db_connection['password'] );
}
catch( Exception $e) {
	die( 'Could not connect to database using the supplied credentials.  Please check config.php for the correct values. Further information follows: ' .  $e->getMessage() );		
}
unset($db_connection);

// Install the database tables if they're not already installed
Installer::install();

// Figure out what the user requested and do something about it
$url = ( isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : $_SERVER['SCRIPT_NAME'] . ( isset($_SERVER['PATH_INFO']) ? $_SERVER['PATH_INFO'] : '') . ( (isset($_SERVER['QUERY_STRING']) && ($_SERVER['QUERY_STRING'] != '')) ? '?' . $_SERVER['QUERY_STRING'] : ''));
$urlparser = new URLParser( $url );
$urlparser->handle_request();

?>
