<?php
function update_config($file){
	global $udb, $ucms;
	if(!defined("UC_PREFIX")){
		$uc_prefix = 'uc_';
		define("UC_PREFIX", $uc_prefix);
	}
	else{
		$uc_prefix = UC_PREFIX;
	}
	$server = $udb->server;
	$login = $udb->login;
	$password = $udb->password;
	$db = $udb->db;
	$fields = array("uc_", "localhost", "login", "password", "ucms_database");
	$values = array("$uc_prefix", "$server", "$login", "$password", "$db");
	$config = file_get_contents("../../config-manual.php");
	$config = str_replace($fields, $values, $config);
	$file = fopen($file, "w+");
	fprintf($file, '%s', $config);
	fclose($file);
	$con = @mysqli_connect($server, $login, $password);
	$log = fopen("install.log", "a");
	if(!$con and mysqli_connect_errno() == 1045){
		fprintf($log, "[%s] ERROR: Updating config file failed, MySQL error: %s\n", $ucms->date_format(time()), mysqli_connect_error());
	}else{
		fprintf($log, "[%s] SUCCESS: Updating config file done\n", $ucms->date_format(time()));
	}
	fclose($log);
	if (!$con and mysqli_connect_errno() == 1045) {
		return false;
	} else {
		return true;
	}
	
}

function update_tables(){
	global $udb, $ucms, $con;

	include 'tables.php';

	$changed = false;
	$j = 0;
	$log = fopen("install.log", "a");

	foreach ($tables as $table) {
		$fields = $udb->get_rows("SHOW COLUMNS FROM `$table`");
		$sql = "ALTER TABLE `$table` ENGINE = InnoDB";
		$query = $udb->query($sql);
		if(!$query){
			fprintf($log, "[%s] ERROR: Query '%s' failed, MySQL error: %s\n", $ucms->date_format(time()), $sql, mysqli_error($con));
		}else{
			fprintf($log, "[%s] SUCCESS: Query '%s' done\n", $ucms->date_format(time()), $sql);
		}
		for($i = 0; $i < count($exfields[$j][0]); $i++){
			if(isset($fields[$i])){
				$field_type = $fields[$i]['Type'].($fields[$i]['Null'] == "NO" ? " NOT NULL" : " NULL").($fields[$i]['Default'] != NULL ? " DEFAULT '".$fields[$i]['Default']."'" : "")
				.($fields[$i]['Extra'] == "auto_increment" ? " AUTO_INCREMENT" : "");
				if($field_type != $exfields[$j][1][$i]){
					$sql = "ALTER TABLE `$table` CHANGE `".$exfields[$j][0][$i]."` `".$exfields[$j][0][$i]."` ".$exfields[$j][1][$i];
					$query = $udb->query($sql);
					if(!$query){
						fprintf($log, "[%s] ERROR: Query '%s' failed, MySQL error: %s\n", $ucms->date_format(time()), $sql, mysqli_error($con));
					}else{
						fprintf($log, "[%s] SUCCESS: Query '%s' done\n", $ucms->date_format(time()), $sql);
					}
					$changed = true;
				}
			}
			else{
				$sql = "ALTER IGNORE TABLE `$table` ADD `".$exfields[$j][0][$i]."` ".$exfields[$j][1][$i];
				$query = $udb->query($sql, true);
				if(!$query){
					fprintf($log, "[%s] ERROR: Query '%s' failed, MySQL error: %s\n", $ucms->date_format(time()), $sql, mysqli_error($con));
				}else{
					fprintf($log, "[%s] SUCCESS: Query '%s' done\n", $ucms->date_format(time()), $sql);
				}
				$changed = true;
			}
		}
		$j++;
	}
	fclose($log);
	if($changed) return 2;
	else return 1;
}

