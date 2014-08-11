<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<link rel="stylesheet" type="text/css" href="style.css">
<?php
session_start();
$install = true;
require 'setup.php';
require 'update.php';
require 'settings.php';
if(!file_exists('install.log')){
	touch('install.log');
}
if (!defined("DATETIME_FORMAT")){								// Datetime format string 
	define("DATETIME_FORMAT", "%Y-%m-%d %H:%M:%S");
}
if(!defined("ABSPATH"))
	define("ABSPATH", dirname("../../index.php")."/");

if(!defined("LANGUAGES_PATH")){	
	define("LANGUAGES_PATH", 'content/languages/');
}

if(!defined("UCMS_MIN_PHP_VERSION")){	
	define("UCMS_MIN_PHP_VERSION", 5.2);
}

$file = "../../config.php";
$exists = false;

if(file_exists($file)){
	$exists = true;
}else if(file_exists("../".$file)){
	$file = "../".$file;
	$exists = true;
}
if($exists){
	$conf_test = file($file);
	require $file;
	if(!isset($conf_test[1]) or trim($conf_test[1]) != "/* Config version: 1.3 */"){
		update_config($file);
	}
}

if(!isset($_SESSION['lang']) or $_SESSION['lang'] == "")
	$_SESSION['lang'] = isset($_POST['lang']) ? $_POST['lang'] : '';
if(isset($_POST['lang'])){
	header("Location: index.php");
	exit;
}

if( isset($con) and !$con and $exists and !isset($_GET['connected']) and (!isset($_GET['action']) or $_GET['action'] != 'done-config') and !isset($_POST['make-config'])){
	header("Location: index.php?action=done-config&connected=0");
	exit;
}

if($exists and $con){
	if(isset($_SESSION['modules_enabled'])){
		$udb->query("UPDATE `".UC_PREFIX."settings` SET `value` = '0' WHERE `name` = 'modules_enabled'", true);
	}
	$lang = $udb->get_row("SELECT `value` FROM `".UC_PREFIX."settings` WHERE `name` = 'system_language' LIMIT 1", true);
	$modules = $udb->get_row("SELECT `value` FROM `".UC_PREFIX."settings` WHERE `name` = 'modules_enabled' LIMIT 1", true);
}

if(isset($lang) and $lang and count($lang) > 0){
	define("SYSTEM_LANGUAGE", $lang['value']);
}elseif(isset($_SESSION['lang'])){
	define("SYSTEM_LANGUAGE", $_SESSION['lang']);
}else{
	define("SYSTEM_LANGUAGE", '');
}

if(!class_exists('uCMS')){
	require '../ucms.php';
	$ucms = new uCMS();
	set_error_handler('uCMS::error_handler');
}

$ucms->set_language();
?>
<title><?php $ucms->cout("updates.title"); ?></title>
</head>
<body>
<div id="page-wrap">
<div id="content">
<?php
require 'stages.php';
if(!empty($modules) and $modules['value'] == '1'){
	fine();
	echo '</div>
		</div>
		</body>
		</html>';
	exit;
}

if(!defined("UC_PREFIX")){
	define("UC_PREFIX", "uc_");
}

if(!defined("UCMS_DEBUG")){
	define("UCMS_DEBUG", false);
}

if(SYSTEM_LANGUAGE == ''){
	select_language();
	echo '</div>
		</div>
		</body>
		</html>';
	exit;
}

if(!isset($_GET['action']) and !isset($_POST['action'])){
	if(!file_exists($file) and !file_exists("../".$file)){
		welcome();
	}else{
		header("Location: index.php?action=check");
		exit;
	}
}
	
