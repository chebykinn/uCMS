<?php
class uCMS{
	function settings(){
		global $udb;
		if(isset($_POST['settings-update'])){
			$site_name = $udb->parse_value($_POST['site_name']);
			$site_description = $udb->parse_value($_POST['site_description']);
			$site_additional_name = $udb->parse_value($_POST['site_additional_name']);
			$ucms_maintenance = isset($_POST['ucms_maintenance']) ? $udb->parse_value($_POST['ucms_maintenance']) : 0;
			$nice_links = isset($_POST['nice_links']) ? $udb->parse_value($_POST['nice_links']) : 0;
			$user_avatars = isset($_POST['user_avatars']) ? $udb->parse_value($_POST['user_avatars']) : 0;
			$user_messages = isset($_POST['user_messages']) ? $udb->parse_value($_POST['user_messages']) : 0;
			$posts_module = isset($_POST['posts_module']) ? 1 : 0;
			$comments_module = isset($_POST['comments_module']) ? 1 : 0;
			$pages_module = isset($_POST['pages_module']) ? 1 : 0;
			$users_module = isset($_POST['users_module']) ? 1 : 0;
			$themes_module = isset($_POST['themes_module']) ? 1 : 0;
			$widgets_module = isset($_POST['widgets_module']) ? 1 : 0;
			$posts_on_page = isset($_POST['posts_on_page']) ? $udb->parse_value($_POST['posts_on_page']) : 10;
			$modules = $posts_module.','.$comments_module.','.$pages_module.','.$users_module.','.$themes_module.','.$widgets_module;
			$domain = $udb->parse_value($_POST['domain']);
			$num_tries = isset($_POST['num_tries']) ? $udb->parse_value($_POST['num_tries']) : 10;
			$ucms_dir = $_POST['ucms_dir'] != '/' ? $udb->parse_value($_POST['ucms_dir']) : '';
			$default_group = isset($_POST['default_group']) ? $udb->parse_value($_POST['default_group']) : 4;
			$allow_registration = isset($_POST['allow_registration']) ? 1 : 0;
			$post_sef_link = $_POST['post_sef_link'] != '' ? $udb->parse_value($_POST['post_sef_link']) : '@alias@';
			$page_sef_link = $_POST['page_sef_link'] != '' ? $udb->parse_value($_POST['page_sef_link']) : 'pages/@alias@';
			$category_sef_prefix = $_POST['category_sef_prefix'] != '' ? $udb->parse_value($_POST['category_sef_prefix']) : 'category';
			$tag_sef_prefix = $_POST['tag_sef_prefix'] != '' ? $udb->parse_value($_POST['tag_sef_prefix']) : 'tag';
			$timezone = $udb->parse_value(preg_replace("#(\n)#", '', $_POST['timezone']));
			$use_captcha = (int) $_POST['use_captcha'];
			$site_author = $udb->parse_value($_POST['site_author']);
			$avatar_width = isset($_POST['avatar_width']) ? (int) $_POST['avatar_width'] : 150;
			$avatar_height = isset($_POST['avatar_height']) ? (int) $_POST['avatar_height']: 150;
			if($ucms_dir != ''){
				$htaccess = "<IfModule mod_rewrite.c>
RewriteEngine On
RewriteBase /
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule . $ucms_dir/index.php [L]
</IfModule>";
				
			}else{
				$htaccess = "<IfModule mod_rewrite.c>
RewriteEngine On
RewriteBase /
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule . index.php [L]
</IfModule>";
			}
			$file = fopen("../.htaccess", "w+");
			fprintf($file,'%s', $htaccess);
			fclose($file);
			$unique_emails = isset($_POST['unique_emails']) ? $udb->parse_value($_POST['unique_emails']) : 0;
			$phpmyadmin = $udb->parse_value($_POST['phpmyadmin']);
			if(!empty($site_name) and !empty($domain) and !empty($modules)){
				$udb->query("UPDATE `".UC_PREFIX."settings` SET `value` = '$site_name' WHERE `id` = 1;");
				$udb->query("UPDATE `".UC_PREFIX."settings` SET `value` = '$site_description' WHERE `id` = 2;");
				$udb->query("UPDATE `".UC_PREFIX."settings` SET `value` = '$site_additional_name' WHERE `id` = 3;");
				$udb->query("UPDATE `".UC_PREFIX."settings` SET `value` = '$nice_links' WHERE `id` = 4;");
				$udb->query("UPDATE `".UC_PREFIX."settings` SET `value` = '$user_avatars' WHERE `id` = 5;");
				$udb->query("UPDATE `".UC_PREFIX."settings` SET `value` = '$user_messages' WHERE `id` = 6;");
				$udb->query("UPDATE `".UC_PREFIX."settings` SET `value` = '$domain' WHERE `id` = 7;");
				$udb->query("UPDATE `".UC_PREFIX."settings` SET `value` = '$num_tries' WHERE `id` = 10;");
				$udb->query("UPDATE `".UC_PREFIX."settings` SET `value` = '$ucms_dir' WHERE `id` = 11;");
				$udb->query("UPDATE `".UC_PREFIX."settings` SET `value` = '$ucms_maintenance' WHERE `id` = 12;");
				$udb->query("UPDATE `".UC_PREFIX."settings` SET `value` = '$phpmyadmin' WHERE `id` = 13;");
				$udb->query("UPDATE `".UC_PREFIX."settings` SET `value` = '$unique_emails' WHERE `id` = 14;");
				$udb->query("UPDATE `".UC_PREFIX."settings` SET `value` = '$modules' WHERE `id` = 15;");
				$udb->query("UPDATE `".UC_PREFIX."settings` SET `value` = '$posts_on_page' WHERE `id` = 16;");
				$udb->query("UPDATE `".UC_PREFIX."settings` SET `value` = '$default_group' WHERE `id` = 17;");
				$udb->query("UPDATE `".UC_PREFIX."settings` SET `value` = '$allow_registration' WHERE `id` = 18;");
				$udb->query("UPDATE `".UC_PREFIX."settings` SET `value` = '$timezone' WHERE `id` = 22;");
				$udb->query("UPDATE `".UC_PREFIX."settings` SET `value` = '$post_sef_link' WHERE `id` = 23;");
				$udb->query("UPDATE `".UC_PREFIX."settings` SET `value` = '$page_sef_link' WHERE `id` = 24;");
				$udb->query("UPDATE `".UC_PREFIX."settings` SET `value` = '$category_sef_prefix' WHERE `id` = 25;");
				$udb->query("UPDATE `".UC_PREFIX."settings` SET `value` = '$tag_sef_prefix' WHERE `id` = 26;");
				$udb->query("UPDATE `".UC_PREFIX."settings` SET `value` = '$use_captcha' WHERE `id` = 27;");
				$udb->query("UPDATE `".UC_PREFIX."settings` SET `value` = '$site_author' WHERE `id` = 28;");
				$udb->query("UPDATE `".UC_PREFIX."settings` SET `value` = '$avatar_width' WHERE `id` = 29;");
				$udb->query("UPDATE `".UC_PREFIX."settings` SET `value` = '$avatar_height' WHERE `id` = 30;");
				header("Location: $ucms_dir/admin/settings.php");
				$_SESSION['success'] = true;
			}else{
				echo '<div class="error">Нужно заполнить все необходимые поля!</div>';
			}
			
		}else{
			if(isset($_SESSION['success'])) {
				echo '<div class="success">Настройки успешно изменены.</div>';
				unset($_SESSION['success']);
			}
		}
			
	}

