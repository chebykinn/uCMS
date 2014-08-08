<?php
$rtl = (bool) $ucms->get_language_info('rtl');
$admin_css = $rtl ? 'style_rtl.css' : 'style.css';
if(isset($title)){
	if($ucms->is_language_string_id($title)){
		$title = $ucms->cout($title, true);
	}
}else $title = '';
?>
<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">	
<?php $event->do_actions("admin.head"); ?>
<link rel="stylesheet" href="<?php echo $admin_css; ?>" type="text/css" media="screen">
<link rel="apple-touch-icon" href="<?php echo SITE_DOMAIN.UCMS_DIR; ?>/favicon.ico">
<script type="text/javascript" src="<?php echo SITE_DOMAIN.UCMS_DIR; ?>/sys/include/jquery.js"></script>
<script type="text/javascript" src="<?php echo SITE_DOMAIN.UCMS_DIR; ?>/admin/scripts/admin.js"></script>
<title><?php 
if(UCMS_DEBUG) 
	echo "[DEBUG] "; 


if(preg_match("/settings.php/", $_SERVER['REQUEST_URI'])) 
	echo $ucms->cout("admin.settings.title", true)." :: ";

echo $title; 

$ucms->cout("admin.title"); ?> :: <?php site_info("name"); ?>
</title>
</head>
<body>
<?php
if(!$user->has_access("at_least_one", 3)){ 
	header("Location: ".UCMS_DIR."/admin/login.php");
	exit;
}
?>
<table id="main">
<tr>