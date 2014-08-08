<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<link rel="stylesheet" type="text/css" href="style.css">
<title>Установка uCMS</title>
</head>
<body>
<div id="page-wrap">
<div id="content">
<?php
session_start();
$install = true;
require 'stages.php';
$file = "../../config.php";
if(!file_exists($file) and !file_exists("../".$file)){
	if(isset($_POST['continue']))
		make_config();
	else
		welcome();
}else{
	if(isset($_SESSION['connected']) and !$_SESSION['connected']){
		no_connect();
	}else{
		require 'update.php';
		if(file_exists($file)){
			require '../../config.php';
			if(isset($_SESSION['update-config'])){
				update_config();
				unset($_SESSION['update-config']);
			}
		}else if(file_exists("../".$file)){
			require '../../../config.php';
		}
		if(!isset($con) or !$con){
			no_connect();
		}else{
			$test = $udb->query("SELECT `ip` FROM `".UC_PREFIX."attempts` LIMIT 1", true);
			$test2 = $udb->query("SELECT `id` FROM `".UC_PREFIX."categories` LIMIT 1", true);
			$test3 = $udb->query("SELECT `id` FROM `".UC_PREFIX."comments` LIMIT 1", true);
			$test4 = $udb->query("SELECT `id` FROM `".UC_PREFIX."groups` LIMIT 1", true);
			$test5 = $udb->query("SELECT `id` FROM `".UC_PREFIX."messages` LIMIT 1", true);
			$test6 = $udb->query("SELECT `id` FROM `".UC_PREFIX."pages` LIMIT 1", true);
			$test7 = $udb->query("SELECT `id` FROM `".UC_PREFIX."posts` LIMIT 1", true);
			$test8 = $udb->query("SELECT `id` FROM `".UC_PREFIX."themes` LIMIT 1", true);
			$test9 = $udb->query("SELECT `id` FROM `".UC_PREFIX."users` LIMIT 1", true);
			$test10 = $udb->query("SELECT `id` FROM `".UC_PREFIX."settings` LIMIT 1", true);
			$test11 = $udb->query("SELECT `id` FROM `".UC_PREFIX."stats` LIMIT 1", true);
			$test12 = $udb->query("SELECT `id` FROM `".UC_PREFIX."usersinfo` LIMIT 1", true);
			$test13 = $udb->query("SELECT `id` FROM `".UC_PREFIX."links` LIMIT 1", true);
			$test14 = $udb->query("SELECT `id` FROM `".UC_PREFIX."widgets` LIMIT 1", true);
			if(!$test or !$test2 or !$test3 or !$test4 or !$test5 or !$test6 or !$test7 or !$test8 or !$test9 or !$test10 or !$test11 or !$test12 or !$test13 or !$test14){
				make_tables();
			}else{
				$settings = $udb->num_rows("SELECT `id` FROM `".UC_PREFIX."settings`", true);
				$users = $udb->num_rows("SELECT `id` FROM `".UC_PREFIX."users`", true);
				
				if($settings <= 0)
					fill_settings();
				else if($users <= 0)
					fill_users();
				else{
					$categories = $udb->num_rows("SELECT * FROM `".UC_PREFIX."categories`");
					$themes = $udb->num_rows("SELECT * FROM `".UC_PREFIX."themes`");
					$stats = $udb->num_rows("SELECT * FROM `".UC_PREFIX."stats`");
					$groups = $udb->num_rows("SELECT * FROM `".UC_PREFIX."groups`");
					$widgets = $udb->num_rows("SELECT * FROM `".UC_PREFIX."widgets`");
					if($categories <= 0 or $themes <= 0 or $stats <= 0 or $groups <= 0 or $widgets <= 0){
						header("Location: setup.php");
						$_SESSION['fill-tables'] = true;

					}
				 	if($settings < 30){
						update_tables();
				 		update_settings();
				 		update_users();
				 		update_themes();
				 	}
				 	if(isset($_SESSION['success']))
						success();
					else
						fine();
				}
			}
		}
	}	
}
?>
</div>
</div>
</body>
</html>