function update_settings(){
	global $udb, $default_settings_array;

	$old = $udb->get_rows("SELECT `value` FROM `".UC_PREFIX."settings`");
	$lang = isset($_SESSION['lang']) ? $udb->parse_value($_SESSION['lang']) : "en_US";
	$start_num = 0;
	if($old){
		if(count($old) <= 16){ // до uCMS 1.2
			$site_name = $old[0]['value'];
 	 		$site_description = $old[1]['value'];
 	 		$site_title = $old[2]['value'];
 	 		$nice_links = $old[3]['value'];
 	 		$user_avatars = $old[4]['value'];
 	 		$user_messages = $old[5]['value'];
 	 		$post_comments = $old[6]['value'];
 	 		$domain = $old[7]['value'];
 	 		$theme_dir = str_replace("sys/themes", "", $old[8]['value']);
 	 		$theme_dir = preg_replace("#(/)#", "", $theme_dir);
 	 		$theme_name = $old[9]['value'];
 	 		$num_tries = $old[11]['value'];
 	 		$dir = $old[12]['value'];
 	 		$phpmyadmin = $old[14]['value'];
 	 		$unique_emails = $old[15]['value'];
 	 		$udb->query("TRUNCATE TABLE `".UC_PREFIX."settings`");
 	 		$author = $udb->get_row("SELECT `login`, `email` FROM `".UC_PREFIX."users` WHERE `id` = 1");

 	 		$default_settings_array[0]['value'] = $site_name;
			$default_settings_array[1]['value'] = $site_description;
			$default_settings_array[2]['value'] = $site_title;
			$default_settings_array[3]['value'] = $nice_links;
			$default_settings_array[4]['value'] = $user_avatars;
			$default_settings_array[5]['value'] = $user_messages;
			$default_settings_array[6]['value'] = $domain;
			$default_settings_array[7]['value'] = $theme_dir;
			$default_settings_array[8]['value'] = $theme_name;
			$default_settings_array[9]['value'] = $num_tries;
			$default_settings_array[10]['value'] = $dir;
			$default_settings_array[12]['value'] = $phpmyadmin;
			$default_settings_array[13]['value'] = $unique_emails;
			$default_settings_array[18]['value'] = $author["email"];
			$default_settings_array[19]['value'] = $author["email"];
			$default_settings_array[20]['value'] = $author["email"];
			$default_settings_array[27]['value'] = $author["login"];
			$default_settings_array[33]['value'] = $lang;
		}else{
			switch (count($old)) {
				case 30: // uCMS 1.2
					$udb->query("UPDATE `".UC_PREFIX."settings` SET `name` = 'site_domain' WHERE `name` = 'domain'");
					$udb->query("UPDATE `".UC_PREFIX."settings` SET `name` = 'themedir' WHERE `name` = 'theme_dir'");
					$udb->query("UPDATE `".UC_PREFIX."settings` SET `name` = 'themename' WHERE `name` = 'theme_name'");
					$udb->query("UPDATE `".UC_PREFIX."settings` SET `name` = 'login_attempts_num' WHERE `name` = 'num_tries'");
					$udb->query("UPDATE `".UC_PREFIX."settings` SET `name` = 'phpmyadmin_link' WHERE `name` = 'phpmyadmin'");
					$udb->query("UPDATE `".UC_PREFIX."settings` SET `name` = 'modules_enabled', `value` = '0' WHERE `name` = 'modules'");
					$udb->query("UPDATE `".UC_PREFIX."settings` SET `name` = 'ucms_timezone' WHERE `name` = 'timezone'");
					$default_settings_array[33]['value'] = $lang;
					$start_num = 30;
				break;
				default:
					if(count($old) < SETTINGS_NUM){
						$default_settings_array[33]['value'] = $lang;
						$start_num = 30;
					}
				break;
			}
		}

		$uc_settings = "INSERT IGNORE INTO `".UC_PREFIX."settings` (`id`, `name`, `value`, `update`, `owner`) VALUES ";
		for($i = $start_num; $i < count($default_settings_array); $i++){
			$uc_settings .= "('".($i+1)."', '".$default_settings_array[$i]["name"]."', '".$default_settings_array[$i]["value"]."', NOW(), '".$default_settings_array[$i]["owner"]."')";
			if($i+1 == count($default_settings_array)) {
				$uc_settings .= ";";
			}else{
				$uc_settings .= ",";
			}
		}

		$owner_update = "UPDATE `".UC_PREFIX."settings` SET `owner` = CASE";
		$names = '';
		for($i = 0; $i < count($default_settings_array); $i++){
			$owner_update .= " WHEN `name` = '".$default_settings_array[$i]["name"]."' THEN '".$default_settings_array[$i]["owner"]."'";
			$names .= "'".$default_settings_array[$i]["name"]."'";
			if($i+1 != count($default_settings_array)) {
				$names .= ",";
			}
		}
		$owner_update .= " END WHERE `name` IN ($names)";
		$t = $udb->query($uc_settings);
		$o = $udb->query($owner_update);
		if($t and $o) return true;
		return false;
	}
}

