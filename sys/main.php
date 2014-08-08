<?php
/**
* uCMS Main Query handler
*
* @package uCMS
* @since 1.0
* @version 1.3
*
*/
if ( UCMS_MAINTENANCE and !$user->has_access("system") and !isset($_POST['login']) and !isset($_POST['password']) ) { // Check maintenance mode
	$ucms->template(ERROR_TEMPLATES_PATH."maintenance.php");
	exit;
}

if ((!isset($_SERVER['HTTP_HOST']) or preg_replace("#(www.)#", "", $_SERVER['HTTP_HOST']) != preg_replace("#(http://)#", "", SITE_DOMAIN)) 
	and !EMBEDDING_ALLOWED and !isset($_POST['login']) and !isset($_POST['password']) ) {
    header($_SERVER['SERVER_PROTOCOL'].' 400 Bad Request');
    require ERROR_TEMPLATES_PATH."no_iframe.php"; // If embedding is not allowed - block site with error message
    exit;
}

$titles = array('index' => SITE_TITLE, 'error404' => $ucms->cout('ucms.page_not_found.title', true)); // Base titles
$include_files = array('index.php', 'main.php');
$url_actions = array('index', 'redirect'); // Base actions
$require_params = array(false, true); // If action need parameters or not
$action_exec_dirs = array(THEMEPATH, UC_SYS_PATH);

require ABSPATH.UC_INCLUDES_PATH."modules_url_actions.php"; // Loading modules actions

if(NICE_LINKS){
	$action = $url_all[1];
}
if(isset($_GET['action']))
	$action = trim($_GET['action']);
if(!isset($action) or $action == '' or $action == 'index.php') $action = 'index';

if(!in_array($action, $url_actions)){
	$action = 'other';	// If the action is not used then it will be replaced with 'other'
}
$key = array_search($action, $url_actions);
$dir = '';

if($require_params[$key] and (NICE_LINKS and empty($url_all[2]) or !NICE_LINKS and count($_GET) <= 1)){
	$action = 'other';
	$key = array_search($action, $url_actions);
}

if(isset($action_exec_dirs[$key]))
	$dir = $action_exec_dirs[$key];
$file = "$dir$action-load.php";
if(isset($include_files[$key]) and $include_files[$key] != ''){
	$file = $dir.$include_files[$key];
}

if($action == 'redirect'){
	redirect(); // Redirect function
	exit;
}
require $file; // Loading module handler for action

$event->do_actions("site.shutdown"); // Executing shutdown functions
?>