	function get_date($time = true, $format = true, $seconds = true, $only_time = false, $delimiter = '-', $datetime_delimiter = " в "){
		if($seconds)
			$dtime = date('H:i:s');
		else
			$dtime = date('H:i');
		if($only_time) return $dtime;
		$months = array(
				'01'=>'Января', 
				'02'=>'Февраля', 
				'03'=>'Марта', 
				'04'=>'Апреля', 
				'05'=>'Мая', 
				'06'=>'Июня', 
				'07'=>'Июля', 
				'08'=>'Августа', 
				'09'=>'Cентября', 
				'10'=>'Октября', 
				'11'=>'Ноября', 
				'12'=>'Декабря');
		$date = date('Y'.$delimiter.'m'.$delimiter.'d H:i:s');
		if(!$format){
			if(!$seconds) $date = date('Y'.$delimiter.'m'.$delimiter.'d H:i');
			if(!$time) $date = date('Y'.$delimiter.'m'.$delimiter.'d');
			return $date;
		}
		if($time){
			if($seconds)
				$date = preg_replace('~(\d{4})-(\d{2})-(0(\d)|([^0]\d)) (\d{2}):(\d{2}):(\d{2})~e', '"\\4\\5 ".$months["\\2"]." \\1'.$datetime_delimiter.'\\6:\\7:\\8"', $date);
			else
				$date = preg_replace('~(\d{4})-(\d{2})-(0(\d)|([^0]\d)) (\d{2}):(\d{2}):(\d{2})~e', '"\\4\\5 ".$months["\\2"]." \\1'.$datetime_delimiter.'\\6:\\7"', $date);
		}else
			$date = preg_replace('~(\d{4})-(\d{2})-(0(\d)|([^0]\d)) (\d{2}):(\d{2}):(\d{2})~e', '"\\4\\5 ".$months["\\2"]." \\1"', $date);
		return $date;
	}

