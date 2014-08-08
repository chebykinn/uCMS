<?php
if($user->logged()){
	header("Location: ".UCMS_DIR."/");
}
require_once 'sys/users/login.php';
$login = new login();
$domain = preg_replace("#(".SITE_DOMAIN."/".UCMS_DIR.")#", "", $ucms->get_back_url());
if(!preg_match("/admin|login/", $domain)){
	if(isset($_SESSION['admin-login']))
		unset($_SESSION['admin-login']);
}
if(file_exists(THEMEPATH.'login.php') and !UCMS_MAINTENANCE and !preg_match("/admin/", $ucms->get_back_url()) and !isset($_SESSION['admin-login']) ){
	require THEMEPATH.'login.php';
	if(isset($_SESSION['admin-login']))
		unset($_SESSION['admin-login']);
}else{ 
	$_SESSION['admin-login'] = true;
	require GENERAL_TEMPLATES_PATH.'login.php';
}

$udb->db_disconnect($con);
?>