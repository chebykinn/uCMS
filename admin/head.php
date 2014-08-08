<?php
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
?>
<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />	
<link rel="stylesheet" href="style.css" type="text/css" media="screen"  />
<link rel="apple-touch-icon" href="/favicon.ico"/>
<script type="text/javascript" src="<?php echo UCMS_DIR; ?>/sys/include/jquery.js"></script>
<script type="text/javascript" src="<?php echo UCMS_DIR; ?>/admin/scripts/admin.js"></script>
<title><?php if(UCMS_DEBUG) echo "[DEBUG] "; if(isset($title)) echo $title; ?>Панель управления uCMS <?php echo UCMS_VERSION; ?> :: <?php site_info("name"); ?></title>
</head>
<body>
<?php
if(!$user->has_access(0, 3)){ 
header("Location: ".UCMS_DIR."/admin/login.php");
}
?>