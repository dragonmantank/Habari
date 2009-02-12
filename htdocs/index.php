<?php /*

  Copyright 2007-2009 The Habari Project <http://www.habariproject.org/>

  Licensed under the Apache License, Version 2.0 (the "License");
  you may not use this file except in compliance with the License.
  You may obtain a copy of the License at

      http://www.apache.org/licenses/LICENSE-2.0

  Unless required by applicable law or agreed to in writing, software
  distributed under the License is distributed on an "AS IS" BASIS,
  WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
  See the License for the specific language governing permissions and
  limitations under the License.

*/ ?>
<?php
/**
 * Habari Index
 *
 * This is where all the magic happens:
 * 1. Validate the installation
 * 2. Set the locale
 * 3. Load the active plugins
 * 4. Parse and handle the incoming request
 * 5. Run the cron jobs
 * 6. Dispatch the request to the found handler
 *
 * @package Habari
 */

// Compares PHP version against our requirement.
if ( !version_compare( PHP_VERSION, '5.2.0', '>=' ) ) {
	die ( 'Habari needs PHP 5.2.x or higher to run. You are currently running PHP ' . PHP_VERSION . '.' );
}

// Increase the error reporting level: E_ALL, E_NOTICE, and E_STRICT
error_reporting( E_ALL | E_NOTICE | E_STRICT );

// set a default timezone for PHP. Habari will base off of this later on
date_default_timezone_set( 'UTC' );

/**
 * Start the profile time
 */
$profile_start = microtime(true);

/**
 * Define the constant HABARI_PATH.
 * The path to the root of this Habari installation.
 */
if ( !defined( 'HABARI_PATH' ) ) {
	define( 'HABARI_PATH', dirname( __FILE__ ) );
}

/**
 * Make GLOB_BRACE available on platforms that don't have it. Use Utils::glob().
 */
if ( !defined( 'GLOB_BRACE' ) ) {
	define( 'GLOB_NOBRACE', true );
	define( 'GLOB_BRACE', 128 );
}

// We start up output buffering in order to take advantage of output compression,
// as well as the ability to dynamically change HTTP headers after output has started.
ob_start();

// Replace all of the $_GET, $_POST and $_SERVER superglobals with object
// representations of each.  Unset $_REQUEST, which is evil.
// $_COOKIE must be set after sessions start
SuperGlobal::process_gps();

/**
 * Attempt to load the class before PHP fails with an error.
 * This method is called automatically in case you are trying to use a class which hasn't been defined yet.
 *
 * We look for the undefined class in the following folders:
 * - /system/classes/*.php
 * - /user/classes/*.php
 * - /user/sites/x.y.z/classes/*.php
 *
 * @param string $class_name Class called by the user
 */
function __autoload($class_name) {
	static $files = null;

	$success = false;
	$class_file = strtolower($class_name) . '.php';

	if ( empty($files) ) {
		$files = array();
		$dirs = array( HABARI_PATH . '/system', HABARI_PATH . '/user' );

		// For each directory, save the available files in the $files array.
		foreach ($dirs as $dir) {
			$glob = glob( $dir . '/classes/*.php' );
			if ( $glob === false || empty( $glob ) ) continue;
			$fnames = array_map(create_function('$a', 'return strtolower(basename($a));'), $glob);
			$files = array_merge($files, array_combine($fnames, $glob));
		}

		// Load the Site class, a requirement to get files from a multisite directory.
		if ( isset($files['site.php']) ) {
			require $files['site.php'];
		}

		// Verify if this Habari instance is a multisite.
		if ( ($site_user_dir = Site::get_dir('user')) != HABARI_PATH . '/user' ) {
			// We are dealing with a site defined in /user/sites/x.y.z
			// Add the available files in that directory in the $files array.
			$glob = glob( $site_user_dir . '/classes/*.php' );
			if ( $glob !== false && !empty( $glob ) ) {
			        $fnames = array_map(create_function('$a', 'return strtolower(basename($a));'), $glob);
				$files = array_merge($files, array_combine($fnames, $glob));
			}
		}
	}

	// Search in the available files for the undefined class file.
	if ( isset($files[$class_file]) ) {
		require $files[$class_file];
		// If the class has a static method named __static(), execute it now, on initial load.
		if ( class_exists($class_name, false) && method_exists($class_name, '__static') ) {
			call_user_func(array($class_name, '__static'));
		}
		$success = true;
	}
}

