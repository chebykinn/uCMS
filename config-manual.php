<?php
/**
* Welcome to the uCMS source code! 
* This Is The Configuration File. 
* It contains various important settings. 
* For more information visit: {@link http://ucms.ivan4b.ru/manuals/config_file}
* @package uCMS
*/
$GLOBALS['databases'] = [ 
	"default" => [ // Default Database keeps uCMS data
		"name"     => "%name%",  // Database name
		"server"   => "%server%",  // Database server
		"port"     => "%port%", // Database server port
		"user"     => "%user%",  // Login
		"password" => "%password%", // Password
		"prefix"   => "%prefix%" // Table prefix, if you want to install multiple uCMS sites, set your own one
	]
];

if(!defined("ABSPATH"))
	define("ABSPATH", dirname(__FILE__)."/"); // Absolute path to uCMS files

define("UCMS_DEBUG", false); // Change it to true to enter debug mode. Recommended for developers.

define("UCMS_HASH_SALT", "%salt%");
/* Loading necessary stuff */
require_once ABSPATH."core/autoload.php";
?>