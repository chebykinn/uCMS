<?php
/* Config version: 1.3 */

/** 
* Welcome to the uCMS source code! 
* This is The Configuration File. 
* It contains various important settings. 
* For more information visit: {@link http://ucms.ivan4b.ru/manuals/config_file}
* @package uCMS
*/

define("UC_PREFIX", "uc_"); // Table prefix, if you want to install multiple uCMS sites, set your own one.

define("DB_SERVER", "localhost"); // Database server

define("DB_USER", "login"); // Login

define("DB_PASSWORD", "password"); // Password

define("DB_NAME", "ucms_database"); // Database name

if(!defined("ABSPATH"))
	define("ABSPATH", dirname(__FILE__)."/"); // Absolute path to uCMS files

define("UCMS_DEBUG", false); // Change it to true to enter debug mode. Recommended for developers.

/* Loading necessary stuff */
require_once ABSPATH."sys/load.php";
?>