spl_autoload_register('__autoload');

// Use our own error reporting class.
Error::handle_errors();

/* Initiate install verifications */

// Retrieve the configuration file's path.
$config = Site::get_dir( 'config_file' );

/**
 * We make sure the configuration file exist.
 * If it does, we load it and check it's validity.
 *
 * @todo Call the installer from the database classes.
 */
if ( file_exists( $config ) ) {
	require_once $config;

	// Set the default locale.
	HabariLocale::set( isset($locale) ? $locale : 'en-us' );

	if ( !defined( 'DEBUG' ) ) define( 'DEBUG', false );

	// Make sure we have a DSN string and database credentials.
	// $db_connection is an array with necessary informations to connect to the database.
	if ( !isset($db_connection) ) {
		$installer = new InstallHandler();
		$installer->begin_install();
	}

	// Try to connect to the database.
	if ( DB::connect() ) {
		// Make sure Habari is installed properly.
		// If the 'installed' option is missing, we assume the database tables are missing or corrupted.
		// @todo Find a decent solution, we have to compare tables and restore or upgrade them.
		if ( !@ Options::get('installed') ) {
			$installer = new InstallHandler();
			$installer->begin_install();
		}
	}
	else {
		$installer = new InstallHandler();
		$installer->begin_install();
	}
}
else {
	if ( !defined( 'DEBUG' ) ) define( 'DEBUG', false );

	// The configuration file does not exist.
	// Therefore we load the installer to create the configuration file and install a base database.
	$installer = new InstallHandler();
	$installer->begin_install();
}

/* Habari is installed and we established a connection with the database */

// Set the locale from database or default locale
if ( Options::get('locale') ) {
	HabariLocale::set( Options::get('locale') );
}
else {
	HabariLocale::set( 'en-us' );
}
if ( Options::get( 'system_locale' ) ) {
	HabariLocale::set_system_locale( Options::get( 'system_locale' ) );
}

// Verify if the database has to be upgraded.
if ( Version::requires_upgrade() ) {
	$installer = new InstallHandler();
	$installer->upgrade_db();
}

// If we're doing unit testing, stop here
if ( defined( 'UNIT_TEST' ) ) {
	return;
}

// if this is an asyncronous call, ignore abort.
if ( isset( $_GET['asyncronous'] ) && Utils::crypt( Options::get( 'guid' ), $_GET['asyncronous'] ) ) {
	ignore_user_abort( true );
}

// Send the Content-Type HTTP header.
// @todo Find a better place to put this.
header( 'Content-Type: text/html;charset=utf-8' );

/**
 * Include all the active plugins.
 * By loading them here they'll have global scope.
 *
 * We loop through them twice so we can cache all plugin classes on the first load() call.
 * This gives about 60% improvement.
 */
foreach ( Plugins::list_active() as $file ) {
	include_once( $file );
}
// Call the plugin's load procedure.
foreach ( Plugins::list_active() as $file ) {
	Plugins::load( $file );
}

// All plugins loaded, tell the plugins.
Plugins::act('plugins_loaded');

// Start the session.
Session::init();

// Replace the $_COOKIE superglobal with an object representation
SuperGlobal::process_c();

// Initiating request handling, tell the plugins.
Plugins::act('init');

// Parse and handle the request.
Controller::parse_request();

// Run the cron jobs asyncronously.
CronTab::run_cron(true);

// Dispatch the request (action) to the matched handler.
Controller::dispatch_request();

// Flush (send) the output buffer.
$buffer = ob_get_clean();
$buffer = Plugins::filter('final_output', $buffer);
echo $buffer;
?>
