<?php
namespace uCMS\Core;

class uCMS{
	const CORE_VERSION = "2.0 Alpha 4";
	const COMPATIBILITY_VERSION = "2.x";
	const MIN_PHP_VERSION = "5.4";
	const MIN_MYSQL_VERSION = "5.0";
	const ERR_NOT_FOUND = 404;
	const ERR_FORBIDDEN = 403;
	const CONFIG_FILE = 'config.php';
	const CONFIG_SAMPLE = 'config-manual.php';

	public static function GetDirectory(){
		$storedValue = Settings::Get('ucms_dir');
		// TODO: more complex default value
		return empty($storedValue) ? '/' : $storedValue;
	}

	public static function GetDomain(){
		$storedValue = Settings::Get("site_domain");
		return empty($storedValue) ? $_SERVER['REQUEST_URI'] : $storedValue;
	}
}

if( !defined("UCMS_DIR") ){
	define("UCMS_DIR", '/');
}
?>