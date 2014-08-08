<?php
function update_config(){
	global $udb;
	$uc_prefix = 'uc_';
	$server = $udb->server;
	$login = $udb->login;
	$password = $udb->password;
	$db = $udb->db;
	$filetext = '<?php			
/*Добро пожаловать в исходный код uCMS! Этот файл нужно беречь!*/

define(UC_PREFIX, "'.$uc_prefix.'"); //префикс для таблиц, если вы хотите установить несколько копий uCMS в одну базу данных, то поставьте свой

define(DB_SERVER, "'.$server.'"); //Сервер базы данных

define(DB_USER, "'.$login.'"); //Логин

define(DB_PASSWORD, "'.$password.'"); //Пароль

define(DB_NAME, "'.$db.'"); //Имя базы данных

if(!defined(ABSPATH))
	define(ABSPATH, dirname(__FILE__)."/"); //абсолютный путь к файлам CMS относительно config.php

define(UCMS_DEBUG, false); //Включить или выключить режим отладки uCMS. Рекомендуется для разработчиков

/*Загрузка необходимого*/
require_once ABSPATH."sys/load.php";
?>';
	$file = fopen("../../config.php", "w+");
	fprintf($file, '%s', $filetext);
	fclose($file);
	$con = @mysqli_connect($server, $login, $password);	
	if (!$con and mysqli_connect_errno() == 1045) {
		return false;
	} else {
		return true;
	}
	
}

