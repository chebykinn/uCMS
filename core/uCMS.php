<?php
namespace uCMS\Core;

class uCMS{
	const CORE_VERSION = "2.0 Alpha 3";
	const COMPATIBILITY_VERSION = "2.x";
	const MIN_PHP_VERSION = "5.3";
	const MIN_MYSQL_VERSION = "5.0";

	public static function GetDirectory(){
		return Settings::Get('ucms_dir');
	}
}

if( !defined("UCMS_DIR") ){
	define("UCMS_DIR", '/');
}
?>