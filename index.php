<?php
if( file_exists('config.php') ){
	require 'config.php';
}else if( file_exists('../config.php') ){
	define("ABSPATH", getcwd()."/");
	require '../config.php';
}

if( !defined("ABSPATH") ){
	//install
	echo "install";
	exit;
}
use uCMS\Core\Loader;
Loader::GetInstance()->init();

Loader::GetInstance()->runSite();
exit;
?>