function update_tables(){
	global $udb;

	$tables = array(
		UC_PREFIX."attempts",
		UC_PREFIX."categories", 
		UC_PREFIX."comments", 
		UC_PREFIX."groups", 
		UC_PREFIX."messages", 
		UC_PREFIX."pages", 
		UC_PREFIX."posts", 
		UC_PREFIX."themes", 
		UC_PREFIX."users", 
		UC_PREFIX."settings", 
		UC_PREFIX."stats", 
		UC_PREFIX."usersinfo",
		UC_PREFIX."links",
		UC_PREFIX."widgets");

	$exfields = array();
	$attempts[0] = array(
		"ip", 
		"date", 
		"times");
	$attempts[1] = array(
		"varchar(15) NOT NULL", 
		"datetime NOT NULL DEFAULT '2012-01-01 00:00:00'", 
		"int(1) NOT NULL DEFAULT '1'");

	$exfields[0] = $attempts;

	$categories[0] = array(
		"id", 
		"name", 
		"alias", 
		"posts"); 
	$categories[1] = array(
		"int(11) NOT NULL AUTO_INCREMENT", 
		"varchar(75) NOT NULL", 
		"varchar(75) NOT NULL", 
		"int(11) NOT NULL"); 

	$exfields[1] = $categories;

	$comments[0] = array(
		"id", 
		"post", 
		"comment", 
		"author", 
		"approved", 
		"date");
	$comments[1] = array(
		"int(11) NOT NULL AUTO_INCREMENT", 
		"int(11) NOT NULL", 
		"longtext NOT NULL", 
		"varchar(75) NOT NULL", 
		"int(11) NOT NULL", 
		"datetime NOT NULL DEFAULT '2012-01-01 00:00:00'");
	
	$exfields[2] = $comments;
	
	$groups[0] = array(
		"id", 
		"name", 
		"alias", 
		"permissions");
	$groups[1] = array(
		"int(11) NOT NULL AUTO_INCREMENT", 
		"varchar(75) NOT NULL", 
		"varchar(75) NOT NULL", 
		"varchar(75) NOT NULL");
	
	$exfields[3] = $groups;
	
	$messages[0] = array(
		"id", 
		"author", 
		"receiver", 
		"date", 
		"text", 
		"readed");
	$messages[1] = array(
		"int(11) NOT NULL AUTO_INCREMENT", 
		"varchar(75) NOT NULL", 
		"varchar(75) NOT NULL", 
		"datetime NOT NULL DEFAULT '2012-01-01 00:00:00'", 
		"text NOT NULL", 
		"int(11) NOT NULL DEFAULT '0'");
	
	$exfields[4] = $messages;
	
	$pages[0] = array(
		"id", 
		"title", 
		"alias", 
		"author", 
		"body", 
		"publish", 
		"date");
	$pages[1] = array(
		"int(11) NOT NULL AUTO_INCREMENT", 
		"varchar(75) NOT NULL", 
		"varchar(75) NOT NULL", 
		"varchar(75) NOT NULL", 
		"longtext NOT NULL", 
		"int(11) NOT NULL", 
		"datetime NOT NULL DEFAULT '2012-01-01 00:00:00'");
	
	$exfields[5] = $pages;
	
	$posts[0] = array(
		"id", 
		"title", 
		"body", 
		"keywords", 
		"publish", 
		"alias", 
		"author", 
		"category", 
		"comments", 
		"date");
	$posts[1] = array(
		"int(11) NOT NULL AUTO_INCREMENT", 
		"varchar(75) NOT NULL", 
		"longtext NOT NULL", 
		"text NOT NULL", 
		"int(11) NOT NULL DEFAULT '0'", 
		"varchar(75) NOT NULL", 
		"varchar(75) NOT NULL", 
		"int(11) NOT NULL", 
		"int(11) NOT NULL", 
		"datetime NOT NULL DEFAULT '2012-01-01 00:00:00'");
	
	$exfields[6] = $posts;
	
	$themes[0] = array(
		"id", 
		"name", 
		"version", 
		"author", 
		"site", 
		"description",
		"dir",
		"activated");
	$themes[1] = array(
		"int(11) NOT NULL AUTO_INCREMENT", 
		"varchar(75) NOT NULL", 
		"varchar(75) NOT NULL", 
		"varchar(75) NOT NULL", 
		"varchar(75) NOT NULL", 
		"varchar(75) NOT NULL", 
		"varchar(75) NOT NULL",
		"int(11) NOT NULL DEFAULT '0'");
	
	$exfields[7] = $themes;
	
	$users[0] = array(
		"id", 
		"login", 
		"password", 
		"group", 
		"avatar", 
		"email", 
		"activation", 
		"date", 
		"session_hash", 
		"regip", 
		"logip", 
		"online", 
		"lastlogin");
	$users[1] = array(
		"int(255) NOT NULL AUTO_INCREMENT", 
		"varchar(255) NOT NULL", 
		"varchar(75) NOT NULL", 
		"int(11) NOT NULL DEFAULT '4'", 
		"varchar(255) NOT NULL", 
		"varchar(255) NOT NULL", 
		"int(1) NOT NULL", 
		"datetime NOT NULL DEFAULT '2012-01-01 00:00:00'", 
		"varchar(40) NOT NULL", 
		"varchar(15) NOT NULL", 
		"varchar(15) NOT NULL", 
		"int(11) NOT NULL", 
		"datetime NOT NULL DEFAULT '0000-00-00 00:00:00'");
	
	$exfields[8] = $users;
	
	$settings[0] = array(
		"id", 
		"name", 
		"value");
	$settings[1] = array(
		"int(11) NOT NULL AUTO_INCREMENT", 
		"varchar(75) NOT NULL", 
		"varchar(75) NOT NULL DEFAULT '0'");
	
	$exfields[9] = $settings;
	
	$stats[0] = array(
		"id", 
		"name", 
		"value", 
		"update");
	$stats[1] = array(
		"int(11) NOT NULL AUTO_INCREMENT", 
		"varchar(75) NOT NULL", 
		"varchar(75) NOT NULL DEFAULT '0'", 
		"datetime NOT NULL DEFAULT '2012-01-01 00:00:00'");
	
	$exfields[10] = $stats;
	
	$usersinfo[0] = array(
		"id", 
		"userid", 
		"firstname", 
		"surname", 
		"icq", 
		"skype", 
		"addinfo", 
		"birthdate", 
		"pm-alert");
	$usersinfo[1] = array(
		"int(11) NOT NULL AUTO_INCREMENT", 
		"int(11) NOT NULL", 
		"varchar(75) NOT NULL", 
		"varchar(75) NOT NULL", 
		"varchar(75) NOT NULL", 
		"varchar(75) NOT NULL", 
		"text NOT NULL", 
		"date NOT NULL DEFAULT '2012-01-01'", 
		"int(11) NOT NULL");
	
	$exfields[11] = $usersinfo;

	$links[0] = array(
		"id",
		"name",
		"publish",
		"url",
		"description",
		"author",
		"target",
		"date");

	$links[1] = array(
		"int(11) NOT NULL AUTO_INCREMENT",
		"varchar(75) NOT NULL",
		"varchar(75) NOT NULL",
		"varchar(75) NOT NULL",
		"varchar(75) NOT NULL",
		"varchar(75) NOT NULL",
		"varchar(75) NOT NULL",
		"datetime NOT NULL DEFAULT '2012-01-01 00:00:00'");

	$exfields[12] = $links;

	$widgets[0] = array(
		"id", 
		"name", 
		"version", 
		"author", 
		"site", 
		"description",
		"dir",
		"activated");
	$widgets[1] = array(
		"int(11) NOT NULL AUTO_INCREMENT", 
		"varchar(75) NOT NULL", 
		"varchar(75) NOT NULL", 
		"varchar(75) NOT NULL", 
		"varchar(75) NOT NULL", 
		"varchar(75) NOT NULL", 
		"varchar(75) NOT NULL",
		"int(11) NOT NULL DEFAULT '0'");
	
	$exfields[13] = $widgets;

	$changed = false;
	$j = 0;
	foreach ($tables as $table) {
		$fields = $udb->get_rows("SHOW COLUMNS FROM `$table`");
		for($i = 0; $i < count($exfields[$j][0]); $i++){
			$field_type = $fields[$i]['Type'].($fields[$i]['Null'] == "NO" ? " NOT NULL" : " NULL").($fields[$i]['Default'] != NULL ? " DEFAULT '".$fields[$i]['Default']."'" : "").($fields[$i]['Extra'] == "auto_increment" ? " AUTO_INCREMENT" : "");
	 		if($fields[$i]['Field'] != $exfields[$j][0][$i]){
	 			if(isset($fields[$i]['Field']) and $fields[$i]['Field'] != ''){
	 				if($exfields[$j][0][$i] != $fields[$i-1]['Field']){
		 				$udb->query("ALTER IGNORE TABLE `$table` ADD `".$exfields[$j][0][$i]."` ".$exfields[$j][1][$i]." AFTER `".$fields[$i-1]['Field']."`", true);	
		 				//echo "<br>ALTER IGNORE TABLE `$table` ADD `".$exfields[$j][0][$i]."` ".$exfields[$j][1][$i]." AFTER `".$fields[$i-1]['Field']."`";
		 			}
		 		}else{
		 			$udb->query("ALTER IGNORE TABLE `$table` ADD `".$exfields[$j][0][$i]."` ".$exfields[$j][1][$i], true);
		 			//echo "<br>ALTER IGNORE TABLE `$table` ADD `".$exfields[$j][0][$i]."` ".$exfields[$j][1][$i];
		 		}
		 		$changed = true;
	 		}else if($field_type != $exfields[$j][1][$i]){
	 			//echo "<br>".$fields[$i]['Type'].($fields[$i]['Null'] == "NO" ? " NOT NULL" : " NULL").($fields[$i]['Default'] != NULL ? " DEFAULT '".$fields[$i]['Default']."'" : "").($fields[$i]['Extra'] == "auto_increment" ? " AUTO_INCREMENT" : "");
	 			$udb->query("ALTER TABLE `$table` CHANGE `".$exfields[$j][0][$i]."` `".$exfields[$j][0][$i]."` ".$exfields[$j][1][$i]);
	 			//echo "<br>ALTER TABLE `$table` CHANGE `".$exfields[$j][0][$i]."` `".$exfields[$j][0][$i]."` ".$exfields[$j][1][$i];
	 		}
			//echo "<br>$table $i: ".$fields[$i]['Field']." - ".$exfields[$j][0][$i]."<br>";
		}
		$j++;
	}
	//echo "<br><br>exit";
	//exit;
	if($changed) return 2;
	else return 1;
}

