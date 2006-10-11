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

//$connection_string = 'mysql:host=habari.fearsome-engine.com;dbname=habari';  // MySQL Connection string
$connection_string = 'mysql:host=habari.fearsome-engine.com;dbname=habari'; 
$db = new habari_db( $connection_string, 'chrisjdavis', 'walker' );
?>