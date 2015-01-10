<?php
	function config_file(){
		if(isset($_POST['dbserver']) and isset($_POST['dbuser']) and isset($_POST['dbname']) and !empty($_POST['dbserver']) and !empty($_POST['dbuser']) and !empty($_POST['dbname'])){
			$server = addslashes($_POST['dbserver']);
			$login = addslashes($_POST['dbuser']);
			$password = (isset($_POST['dbpass']) or $_POST['dbpass'] != '') ? addslashes($_POST['dbpass']) : '';
			$database = addslashes($_POST['dbname']);
			$uc_prefix = addslashes($_POST['uc_prefix']);
			$fields = array("uc_", "localhost", "login", "password", "ucms_database");
			$values = array("$uc_prefix", "$server", "$login", "$password", "$database");
			$config = file_get_contents("../../config-manual.php");
			$config = str_replace($fields, $values, $config);
			$file = fopen("../../config.php", "w+");
			fprintf($file, '%s', $config);
			fclose($file);
			$con = @mysqli_connect($server, $login, $password);	
			if (!$con and mysqli_connect_errno() == 1045) {
				$connected = false;
			} else {
				$connected = true;
			}
			global $ucms;
			$log = fopen("install.log", "a");
			if(!$connected){
				fprintf($log, "[%s] ERROR: Creating config file failed, MySQL error: %s\n", $ucms->date_format(time()), mysqli_connect_error());
			}else{
				fprintf($log, "[%s] SUCCESS: Creating config file done\n", $ucms->date_format(time()));
			}
			fclose($log);
			header("Location: index.php?action=done-config&connected=$connected");
			return true;
		}
	}

	function add_tables(){
		global $udb, $ucms, $con;
		
		include 'tables.php';
		$log = fopen("install.log", "a");
		foreach ($add_tables as $add_table) {
			$query = $udb->query($add_table);
			if(!$query){
				fprintf($log, "[%s] ERROR: Query '%s' failed, MySQL error: %s\n", $ucms->date_format(time()), $add_table, mysqli_error($con));
			}else{
				fprintf($log, "[%s] SUCCESS: Query '%s' done\n", $ucms->date_format(time()), $add_table);
			}
		}
		fclose($log);

		header("Location: index.php?action=check");
		return true;
	}

	function fill_settings(){
		global $udb, $con, $default_settings_array, $ucms;
		$settings = $udb->num_rows("SELECT * FROM `".UC_PREFIX."settings`");
		if($settings == 0){
			if(isset($_POST['domain']) and $_POST['domain'] != '' and isset($_POST['site_name']) and $_POST['site_name'] != '' and isset($_POST['site_description']) and $_POST['site_description'] != '' and isset($_POST['site_title']) and $_POST['site_title'] != ''){
				$lang = isset($_SESSION['lang']) ? $udb->parse_value($_SESSION['lang']) : "en_US";
				$domain = $udb->parse_value($_POST['domain']);
				if(!preg_match("#(http://)#", $domain)){
					$domain = "http://".$domain;
				}
				if(substr($domain, -1) == '/'){
					$domain = substr($domain, 0, strlen($domain)-1);
				}
				$site_name = $udb->parse_value($_POST['site_name']);
				$site_description = $udb->parse_value($_POST['site_description']);
				$site_title = $udb->parse_value($_POST['site_title']);
				$dir = ($_POST['dir'] != '' and $_POST['dir'] != '/') ? $udb->parse_value($_POST['dir']) : '';
				if(substr($dir, 0, 1) != "/"){
					$dir = "/".$dir;
				}
				if(substr($dir, -1) == '/'){
					$dir = substr($dir, 0, strlen($dir)-1);
				}
				$default_settings_array[0]['value'] = $site_name;
				$default_settings_array[1]['value'] = $site_description;
				$default_settings_array[2]['value'] = $site_title;
				$default_settings_array[6]['value'] = $domain;
				$default_settings_array[10]['value'] = $dir;
				$default_settings_array[33]['value'] = $lang;
				$uc_settings = "INSERT IGNORE INTO `".UC_PREFIX."settings` (`id`, `name`, `value`, `update`, `owner`) VALUES ";
				for($i = 0; $i < count($default_settings_array); $i++){
					$uc_settings .= "('".($i+1)."', '".$default_settings_array[$i]["name"]."', '".$default_settings_array[$i]["value"]."', NOW(), '".$default_settings_array[$i]["owner"]."')";
					if($i+1 == count($default_settings_array)) {
						$uc_settings .= ";";
					}else{
						$uc_settings .= ",";
					}
				}
				$udb->query($uc_settings);
				$log = fopen("install.log", "a");
				if(!$query){
					printf($log, "[%s] ERROR: Query '%s' failed, MySQL error: %s\n", $ucms->date_format(time()), $uc_settings, mysqli_error($con));
				}else{
					fprintf($log, "[%s] SUCCESS: Query '%s' done\n", $ucms->date_format(time()), $uc_settings);
				}
				fclose($log);
				return true;
			}
			return false;
		}
		return true;
	}

	function fill_users(){
		global $udb, $con;
		$users = $udb->num_rows("SELECT * FROM `".UC_PREFIX."users`");
		if($users == 0){
			if(isset($_POST['setup-login']) and $_POST['setup-login'] != '' and isset($_POST['setup-password']) and $_POST['setup-password'] != '' and isset($_POST['setup-email']) and $_POST['setup-email'] != ''){
				$login = $udb->parse_value($_POST['setup-login']);
				$login = htmlspecialchars($login);
				$login = trim($login);
				$reg = "/[^(\w)|(\x7F-\xFF)|(\s)]/";
				$login = preg_replace($reg,'',$login);
				if (mb_strlen($login, "UTF-8") < 4 or mb_strlen($login, "UTF-8") > 16){
					return 1;
				}
				$password = $udb->parse_value($_POST['setup-password']);
				$password = htmlspecialchars(trim($password));
				if (strlen($password) < 6 or strlen($password) > 20){
					return 2;
				}
				$salt = substr(sha1($password),0,22);
				$password = $udb->parse_value(stripslashes($password));
				$password = htmlspecialchars(trim($password));
				$password = crypt($password, '$2a$10$'.$salt);
				$email = $udb->parse_value($_POST['setup-email']);
				if (!preg_match("/@/i", $email)) {
					return 3;
				}
				$ip = getenv("HTTP_X_FORWARDED_FOR");
				if (empty($ip) || $ip == 'unknown'){
					$ip = getenv("REMOTE_ADDR"); 
				}

				$chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHI JKLMNOPRQSTUVWXYZ0123456789";
     			$hash = "";
     			$clen = strlen($chars) - 1;  
     			while (strlen($hash) < 10) {
        		    $hash .= $chars[mt_rand(0,$clen)];  
    			}
				$hash = md5($hash);
				
							 
				$_SESSION['id'] = 1;
				$_SESSION['hash'] = $hash;
				$uc_users = "INSERT IGNORE INTO `".UC_PREFIX."users` VALUES (1, '$login','$password', '1', 'no-avatar.jpg', '$email', '1', NOW(), '$hash', '$ip', '$ip', '1', NOW())";
				$udb->query($uc_users);
				$udb->query("UPDATE `".UC_PREFIX."settings` SET `value` = '$email' WHERE `id` = '19'");
				$udb->query("UPDATE `".UC_PREFIX."settings` SET `value` = '$email' WHERE `id` = '20'");
				$udb->query("UPDATE `".UC_PREFIX."settings` SET `value` = '$email' WHERE `id` = '21'");
				$udb->query("UPDATE `".UC_PREFIX."settings` SET `value` = '$login' WHERE `id` = '28'");
				return true;
			}else{
				return 4;
			}					
		}
		return true;
	}

	function fill_groups(){
		global $udb, $con, $ucms;
		include ABSPATH.UC_INCLUDES_PATH.'modules.php';
		$ucms->set_language(get_module('path', 'users').'languages/'.SYSTEM_LANGUAGE.'.lang');

		$groups = $udb->get_rows("SELECT * FROM `".UC_PREFIX."groups`");
		if(($groups and count($groups) < 7) or $groups[0]['permissions'] == ""){
			$uc_groups = "INSERT IGNORE INTO `".UC_PREFIX."groups` (`id`, `name`, `alias`, `permissions`) VALUES
	  		(1, '".$ucms->cout("module.users.group.admin.name", true)."',     'admin',     'posts:7,comments:7,pages:7,users:7,themes:4,widgets:4,plugins:4,links:7,fileman:4'),
	  		(2, '".$ucms->cout("module.users.group.moderator.name", true)."', 'moderator', 'posts:5,comments:5,pages:5,users:5,themes:1,widgets:1,plugins:1,links:5,fileman:1'),
	  		(3, '".$ucms->cout("module.users.group.trusted.name", true)."',   'trusted',   'posts:1,comments:3,pages:1,users:3,themes:0,widgets:0,plugins:0,links:3,fileman:1'),
	  		(4, '".$ucms->cout("module.users.group.user.name", true)."',      'user',      'posts:1,comments:2,pages:1,users:2,themes:0,widgets:0,plugins:0,links:2,fileman:0'),
	  		(5, '".$ucms->cout("module.users.group.banned.name", true)."',    'banned',    'posts:0,comments:0,pages:0,users:0,themes:0,widgets:0,plugins:0,links:0,fileman:0'),
	  		(6, '".$ucms->cout("module.users.group.guest.name", true)."',     'guest',     'posts:1,comments:2,pages:1,users:0,themes:0,widgets:0,plugins:0,links:1,fileman:0');";

	  		$udb->query("TRUNCATE TABLE `".UC_PREFIX."groups`");
			$udb->query($uc_groups);
		}
		return true;
	}

	function fill_categories(){
		global $udb, $con, $ucms;
		$categories = $udb->num_rows("SELECT * FROM `".UC_PREFIX."categories`");
		if($categories == 0){
			$uc_categories = "INSERT IGNORE INTO `".UC_PREFIX."categories` (`id`, `name`, `alias`, `posts`, `parent`, `sort`) VALUES (1, '".$ucms->cout("updates.add_data.default_category", true)."', 'uncategorized', '1', '0', '0');";
			$udb->query($uc_categories);
		}
		return true;
	}

	function add_data(){
		global $udb, $con, $ucms;
		$uc_posts = "INSERT IGNORE INTO `".UC_PREFIX."posts` VALUES (1, '".$ucms->cout("updates.add_data.default_post_title", true)."','".$ucms->cout("updates.add_data.default_post_body", true)."', '".$ucms->cout("updates.add_data.default_post_tags", true)."', '1', 'hello-world', '1', '1', '1', NOW())";
		$uc_comments = "INSERT IGNORE INTO `".UC_PREFIX."comments` VALUES (1, '1','".$ucms->cout("updates.add_data.default_comment", true)."', '1', '1', NOW(), '0', '127.0.0.1', 'email@ucms', '0')";
		$uc_pages = "INSERT IGNORE INTO `".UC_PREFIX."pages` VALUES (1, '".$ucms->cout("updates.add_data.default_page_title", true)."','first-page', '1', '".$ucms->cout("updates.add_data.default_page_body", true)."', '1', NOW(), '0', '0')";
		$udb->query($uc_posts);
		$udb->query($uc_comments);
		$udb->query($uc_pages);
		$log = fopen("install.log", "a");
		fprintf($log, "[%s] SUCCESS: Installation done\n", $ucms->date_format(time()));
		fclose($log);
		return true;
	}
 
	function check_tables(){
		global  $udb;
		include 'tables.php';
		$i = 0;
		foreach($tables as $table){
			$test = $udb->query("SELECT `".$exfields[$i][0][0]."` FROM `$table` LIMIT 1", true);
			if(!$test) return false;
			$i++;
		}
		return true;
	}

?>