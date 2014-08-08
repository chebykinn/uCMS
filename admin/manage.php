<?php
include "config.php";
include "head.php";
if(!isset($manage_file)){
	header("Location: index.php");
	exit;
}
if(!$user->has_access($module_accessID, $module_accessLVL)){
	header("Location: index.php");
	exit;
}
include "sidebar.php";

if(file_exists(ABSPATH.MODULES_PATH.$_GET['module']."/".$manage_file)){
	require ABSPATH.MODULES_PATH.$_GET['module']."/".$manage_file;
}
include "footer.php"; ?>