	function format_date($date, $time = true, $seconds = true, $datetime_delimiter = " в "){
		$months = array(
				'01'=>'Января', 
				'02'=>'Февраля', 
				'03'=>'Марта', 
				'04'=>'Апреля', 
				'05'=>'Мая', 
				'06'=>'Июня', 
				'07'=>'Июля', 
				'08'=>'Августа', 
				'09'=>'Cентября', 
				'10'=>'Октября', 
				'11'=>'Ноября', 
				'12'=>'Декабря');
		if($time){
			if($seconds)
				$date = preg_replace('~(\d{4})-(\d{2})-(0(\d)|([^0]\d)) (\d{2}):(\d{2}):(\d{2})~e', '"\\4\\5 ".$months["\\2"]." \\1'.$datetime_delimiter.'\\6:\\7:\\8"', $date);
			else
				$date = preg_replace('~(\d{4})-(\d{2})-(0(\d)|([^0]\d)) (\d{2}):(\d{2}):(\d{2})~e', '"\\4\\5 ".$months["\\2"]." \\1'.$datetime_delimiter.'\\6:\\7"', $date);
		}else
		if(preg_match('~(\d{2}):(\d{2}):(\d{2})~e', $date))
			$date = preg_replace('~(\d{4})-(\d{2})-(0(\d)|([^0]\d)) (\d{2}):(\d{2}):(\d{2})~e', '"\\4\\5 ".$months["\\2"]." \\1"', $date);
		else
			$date = preg_replace('~(\d{4})-(\d{2})-(0(\d)|([^0]\d))~e', '"\\4\\5 ".$months["\\2"]." \\1"', $date);
		return $date;
	}

	function get_back_url(){
		$back_link = (isset($_SERVER['HTTP_REFERER']) and preg_match("#(".SITE_DOMAIN.")#", $_SERVER['HTTP_REFERER'])) ? $_SERVER['HTTP_REFERER'] : SITE_DOMAIN.UCMS_DIR;
		return $back_link;
	}

