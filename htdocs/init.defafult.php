<?php
/**
 * Habari Initialization Class
 *
 * Requires PHP5.0.4 or later
 * @package Habari
 */

function __autoload($class_name) {   
    require_once 'system/classes/' . $class_name . '.php';
}

$connection_string = 'mysql:host=localhost;dbname=habari'; // shouldn't need to change this 
$db = new habari_db( $connection_string, 'username', 'password' );
?>