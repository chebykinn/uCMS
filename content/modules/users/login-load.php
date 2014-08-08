<?php
if($user->logged()){
	header("Location: ".UCMS_DIR."/");
}
add_title($action, 'module.users.site.title.login');

$domain = preg_replace("#(".SITE_DOMAIN."/".UCMS_DIR.")#", "", $ucms->get_back_url());
if(!preg_match("/admin|login/", $domain)){
	if(isset($_SESSION['admin-login']))
		unset($_SESSION['admin-login']);
}

if(isset($_POST['login']) and isset($_POST['password'])){
	$result = $login->authenticate();
}

if(file_exists($theme->get_path().'login.php') and !UCMS_MAINTENANCE and !preg_match("/admin/", $ucms->get_back_url()) and !isset($_SESSION['admin-login']) ){
	require $theme->get_path().'login.php';
	if(isset($_SESSION['admin-login']))
		unset($_SESSION['admin-login']);
}else{ 
	$_SESSION['admin-login'] = true;
	require GENERAL_TEMPLATES_PATH.'login.php';
}


?>