	function panic($err_code){
		global $udb, $con, $user, $ucms, $widget, $url, $action;
		if(UCMS_DEBUG){
			echo "<b>uCMS Error ".$err_code."</b><br>";
			echo "<b>Debug Trace:</b>";
			echo "<pre>";
			debug_print_backtrace();
			echo "</pre>";
		}	
		switch ($err_code) {
			case 1:
				$action = 'panic';
				if(UCMS_DEBUG)
					echo "<br>Default php error message loaded from <b>".ERROR_TEMPLATES_PATH."version.php:</b><br><br>";
				require_once ERROR_TEMPLATES_PATH.'version.php';
			exit;
			
			case 404:
				if(UCMS_DEBUG)
					echo "<br><b>Action:</b> ".($action == '' ? 'none' : $action)." <b>URL:</b> $url<br><br>";
				$action = 'error404';
				header("HTTP/1.0 404 Not Found");
				if(file_exists(THEMEPATH.'error.php')){	
					if(UCMS_DEBUG)
						echo "<br>Theme's error 404 message loaded from <b>".THEMEPATH."error.php:</b><br><br>";
					require_once THEMEPATH.'error.php';
				}else{
					if(UCMS_DEBUG)
						echo "<br>Default error 404 message loaded from <b>".ERROR_TEMPLATES_PATH."error$err_code.php:</b><br><br>";
					require_once ERROR_TEMPLATES_PATH.'error'.$err_code.'.php';
				}
				$udb->db_disconnect($con);
			exit;
		}
	}

	function transliterate($str){
		$tr = array(
        "А"=>"A","Б"=>"B","В"=>"V","Г"=>"G",
        "Д"=>"D","Е"=>"E","Ё"=>"YO","Ж"=>"J","З"=>"Z","И"=>"I",
        "Й"=>"Y","К"=>"K","Л"=>"L","М"=>"M","Н"=>"N",
        "О"=>"O","П"=>"P","Р"=>"R","С"=>"S","Т"=>"T",
        "У"=>"U","Ф"=>"F","Х"=>"H","Ц"=>"TS","Ч"=>"CH",
        "Ш"=>"SH","Щ"=>"SCH","Ъ"=>"","Ы"=>"YI","Ь"=>"",
        "Э"=>"E","Ю"=>"YU","Я"=>"YA","а"=>"a","б"=>"b",
        "в"=>"v","г"=>"g","д"=>"d","е"=>"e","ё"=>"yo","ж"=>"j",
        "з"=>"z","и"=>"i","й"=>"y","к"=>"k","л"=>"l",
        "м"=>"m","н"=>"n","о"=>"o","п"=>"p","р"=>"r",
        "с"=>"s","т"=>"t","у"=>"u","ф"=>"f","х"=>"h",
        "ц"=>"ts","ч"=>"ch","ш"=>"sh","щ"=>"sch","ъ"=>"y",
        "ы"=>"yi","ь"=>"","э"=>"e","ю"=>"yu","я"=>"ya"
    	);
    	return strtr($str, $tr);
	}

	
	function remove_dir($path){
		if(file_exists($path) && is_dir($path)){
			$dirHandle = opendir($path);
			while (false !== ($file = readdir($dirHandle))){
				if ($file!='.' && $file!='..'){
					$tmpPath=$path.'/'.$file;
					chmod($tmpPath, 0777);
					
					if (is_dir($tmpPath)){  
						$this->remove_dir($tmpPath);
				   	} 
		  			else{ 
		  				if(file_exists($tmpPath)){
		  					unlink($tmpPath);
						}
		  			}
				}
			}
			closedir($dirHandle);
			if(file_exists($path)){
				rmdir($path);
			}
		}else{
			echo "Удаляемой папки не существует или это файл!";
		}
	}

	

	function get_load_time(){
		global $time_start;
		$current_time = microtime();
		$current_time = explode(" ",$current_time);
		$current_time = $current_time[1] + $current_time[0];
		$time = ($current_time - $time_start);
		$time = number_format($time, 3);
		return $time;
	}

	function check_php_version(){
		if((float) PHP_VERSION < UCMS_MIN_PHP_VERSION){
			$this->panic(1);
		}
	}
}
?>