if(isset($_GET['action'])) {
	switch ($_GET['action']) {
		case 'make-config':
			if($exists){
				header("Location: index.php?action=check");
				exit;
			}
			make_config();
		break;

		case 'done-config':
			if(isset($con) and $con){
				header("Location: index.php?action=check");
				exit;
			}
			$connected = isset($_GET['connected']) ? (bool) $_GET['connected'] : false;
			if(!$connected){
				if(file_exists($file))
					unlink($file);
				no_connect();
			}else{
				header("Location: index.php?action=check");
				exit;
			}
		break;
			
		case 'check':
			if(!$exists){
				header("Location: index.php?action=make-config");
				exit;
			}
			$test   = $udb->query("SELECT `ip` FROM `".UC_PREFIX."attempts`   LIMIT 1", true);
			$test2  = $udb->query("SELECT `id` FROM `".UC_PREFIX."categories` LIMIT 1", true);
			$test3  = $udb->query("SELECT `id` FROM `".UC_PREFIX."comments`   LIMIT 1", true);
			$test4  = $udb->query("SELECT `id` FROM `".UC_PREFIX."groups`     LIMIT 1", true);
			$test5  = $udb->query("SELECT `id` FROM `".UC_PREFIX."messages`   LIMIT 1", true);
			$test6  = $udb->query("SELECT `id` FROM `".UC_PREFIX."pages`      LIMIT 1", true);
			$test7  = $udb->query("SELECT `id` FROM `".UC_PREFIX."posts`      LIMIT 1", true);
			$test8  = $udb->query("SELECT `id` FROM `".UC_PREFIX."users`      LIMIT 1", true);
			$test9  = $udb->query("SELECT `id` FROM `".UC_PREFIX."settings`   LIMIT 1", true);
			$test10 = $udb->query("SELECT `id` FROM `".UC_PREFIX."usersinfo`  LIMIT 1", true);
			$test11 = $udb->query("SELECT `id` FROM `".UC_PREFIX."links`      LIMIT 1", true);
			if(!$test or !$test2 or !$test3 or !$test4 or !$test5 or !$test6 or !$test7 or !$test8 or !$test9 or !$test10 or !$test11){
				make_tables();
			}else{
				$settings = $udb->num_rows("SELECT `id` FROM `".UC_PREFIX."settings`", true);
				$users = $udb->num_rows("SELECT `id` FROM `".UC_PREFIX."users`", true);
				if($settings == 0){
					header("Location: index.php?action=fill-settings");
					exit;
				}elseif($users == 0){
					header("Location: index.php?action=fill-users");
					exit;
				}else{
					$status = 1;
					if($settings < count($default_settings_array)){
						$status = update_tables();
						if($settings < 30){ // before uCMS 1.2
							update_users();
						}else{
							switch ($settings) {
								case 30:
									$maintenance = $udb->query("UPDATE `".UC_PREFIX."settings` SET `value` = '1' WHERE `name` = 'ucms_maintenance'", true);
								break;
							}
						}
						update_usersinfo();
						update_settings();
						update_groups();
					}
				
					$categories = $udb->num_rows("SELECT * FROM `".UC_PREFIX."categories`");
					$groups = $udb->get_rows("SELECT * FROM `".UC_PREFIX."groups`");
					if($categories <= 0 or count($groups) <= 0 or $groups[0]['permissions'] == ""){
						fill_groups();
						fill_categories();
						if($status < 2)
							$status = 0;
					}

					$hotfix = $udb->query("UPDATE `".UC_PREFIX."settings` SET `name` = 'cron_schedule' WHERE `name` = 'cron_shedule'", true);

					if(preg_match("/\/admin\/update.php/", $_SERVER["HTTP_REFERER"]))
						$status = 2;
					switch ($status) {
						case 0:
							add_data();
							success();
						break;

						case 1:
							fine();
						break;
						
						case 2:
							$log = fopen("install.log", "a");
							fprintf($log, "[%s] SUCCESS: Update done\n", $ucms->date_format(time()));
							fclose($log);
							delete_tables();
							updated();
							$maintenance = $udb->query("UPDATE `".UC_PREFIX."settings` SET `value` = '0' WHERE `name` = 'ucms_maintenance'", true);
							if(isset($_SESSION['modules_enabled'])){
								$udb->query("UPDATE `".UC_PREFIX."settings` SET `value` = '1' WHERE `name` = 'modules_enabled'", true);
								unset($_SESSION['modules_enabled']);
							}
						break;
						
						default:
							fine();
						break;
					}
					unset($_SESSION['lang']);
				}
			}
		break;

		case 'fill-settings':
			$settings = $udb->num_rows("SELECT `id` FROM `".UC_PREFIX."settings`", true);
			if($settings > 0){
				header("Location: index.php");
				exit;
			}
			if(isset($_GET['error'])){
				echo "<div class=\"error\">".$ucms->cout("updates.form.error.empty_fields", true)."</div><br>";
			}
			fill_settings_form();
		break;

		case 'fill-users':
			$users = $udb->num_rows("SELECT `id` FROM `".UC_PREFIX."users`", true);
			if($users > 0){
				header("Location: index.php");
				exit;
			}
			if(isset($_GET['error'])){
				switch ($_GET['error']) {
					case 1:
						echo '<div class="error">'.$ucms->cout("updates.form.error.login", true).'</div><br>';
					break;

					case 2:
						echo '<div class="error">'.$ucms->cout("updates.form.error.password", true).'</div><br>';
					break;

					case 3:
						echo '<div class="error">'.$ucms->cout("updates.form.error.email", true).'</div><br>';
					break;

					case 4:
						echo "<div class=\"error\">".$ucms->cout("updates.form.error.empty_fields", true)."</div><br>";
					break;
				}
			}
			fill_users_form();
		break;
	}
}

if(isset($_POST['action'])){
	switch ($_POST['action']) {
		case 'make-config':
			config_file();
		break;

		case 'add-tables':
			add_tables();
		break;
		
		case 'fill-settings':
			$result = fill_settings();
			if(!$result){
				header("Location: index.php?action=fill-settings&error=1");
				exit;
			}
			header("Location: index.php?action=check");
		break;

		case 'fill-users':
			$result = fill_users();
			if($result !== true){
				header("Location: index.php?action=fill-users&error=$result");
				exit;
			}
			header("Location: index.php?action=check");
		break;
	}
}
?>
</div>
</div>
</body>
</html>