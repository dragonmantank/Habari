<?php
/**
* Habari Version Class
*
* Base class for managing metadata about various Habari objects
*
* @package Habari
*/

class Version
{
	// DB and API versions are aligned with the SVN revision
	// number in which they last changed.
	const DB_VERSION = 1312;
	const API_VERSION = 1366;

	const HABARI_VERSION = '0.5-alpha';
	
	// Experimental detection of whether this checkout was from trunk or a tag/branch
	const HABARI_SVN_HEAD_URL = '$HeadURL$';

	public static function get_dbversion()
	{
		return Version::DB_VERSION;
	}

	public static function get_apiversion()
	{
		return Version::API_VERSION;
	}

	public static function get_habariversion()
	{
		return Version::HABARI_VERSION;
	}

	public static function save_dbversion()
	{
		Options::set('db_version', Version::DB_VERSION);
	}

	public static function requires_upgrade()
	{
		if (Options::get('db_version') < Version::DB_VERSION){
			return true;
		}
		return false;
	}
}

?>
