<?php
/**
 * Database connection samples
 * 1. Uncomment the section that applies to you
 * 2. Change the connection string, username, and password, if required. 
 * 3. ...
 * 4. Profit!  
 **/   


/**
 * MySQL Connection Details
 ** 

$db_connection = array(
'connection_string' => 'mysql:host=habari.fearsome-engine.com;dbname=habari',  // MySQL Connection string
'username' => 'mysql username',  // MySQL username
'password' => 'mysql password',  // MySQL password
);

*/

/**
 * SQLite Connection Details
 **

$db_connection = array(
'connection_string' => 'sqlite:' . dirname(__FILE__) . '/habari.db',  // SQLite Connecton string
'username' => null,  // SQLite username
'password' => null,  // SQLite password
);

*/

?>