function update_settings(){
	global $udb;
	$old = $udb->get_rows("SELECT `value` FROM `uc_settings`");
	if($old and count($old) <= 16){
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
		$uc_settings = "INSERT IGNORE INTO `".UC_PREFIX."settings` (`id`, `name`, `value`) VALUES
			(1, 'site_name', '$site_name'),
			(2, 'site_description', '$site_description'),
			(3, 'site_title', '$site_title'),
			(4, 'nice_links', '$nice_links'),
			(5, 'user_avatars', '$user_avatars'),
			(6, 'user_messages', '$user_messages'),
			(7, 'domain', '$domain'),
			(8, 'theme_dir', '$theme_dir'),
			(9, 'theme_name', '$theme_name'),
			(10, 'num_tries', '$num_tries'),
			(11, 'ucms_dir', '$dir'),
			(12, 'ucms_maintenance', '0'),
			(13, 'phpmyadmin', '$phpmyadmin'),
			(14, 'unique_emails', '$unique_emails'),
			(15, 'modules', '1,$post_comments,1,1,1,1'),
			(16, 'posts_on_page', '10'),
			(17, 'default_group', '4'),
			(18, 'allow_registration', '1'),
			(19, 'admin_email', '$author[email]'),
			(20, 'comments_email', '$author[email]'),
			(21, 'new_user_email', '$author[email]'),
			(22, 'timezone', 'UTC'),
			(23, 'post_sef_link', '@alias@'),
			(24, 'page_sef_link', 'pages/@alias@'),
			(25, 'category_sef_prefix', 'category'),
			(26, 'tag_sef_prefix', 'tag'),
			(27, 'use_captcha', '1'),
			(28, 'site_author', '$author[login]'),
			(29, 'avatar_width', '150'),
			(30, 'avatar_height', '150');";
		$t = $udb->query($uc_settings);
		if($t) return true;
		return false;
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

function update_themes(){
	global $udb;
	$ucms = $udb->query("UPDATE `".UC_PREFIX."themes` SET `version` = '1.2' WHERE `id` = '1'");
	return true;
}
?>