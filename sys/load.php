<?php
/**
* uCMS load stage.
*
* @package uCMS
* @since 1.0
*
*/
if (!isset($_SESSION)){
	session_start(); // Starting sessions
}
@mb_internal_encoding("UTF-8");
/* Start load timer */

$current_time = microtime();
$current_time = explode(" ",$current_time);
$time_start = $current_time[1] + $current_time[0];

if(!defined("ABSPATH")){ // Check installation
	header("Location: sys/install/index.php");
	exit;
}

require ABSPATH.'sys/include/defines.php'; 

init_constants(); // Initializing base constants

require ABSPATH.UC_SYS_PATH.'ucms.php'; 
$ucms = new uCMS(); // Registering uCMS class object

register_shutdown_function( "uCMS::fatal_error_handler" );
set_error_handler('uCMS::error_handler'); // Redirecting PHP errors to own handler

require ABSPATH.UC_INCLUDES_PATH.'udb.php';
$udb = new uDB();
$con = $udb->db_connect(DB_SERVER, DB_USER, DB_PASSWORD, DB_NAME); // Connecting to database
$ucms->check_php_mysql_version(); // Checking PHP and MySQL versions

if(!isset($install)){

	ini_set('display_errors', 0);
	if(UCMS_DEBUG){ // Debug mode preparation
		error_reporting(E_ALL);
		ini_set('log_errors', 1);
		ini_set('error_log', ABSPATH.UC_CONTENT_PATH . 'debug.log');
	}else{
		error_reporting(E_ALL ^ (E_DEPRECATED | E_NOTICE | E_STRICT));
	}
	
	/* Settings */

	$setting = $udb->get_rows("SELECT * FROM `".UC_PREFIX."settings`");

	if($setting and count($setting) >= SETTINGS_NUM){ // Checking base settings
		/**
		* Registering settings 
		* @since 1.2
		*/
		define_settings();
	}else{
		header("Location: sys/install/index.php"); // if number of settings is less than it should be then run installation process
		exit;
	}

	$ucms->set_language();

	if(setlocale(LC_ALL, SYSTEM_LANGUAGE.".utf8") === false) // Setting locale for date and other stuff
		setlocale(LC_ALL, "en_US.utf8");

	require ABSPATH.UC_INCLUDES_PATH.'events.php';

	$event = new uEvents();

	require ABSPATH.UC_SYS_PATH.'users_min.php'; // If users module is disabled
	$user = new users_min();

	require ABSPATH.UC_SYS_PATH.'widgets_min.php'; // If widgets module is disabled
	$widget = new widgets_min();

	require ABSPATH.UC_INCLUDES_PATH.'functions.php'; // Additional functions
	include ABSPATH.UC_INCLUDES_PATH.'cron.php';
	require ABSPATH.UC_INCLUDES_PATH.'modules.php'; // Loading API for modules
	require ABSPATH.UC_INCLUDES_PATH."modules_autoload.php"; // Loading modules

	if(UC_CRON)
		run_scheduled_events(); // Run cron events

	$event->do_actions("site.loaded");
}
?>