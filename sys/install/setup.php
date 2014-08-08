<?php
session_start();
$install = true;
$file = "../../config.php";
if(file_exists($file))
	require '../../config.php';
else if(file_exists("../".$file))
	require '../../../config.php';
if(!defined("UC_PREFIX")){
	define(UC_PREFIX, "uc_");
}
	function config_file(){
		if(isset($_POST['dbserver']) and isset($_POST['dbuser']) and isset($_POST['dbname']) and !empty($_POST['dbserver']) and !empty($_POST['dbuser']) and !empty($_POST['dbname'])){
			$server = addslashes($_POST['dbserver']);
			$login = addslashes($_POST['dbuser']);
			$password = (isset($_POST['dbpass']) or $_POST['dbpass'] != '') ? addslashes($_POST['dbpass']) : '';
			$database = addslashes($_POST['dbname']);	
			$uc_prefix = addslashes($_POST['uc_prefix']);
			$filetext = '<?php			
/*Добро пожаловать в исходный код uCMS! Этот файл нужно беречь!*/

define(UC_PREFIX, "'.$uc_prefix.'"); //префикс для таблиц, если вы хотите установить несколько копий uCMS в одну базу данных, то поставьте свой

define(DB_SERVER, "'.$server.'"); //Сервер базы данных

define(DB_USER, "'.$login.'"); //Логин

define(DB_PASSWORD, "'.$password.'"); //Пароль

define(DB_NAME, "'.$database.'"); //Имя базы данных

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
				$_SESSION['connected'] = false;
			} else {
				$_SESSION['connected'] = true;
			}
			header("Location: index.php");
			return true;
		}
	}

	function add_tables(){
		global $udb;
		if(isset($_POST['add-tables'])){
	  		$uc_attempts = "CREATE TABLE IF NOT EXISTS `".UC_PREFIX."attempts` (
			`ip` varchar(15) NOT NULL,
			`date` datetime NOT NULL DEFAULT '2012-01-01 00:00:00',
			`times` int(1) NOT NULL DEFAULT '1',
			PRIMARY KEY (`ip`)
	  		) ENGINE=MyISAM DEFAULT CHARSET=utf8 ;";

	  		$uc_categories = "CREATE TABLE IF NOT EXISTS `".UC_PREFIX."categories` (
			`id` int(11) NOT NULL AUTO_INCREMENT,
			`name` varchar(75) NOT NULL,
			`alias` varchar(75) NOT NULL,
			`posts` int(11) NOT NULL,
			PRIMARY KEY (`id`)
	  		) ENGINE=MyISAM DEFAULT CHARSET=utf8 ;";

	  		$uc_comments = "CREATE TABLE IF NOT EXISTS `".UC_PREFIX."comments` (
			`id` int(11) NOT NULL AUTO_INCREMENT,
			`post` int(11) NOT NULL,
			`comment` longtext NOT NULL,
			`author` varchar(75) NOT NULL,
			`approved` int(11) NOT NULL,
			`date` datetime NOT NULL DEFAULT '2012-01-01 00:00:00',
			PRIMARY KEY (`id`)
	  		) ENGINE=MyISAM DEFAULT CHARSET=utf8 ;";

	  		$uc_groups = "CREATE TABLE IF NOT EXISTS `".UC_PREFIX."groups` (
			`id` int(11) NOT NULL AUTO_INCREMENT,
			`name` varchar(75) NOT NULL,
			`alias` varchar(75) NOT NULL,
			`permissions` varchar(75) NOT NULL,
			PRIMARY KEY (`id`)
		 	) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;";

	  		$uc_messages = "CREATE TABLE IF NOT EXISTS `".UC_PREFIX."messages` (
			`id` int(11) NOT NULL AUTO_INCREMENT,
			`author` varchar(75) NOT NULL,
			`receiver` varchar(75) NOT NULL,
			`date` datetime NOT NULL DEFAULT '2012-01-01 00:00:00',
			`text` text NOT NULL,
			`readed` int(11) NOT NULL,
			PRIMARY KEY (`id`)
	  		) ENGINE=MyISAM DEFAULT CHARSET=utf8 ;";
			
	 		$uc_pages = "CREATE TABLE IF NOT EXISTS `".UC_PREFIX."pages` (
			`id` int(11) NOT NULL AUTO_INCREMENT,
			`title` varchar(75) NOT NULL,
			`alias` varchar(75) NOT NULL,
			`author` varchar(75) NOT NULL,
			`body` longtext NOT NULL,
			`publish` int(11) NOT NULL,
			`date` datetime NOT NULL DEFAULT '2012-01-01 00:00:00',
			PRIMARY KEY (`id`)
	 		) ENGINE=MyISAM DEFAULT CHARSET=utf8 ;";

	 		$uc_posts = "CREATE TABLE IF NOT EXISTS `".UC_PREFIX."posts` (
			`id` int(11) NOT NULL AUTO_INCREMENT,
			`title` varchar(75) NOT NULL,
			`body` longtext NOT NULL,
			`keywords` text NOT NULL,
			`publish` int(11) NOT NULL DEFAULT '0',
			`alias` varchar(75) NOT NULL,
			`author` varchar(75) NOT NULL,
			`category` int(11) NOT NULL,
			`comments` int(11) NOT NULL,
			`date` datetime NOT NULL DEFAULT '2012-01-01 00:00:00',
			PRIMARY KEY (`id`)
	  		) ENGINE=MyISAM DEFAULT CHARSET=utf8 ;";

	  		$uc_users = "CREATE TABLE IF NOT EXISTS `".UC_PREFIX."users` (
			`id` int(255) NOT NULL AUTO_INCREMENT,
			`login` varchar(255) NOT NULL,
			`password` varchar(75) NOT NULL,
			`group` int(11) NOT NULL DEFAULT '4',
			`avatar` varchar(255) NOT NULL,
			`email` varchar(255) NOT NULL,
			`activation` int(1) NOT NULL,
			`date` datetime NOT NULL DEFAULT '2012-01-01 00:00:00',
			`session_hash` varchar(40) NOT NULL,
			`regip` varchar(14) NOT NULL,
			`logip` varchar(14) NOT NULL,
			`online` int(11) NOT NULL,
			`lastlogin` datetime NOT NULL DEFAULT '2012-01-01 00:00:00',
			PRIMARY KEY (`id`)
	  		) ENGINE=MyISAM DEFAULT CHARSET=utf8 ;";
			
	  		$uc_usersinfo = "CREATE TABLE IF NOT EXISTS `".UC_PREFIX."usersinfo` (
			`id` int(11) NOT NULL AUTO_INCREMENT,
			`userid` int(11) NOT NULL,
			`firstname` varchar(75) NOT NULL,
			`surname` varchar(75) NOT NULL,
			`icq` varchar(75) NOT NULL,
			`skype` varchar(75) NOT NULL,
			`addinfo` text NOT NULL,
			`birthdate` date NOT NULL DEFAULT '2012-01-01',
			`pm-alert` int(11) NOT NULL DEFAULT '0',
			PRIMARY KEY (`id`)
	  		) ENGINE=MyISAM DEFAULT CHARSET=utf8 ;";

			$uc_settings = "CREATE TABLE IF NOT EXISTS `".UC_PREFIX."settings` (
			`id` int(11) NOT NULL AUTO_INCREMENT,
			`name` varchar(75) NOT NULL,
			`value` varchar(75) NOT NULL DEFAULT '0',
			PRIMARY KEY (`id`)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8 ;";

			$uc_stats = "CREATE TABLE IF NOT EXISTS `".UC_PREFIX."stats` (
			`id` int(11) NOT NULL AUTO_INCREMENT,
			`name` varchar(75) NOT NULL,
			`value` varchar(75) NOT NULL DEFAULT '0',
			`update` datetime NOT NULL DEFAULT '2012-01-01 00:00:00',
			PRIMARY KEY (`id`)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8 ;";

			$uc_themes = "CREATE TABLE IF NOT EXISTS `".UC_PREFIX."themes` (
			`id` int(11) NOT NULL AUTO_INCREMENT,
			`name` varchar(75) NOT NULL,
			`version` varchar(75) NOT NULL,
			`author` varchar(75) NOT NULL,
			`site` varchar(75) NOT NULL,
			`description` varchar(75) NOT NULL,
			`dir` varchar(75) NOT NULL,
			`activated` int(11) NOT NULL DEFAULT '0',
			PRIMARY KEY (`id`)
			) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;";
			
			$uc_widgets = "CREATE TABLE IF NOT EXISTS `".UC_PREFIX."widgets` (
			`id` int(11) NOT NULL AUTO_INCREMENT,
			`name` varchar(75) NOT NULL,
			`version` varchar(75) NOT NULL,
			`author` varchar(75) NOT NULL,
			`site` varchar(75) NOT NULL,
			`description` varchar(75) NOT NULL,
			`dir` varchar(75) NOT NULL,
			`activated` int(11) NOT NULL DEFAULT '0',
			PRIMARY KEY (`id`)
			) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;";
			
			$uc_links = "CREATE TABLE IF NOT EXISTS `".UC_PREFIX."links` (
			`id` int(11) NOT NULL AUTO_INCREMENT,
			`name` varchar(75) NOT NULL,
			`publish` varchar(75) NOT NULL,
			`url` varchar(75) NOT NULL,
			`description` varchar(75) NOT NULL,
			`author` varchar(75) NOT NULL,
			`target` varchar(75) NOT NULL,
			`date` datetime NOT NULL DEFAULT '2012-01-01 00:00:00',
			PRIMARY KEY (`id`)
			) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;";

			$udb->query($uc_attempts);
			$udb->query($uc_categories);
			$udb->query($uc_comments);
			$udb->query($uc_groups);
			$udb->query($uc_messages);
			$udb->query($uc_pages);
			$udb->query($uc_posts);
			$udb->query($uc_users);
			$udb->query($uc_usersinfo);
			$udb->query($uc_settings);
			$udb->query($uc_stats);
			$udb->query($uc_themes);
			$udb->query($uc_widgets);
			$udb->query($uc_links);
			header("Location: index.php");
			return true;
		}	
	}

	function fill_tables(){
		global $udb, $con;
		$test = $udb->query("SELECT `ip` FROM `".UC_PREFIX."attempts` LIMIT 1");
		$test2 = $udb->query("SELECT `id` FROM `".UC_PREFIX."categories` LIMIT 1");
		$test3 = $udb->query("SELECT `id` FROM `".UC_PREFIX."comments` LIMIT 1");
		$test4 = $udb->query("SELECT `id` FROM `".UC_PREFIX."groups` LIMIT 1");
		$test5 = $udb->query("SELECT `id` FROM `".UC_PREFIX."messages` LIMIT 1");
		$test6 = $udb->query("SELECT `id` FROM `".UC_PREFIX."pages` LIMIT 1");
		$test7 = $udb->query("SELECT `id` FROM `".UC_PREFIX."posts` LIMIT 1");
		$test8 = $udb->query("SELECT `id` FROM `".UC_PREFIX."themes` LIMIT 1");
		$test9 = $udb->query("SELECT `id` FROM `".UC_PREFIX."users` LIMIT 1");
		$test10 = $udb->query("SELECT `id` FROM `".UC_PREFIX."settings` LIMIT 1");
		$test11 = $udb->query("SELECT `id` FROM `".UC_PREFIX."stats` LIMIT 1");
		$test12 = $udb->query("SELECT `id` FROM `".UC_PREFIX."usersinfo` LIMIT 1");
		$test13 = $udb->query("SELECT `id` FROM `".UC_PREFIX."links` LIMIT 1");
		$test14 = $udb->query("SELECT `id` FROM `".UC_PREFIX."widgets` LIMIT 1");
		if($test and $test2 and $test3 and $test4 and $test5 and $test6 and $test7 and $test8 and $test9 and $test10 and $test11 and $test12 and $test13 and $test14){
			$categories = $udb->num_rows("SELECT * FROM `".UC_PREFIX."categories`");
			$themes = $udb->num_rows("SELECT * FROM `".UC_PREFIX."themes`");
			$settings = $udb->num_rows("SELECT * FROM `".UC_PREFIX."settings`");
			$stats = $udb->num_rows("SELECT * FROM `".UC_PREFIX."stats`");
			$groups = $udb->num_rows("SELECT * FROM `".UC_PREFIX."groups`");
			$users = $udb->num_rows("SELECT * FROM `".UC_PREFIX."users`");
			$widgets = $udb->num_rows("SELECT * FROM `".UC_PREFIX."widgets`");
			if($categories <= 0){
				$uc_categories = "INSERT IGNORE INTO `".UC_PREFIX."categories` (`id`, `name`, `alias`, `posts`) VALUES (1, 'Без категории', 'uncategorized', '1');";
				$udb->query($uc_categories);
			}

			if($themes <= 0){
				$uc_themes = "INSERT IGNORE INTO `".UC_PREFIX."themes` (`id`, `name`, `version`, `author`, `site`, `description`, `dir`, `activated`) VALUES
				(1, 'uCMS', '1.2', 'IVaN4B', 'http://ivan4b.ru', 'Стандартная тема uCMS.', 'ucms', '1') ;";
				$udb->query($uc_themes);
			}

			if($groups < 7){
				$uc_groups = "INSERT IGNORE INTO `".UC_PREFIX."groups` (`id`, `name`, `alias`, `permissions`) VALUES
	  			(1, 'Администратор', 'admin', '7777'),
	  			(2, 'Модератор', 'moderator', '5555'),
	  			(3, 'Проверенный', 'trusted', '1313'),
	  			(4, 'Пользователь', 'users', '1212'),
	  			(5, 'Забаненный', 'banned', '0000'),
	  			(6, 'Гость', 'guest', '1110'),
	  			(7, 'Читатель', 'reader', '1111');";

	  			$udb->query("TRUNCATE TABLE `".UC_PREFIX."groups`");
				$udb->query($uc_groups);
			}
			
			if($stats <= 0){
				$uc_stats = "INSERT IGNORE INTO `".UC_PREFIX."stats` (`id`, `name`, `value`, `update`) VALUES
	  			(1, 'guest_count', '0', NOW()),
	  			(2, 'update_user', '0', NOW());";
				$udb->query($uc_stats);
			}

			if($widgets <= 0){
				$uc_widgets = "INSERT IGNORE INTO `".UC_PREFIX."widgets` (`id`, `name`, `version`, `author`, `site`, `description`, `dir`, `activated`) VALUES
				(1, 'Главное меню', '1.2', 'IVaN4B', 'http://ivan4b.ru', 'Стандартный виджет uCMS.', 'menu_links', '1'),
				(2, 'Архив постов', '1.2', 'IVaN4B', 'http://ivan4b.ru', 'Стандартный виджет uCMS.', 'post_archives', '1'),
				(3, 'Категории постов', '1.2', 'IVaN4B', 'http://ivan4b.ru', 'Стандартный виджет uCMS.', 'post_categories', '1'),
				(4, 'Теги постов', '1.2', 'IVaN4B', 'http://ivan4b.ru', 'Стандартный виджет uCMS.', 'post_tags', '1'),
				(5, 'Форма поиска', '1.2', 'IVaN4B', 'http://ivan4b.ru', 'Стандартный виджет uCMS.', 'search_form', '1'),
				(6, 'Список ссылок', '1.2', 'IVaN4B', 'http://ivan4b.ru', 'Стандартный виджет uCMS.', 'site_links', '1'),
				(7, 'Статистика', '1.2', 'IVaN4B', 'http://ivan4b.ru', 'Стандартный виджет uCMS.', 'site_stats', '1'),
				(8, 'Профиль пользователя', '1.2', 'IVaN4B', 'http://ivan4b.ru', 'Стандартный виджет uCMS.', 'user_menu', '1'),
				(9, 'Новые комментарии', '1.2', 'IVaN4B', 'http://ivan4b.ru', 'Стандартный виджет uCMS.', 'new_comments', '1');";
				$udb->query($uc_widgets);
			}

			if($settings <= 0){
				if(isset($_POST['domain']) and $_POST['domain'] != '' and isset($_POST['site_name']) and $_POST['site_name'] != '' and isset($_POST['site_description']) and $_POST['site_description'] != '' and isset($_POST['site_title']) and $_POST['site_title'] != ''){
					$domain = $udb->parse_value($_POST['domain']);
					$site_name = $udb->parse_value($_POST['site_name']);
					$site_description = $udb->parse_value($_POST['site_description']);
					$site_title = $udb->parse_value($_POST['site_title']);
					$dir = ($_POST['dir'] != '' and $_POST['dir'] != '/') ? $udb->parse_value($_POST['dir']) : '';
					$uc_settings = "INSERT IGNORE INTO `".UC_PREFIX."settings` (`id`, `name`, `value`) VALUES
					(1, 'site_name', '$site_name'),
					(2, 'site_description', '$site_description'),
					(3, 'site_title', '$site_title'),
					(4, 'nice_links', '0'),
					(5, 'user_avatars', '1'),
					(6, 'user_messages', '1'),
					(7, 'domain', '$domain'),
					(8, 'theme_dir', 'ucms'),
					(9, 'theme_name', 'uCMS'),
					(10, 'num_tries', '10'),
					(11, 'ucms_dir', '$dir'),
					(12, 'ucms_maintenance', '0'),
					(13, 'phpmyadmin', ''),
					(14, 'unique_emails', '1'),
					(15, 'modules', '1,1,1,1,1,1'),
					(16, 'posts_on_page', '10'),
					(17, 'default_group', '4'),
					(18, 'allow_registration', '1'),
					(19, 'admin_email', ''),
					(20, 'comments_email', ''),
					(21, 'new_user_email', ''),
					(22, 'timezone', 'UTC'),
					(23, 'post_sef_link', '@alias@'),
					(24, 'page_sef_link', 'pages/@alias@'),
					(25, 'category_sef_prefix', 'category'),
					(26, 'tag_sef_prefix', 'tag'),
					(27, 'use_captcha', '1'),
					(28, 'site_author', ''),
					(29, 'avatar_width', '150'),
					(30, 'avatar_height', '150');";
					$udb->query($uc_settings);
					$_SESSION['fill-tables2'] = true;
					header("Location: index.php");
					return true;
				}
				
			}elseif($users <= 0){
				if(isset($_POST['setup-login']) and $_POST['setup-login'] != '' and isset($_POST['setup-password']) and $_POST['setup-password'] != '' and isset($_POST['setup-email']) and $_POST['setup-email'] != ''){
					$login = $udb->parse_value($_POST['setup-login']);
					$login = htmlspecialchars($login);
					$login = trim($login);
					$reg = "/[^(\w)|(\x7F-\xFF)|(\s)]/";
					$login = preg_replace($reg,'',$login);
					if (mb_strlen($login, "UTF-8") < 4 or mb_strlen($login, "UTF-8") > 16){
						echo '<br>Логин должен содержать не менее 4-х символов и не более 16.';
						$error = true;
						
					}
					$password = $udb->parse_value($_POST['setup-password']);
					$password = htmlspecialchars(trim($password));
					if (strlen($password) < 6 or strlen($password) > 20){
						echo '<br>Пароль должен содержать не менее 6-х символов и не более 20.';
						if(!$error){
							$error = true;
						}
					}
					$salt = substr(sha1($password),0,22);
					$password = $udb->parse_value(stripslashes($password));
					$password = htmlspecialchars(trim($password));
					$password = crypt($password, '$2a$10$'.$salt);
					$email = $udb->parse_value($_POST['setup-email']);
					if (!preg_match("/@/i", $email)) {
						echo '<br>Неправильно введен е-mail.';
						if(!$error){
							$error = true;
						}
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
					$_SESSION['login'] = $login;
					$_SESSION['password'] = $password; 
					$_SESSION['hash'] = $hash;
					
					$uc_users = "INSERT IGNORE INTO `".UC_PREFIX."users` VALUES (1, '$login','$password', '1', 'no-avatar.jpg', '$email', '1', NOW(), '$hash', '$ip', '$ip', '1', NOW())";
					$uc_usersinfo = "INSERT IGNORE INTO `".UC_PREFIX."usersinfo` VALUES (1, '1','$login', 'Админов', '', '', '', NOW(), '0')";
					$uc_posts = "INSERT IGNORE INTO `".UC_PREFIX."posts` VALUES (1, 'Привет мир!','Это ваш первый пост!', 'первый, пост, сайт', '1', 'hello-world', '1', '1', '1', NOW())";
					$uc_comments = "INSERT IGNORE INTO `".UC_PREFIX."comments` VALUES (1, '1','А это ваш первый комментарий!', '1', '1', NOW())";
					$uc_pages = "INSERT IGNORE INTO `".UC_PREFIX."pages` VALUES (1, 'Страница','first-page', '1', 'А это ваша первая страница!', '1', NOW())";
					$udb->query("UPDATE `".UC_PREFIX."settings` SET `value` = '$email' WHERE `id` = '19'");
					$udb->query("UPDATE `".UC_PREFIX."settings` SET `value` = '$email' WHERE `id` = '20'");
					$udb->query("UPDATE `".UC_PREFIX."settings` SET `value` = '$email' WHERE `id` = '21'");
					$udb->query("UPDATE `".UC_PREFIX."settings` SET `value` = '$login' WHERE `id` = '28'");
					if(!$error){
						$udb->query($uc_users); 
						$udb->query($uc_usersinfo);
						$udb->query($uc_posts);
						$udb->query($uc_comments);
						$udb->query($uc_pages);
						$_SESSION['success'] = true;
						header("Location: index.php");
						return true;
					}else{
						echo '<br><br><a href="index.php">Повторить</a>';
					}
				}						
			}
		}
		header("Location: index.php");
		return true;
	}

if(isset($_POST['config']))
	config_file();
if(isset($_POST['add-tables']))
	add_tables();
if(isset($_POST['fill-tables']) or isset($_SESSION['fill-tables']))
	fill_tables();

?>