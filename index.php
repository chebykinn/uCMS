<?php
if(!file_exists("config.php")){ 
	if(!file_exists("../config.php")){
		header("Location: sys/install/index.php");
		exit;
	}else{ 
		define(ABSPATH, dirname(__FILE__)."/");
		require '../config.php';
	}
}else{
	require 'config.php';
}
require 'sys/main.php';
?>