function update_usersinfo(){
	global $udb;
	$data = $udb->get_rows("SELECT * FROM `".UC_PREFIX."usersinfo`");
	if(isset($data[0]['firstname'])){
		$del = $udb->query("DROP TABLE `".UC_PREFIX."usersinfo`");
		$create = $udb->query("CREATE TABLE IF NOT EXISTS `".UC_PREFIX."usersinfo` (
			`id` int(11) NOT NULL AUTO_INCREMENT,
			`user_id` int(11) NOT NULL,
			`name` varchar(255) NOT NULL,
			`value` longtext NOT NULL,
			`required` int(11) NOT NULL DEFAULT '0',
			`update` datetime NOT NULL DEFAULT '2012-01-01 00:00:00',
			PRIMARY KEY (`id`)
		  	) ENGINE=MyISAM DEFAULT CHARSET=utf8 ;");
	
		if($data and count($data) > 0){
			for ($i = 0; $i < count($data); $i++) {
				$user_id = $data[$i]['userid'];
				$firstname = $data[$i]['firstname'];
				$surname = $data[$i]['surname'];
				$icq = $data[$i]['icq'];
				$skype = $data[$i]['skype'];
				$addinfo = $data[$i]['addinfo'];
				$birthdate = $data[$i]['birthdate'];
				$pm_alert = $data[$i]['pm-alert'];
				$udb->query("INSERT INTO `".UC_PREFIX."usersinfo` VALUES (NULL, '$user_id', 'firstname', '$firstname', '0', NOW())");
				$udb->query("INSERT INTO `".UC_PREFIX."usersinfo` VALUES (NULL, '$user_id', 'surname', '$surname', '0', NOW())");
				$udb->query("INSERT INTO `".UC_PREFIX."usersinfo` VALUES (NULL, '$user_id', 'icq', '$icq', '0', NOW())");
				$udb->query("INSERT INTO `".UC_PREFIX."usersinfo` VALUES (NULL, '$user_id', 'skype', '$skype', '0', NOW())");
				$udb->query("INSERT INTO `".UC_PREFIX."usersinfo` VALUES (NULL, '$user_id', 'addinfo', '$addinfo', '0', NOW())");
				$udb->query("INSERT INTO `".UC_PREFIX."usersinfo` VALUES (NULL, '$user_id', 'birthdate', '$birthdate', '0', NOW())");
				$udb->query("INSERT INTO `".UC_PREFIX."usersinfo` VALUES (NULL, '$user_id', 'pm_alert', '$pm_alert', '0', NOW())");
			}
		}
	}
}


function update_users(){
	global $udb;
	$users = $udb->get_rows("SELECT `avatar` FROM `".UC_PREFIX."users`");
	if($users){
		for ($i = 0; $i < count($users); $i++) { 
			$avatar = str_replace("sys/users/avatars", "", $users[$i]['avatar']);
 	 		$avatar = preg_replace("#(/)#", "", $avatar);
 	 		$id = $i+1;
 	 		$udb->query("UPDATE `uc_users` SET `avatar` = '$avatar' WHERE `id` = '$id'");
		}
	}
}

function delete_tables(){
	global $udb;
	$udb->query("DROP TABLE `".UC_PREFIX."themes`", true);
	$udb->query("DROP TABLE `".UC_PREFIX."widgets`", true);
	$udb->query("DROP TABLE `".UC_PREFIX."stats`", true);
	return true;
}

function update_groups(){
	global $udb;
	$test = $udb->get_row("SELECT `permissions` FROM `".UC_PREFIX."groups` WHERE `id` = '1'");
	if(!$test or empty($test['permissions']) or $test['permissions'] == '7777'){
		$uc_groups = "INSERT IGNORE INTO `".UC_PREFIX."groups` (`id`, `name`, `alias`, `permissions`) VALUES
		(1, 'Администратор', 'admin', 'posts:7,comments:7,pages:7,users:7,themes:4,widgets:4,plugins:4,links:7,fileman:4'),
		(2, 'Модератор', 'moderator', 'posts:5,comments:5,pages:5,users:5,themes:1,widgets:1,plugins:1,links:5,fileman:1'),
		(3, 'Проверенный', 'trusted', 'posts:1,comments:3,pages:1,users:3,themes:0,widgets:0,plugins:0,links:3,fileman:1'),
		(4, 'Пользователь', 'user', 'posts:1,comments:2,pages:1,users:2,themes:0,widgets:0,plugins:0,links:2,fileman:0'),
		(5, 'Забаненный', 'banned', 'posts:0,comments:0,pages:0,users:0,themes:0,widgets:0,plugins:0,links:0,fileman:0'),
		(6, 'Гость', 'guest', 'posts:1,comments:2,pages:1,users:0,themes:0,widgets:0,plugins:0,links:1,fileman:0');";
		$udb->query("TRUNCATE TABLE `".UC_PREFIX."groups`");
		$udb->query($uc_groups);
	}
}


?>