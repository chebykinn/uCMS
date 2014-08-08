<?php
header("X-Info: Control Panel");
if(!file_exists("../config.php")){ 
	if(!file_exists("../../config.php")){
		header("Location: ../sys/install/index.php");
		exit;
	}else{ 
		$dirname = str_replace("admin", "", dirname(__FILE__));
		define(ABSPATH, $dirname);
		require '../../config.php';
	}
}else{
	require '../config.php';
}

require 'include/modules-admin.php';
require 'include/functions.php';
?>