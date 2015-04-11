<?php
if( file_exists('config.php') ){
	require 'config.php';
}else if( file_exists('../config.php') ){
	define("ABSPATH", dirname(__FILE__)."/");
	require '../config.php';
}

if( !defined("ABSPATH") ){
	//install
}

uCMS::getInstance()->init();

uCMS::getInstance()->runSite();
?>