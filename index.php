<?php
if(!file_exists("config.php")){ 
	if(!file_exists("../config.php")){
		header("Location: sys/install/index.php"); // If config file is not exists then run installation process
		exit;
	}else{ 
		define(ABSPATH, dirname(__FILE__)."/");
		require '../config.php';
	}
}else{
	require 'config.php';
}
require 'sys/main.php'; // Begin loading
?>