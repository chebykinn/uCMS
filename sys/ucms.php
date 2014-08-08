<?php
/**
* uCMS main class.
*
* @package uCMS
* @since 1.0
* @version 1.3
*
*/
class uCMS{
	var $language_strings;
	var $updated_settings = array();
	function settings(){
		global $udb, $event;
		if(isset($_POST['settings-update'])){
			global $type, $extention;
			$owner = substr($type, 0, 1).":$extention";
			if($extention == 'general') $owner = 'system';
			$values = $_POST;
			$event->do_actions("ucms.settings", array($values));
			$error = false;
			foreach ($values as $key => $value) {
				if($value == ""){
					if(isset($values[$key."_default"])){
						$value = $udb->parse_value($values[$key."_default"]);
					}
				}
				$value = htmlspecialchars($value);
				switch ($key) {
					case 'nice_links':
						$nice_links = $value;
					break;

					case 'ucms_dir':
						if(substr($value, 0, 1) != "/"){
							$value = "/".$value;
						}
						if(substr($value, -1) == '/'){
							$value = substr($value, 0, strlen($value)-1);
						}
						$ucms_dir = $value;
					break;

					case 'site_domain':
						if(!preg_match("#(http://)#", $value)){
							$value = "http://".$value;
						}
						if(substr($value, -1) == '/'){
							$value = substr($value, 0, strlen($value)-1);
						}
					break;

					case 'date_format_manual':
						$key = 'date_format';
					break;

					case 'time_format_manual':
						$key = 'time_format';
					break;

					case 'system_language':
						if($value != SYSTEM_LANGUAGE)
							$this->change_language($value);
					break;
				}

				if(!in_array($key, $this->updated_settings)){
					if($this->is_setting($key)){
						$upd = ($owner == $this->get_setting_owner($key)) ? $this->update_setting($key, $value) : false;
						if(!$upd){
							$error = true;
							break;
						}
					}
				}
			}
			if(isset($nice_links) and isset($ucms_dir)){
				if($ucms_dir != UCMS_DIR or $nice_links != NICE_LINKS){
					if($nice_links){
						$this->write_htaccess($ucms_dir);
					}
					else{
						if(file_exists("../.htaccess")){
							unlink("../.htaccess");
						}
					}
				}
			}
			if($error){
				echo '<div class="error">'.$this->cout("admin.settings.error", true).'</div>';
			}else{
				if($type == 'system') $type = 'module';
				header("Location: settings.php?$type=".$_GET[$type]);
				$_SESSION['success'] = true;
			}	
		}else{
			if(isset($_SESSION['success'])) {
				echo '<div class="success">'.$this->cout("admin.settings.success", true).'</div>';
				unset($_SESSION['success']);
			}
		}
			
	}

	/**
	* uCMS add setting function.
	*
	* @package uCMS
	* @since 1.3
	* @version 1.3
	* @return bool
	*
	*/
	function add_setting($name, $default){
		global $udb, $module_accessID;
		$name = $udb->parse_value($name);
		$value = $udb->parse_value($default);
		$owner = $this->get_current_owner();
		if(!empty($module_accessID)){
			$owner = "m:".$module_accessID;
		}
		if(defined("OWNER_ID")){
			$owner = OWNER_ID;
		}
		if($this->get_setting_value($name) === NULL){
			$add = $udb->query("INSERT INTO `".UC_PREFIX."settings` (`id`, `name`, `value`, `update`, `owner`) VALUES(NULL, '$name', '$value', NOW(), '$owner')");
			return $add;
		}
		return false;
	}
	
	/**
	* uCMS update setting function.
	*
	* @package uCMS
	* @since 1.3
	* @version 1.3
	* @return bool
	*
	*/
	function update_setting($name, $value){
		global $udb, $module_accessID;
		$name = $udb->parse_value($name);
		$value = $udb->parse_value($value);
		$setting_owner = $this->get_setting_owner($name);
		$owner = $this->get_current_owner();
		if(!empty($module_accessID)){
			$owner = "m:".$module_accessID;
		}
		$owner = preg_match('#(admin/settings.php)#', $_SERVER['PHP_SELF']) ? $setting_owner : $owner;
		if($owner == $setting_owner){
			$upd = $udb->query("UPDATE `".UC_PREFIX."settings` SET `value` = '$value', `update` = NOW() WHERE `name` = '$name'");
			return $upd;
		}
		return false;
	}

	/**
	* uCMS delete setting function.
	*
	* @package uCMS
	* @since 1.3
	* @version 1.3
	* @return bool
	*
	*/
	function delete_setting($name){
		global $udb, $setting;
		foreach ($setting as $single) {
			if($single['name'] == $name)
				return false;
		}
		$name = $udb->parse_value($name);
		$owner = $this->get_current_owner();
		$setting_owner = $this->get_setting_owner($name);
		if($owner == $setting_owner){
			$del = $udb->query("DELETE FROM `".UC_PREFIX."settings` WHERE `name` = '$name'");
			return $del;
		}
		return false;
	}

	/**
	* uCMS get setting owner function.
	*
	* @package uCMS
	* @since 1.3
	* @version 1.3
	* @return value
	*
	*/
	function get_setting_owner($name){
		global $udb;
		$name = $udb->parse_value($name);
		return $udb->get_val("SELECT `owner` FROM `".UC_PREFIX."settings` WHERE `name` = '$name'");
	}

	/**
	* uCMS get existing setting.
	*
	* @package uCMS
	* @since 1.3
	* @version 1.3
	* @return if setting exists - value, else null.
	*
	*/
	function get_setting_value($name = ""){
		global $udb;
		$name = $udb->parse_value($name);
		return $udb->get_val("SELECT `value` FROM `".UC_PREFIX."settings` WHERE `name` = '$name'");
	}

	/**
	* uCMS check setting existense.
	*
	* @package uCMS
	* @since 1.3
	* @version 1.3
	* @return if setting exists - true, else false.
	*
	*/
	function is_setting($name = ""){
		global $udb;
		$name = $udb->parse_value($name);
		return $udb->get_row("SELECT `id` FROM `".UC_PREFIX."settings` WHERE `name` = '$name'");
	}

	function get_current_owner(){
		global $udb;
		$owner = 'system';
		$extentions = array("modules", "plugins", "widgets", "themes");
		$paths = debug_backtrace();
		foreach ($paths as $path) {
			if(isset($path['file'])){
				$path_array = explode('/', $path['file']);
				foreach ($extentions as $extention) {
					if(in_array($extention, $path_array)){
						$key = array_search($extention, $path_array);
						$owner = substr($extention, 0, 1).":".$path_array[$key+1];
					}
				}
			}
		}
		return $udb->parse_value($owner);
	}

	function write_htaccess($ucms_dir){
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
	}

	/**
	* Get current date string in selected format
	*
	* @package uCMS
	* @since 1.2
	* @version 1.3
	* @return string
	*
	*/
	function get_date($format = DATETIME_FORMAT){
		return $this->date_format(time(), DATETIME_FORMAT);
	}

	/**
	* uCMS Date format function
	*
	* @package uCMS
	* @since 1.3
	* @version 1.3
	* @return string
	*
	*/
	function date_format($date = "", $format = DATETIME_FORMAT){
		if(empty($date)) $date = time();

		if(is_string($date)){
			$date = strtotime($date);
		}
		
		if(preg_match("/rus/", $format)){ // Special datetime formatting for russians
			$format = preg_replace("/rus/", "", $format);
			$left = strftime($format, $date); 
			$date = $this->date_format($date, "%Y-%m-%d");
			$date = explode('-', $date);
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
			$year = $date[0];
			$month = $date[1];
			$day = $date[2];
			return $day.' '.$months[$month].' '.$year.$left;
		}else
			return strftime($format, $date);
	}

	/**
	* Get URL of previous page
	*
	* @package uCMS
	* @since 1.3
	* @version 1.3
	* @return string
	*
	*/
	function get_back_url(){
		$back_link = (isset($_SERVER['HTTP_REFERER']) and preg_match("#(".SITE_DOMAIN.")#", $_SERVER['HTTP_REFERER'])) ? $_SERVER['HTTP_REFERER'] : SITE_DOMAIN.UCMS_DIR;
		return $back_link;
	}

	/**
	* Displays errors for certain codes
	*
	* @package uCMS
	* @since 1.0
	* @version 1.3
	* @return nothing
	*
	*/
	function panic($err_code){
		global $udb, $con, $user, $ucms, $widget, $url, $action, $theme;
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
				require_once ERROR_TEMPLATES_PATH.'version.php';
			exit;
			
			case 404:
				if(UCMS_DEBUG){
					$action = $action == '' ? 'none' : $action;
					$this->cout("ucms.panic.err404.action.label", false, $action, $url);
				}
				$action = 'error404';
				header("HTTP/1.0 404 Not Found");
				if(file_exists(THEMEPATH.'error.php')){	
					$file = THEMEPATH."error.php";
					if(UCMS_DEBUG){
						$this->cout("ucms.panic.err404.message.theme", false, $file);
					}
					require_once $file;
				}else{
					$file = ERROR_TEMPLATES_PATH.'error'.$err_code.'.php';
					if(UCMS_DEBUG){
						$this->cout("ucms.panic.err404.message.default", false, $file);
					}
					require_once $file;
				}
				$udb->db_disconnect($con);
			exit;
		}
	}

	/**
	* Special function for russians - it turns russian literals into english ones.
	*
	* @package uCMS
	* @since 1.0
	* @version 1.3
	* @return string
	*
	*/
	function transliterate($string){
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
    	return strtr($string, $tr);
	}

	/**
	* Remove dir with files
	*
	* @package uCMS
	* @since 1.0
	* @version 1.3
	* @return nothing
	*
	*/
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
			$this->cout("ucms.remove_dir.error");
		}
	}

	/**
	* Get site load time
	*
	* @package uCMS
	* @since 1.0
	* @version 1.3
	* @return float
	*
	*/
	function get_load_time(){
		global $time_start;
		$current_time = microtime();
		$current_time = explode(" ",$current_time);
		$current_time = $current_time[1] + $current_time[0];
		$time = ($current_time - $time_start);
		$time = number_format($time, 3);
		return $time;
	}

	/**
	* Check PHP and MySQL versions and throw an error if they're obsolete
	*
	* @package uCMS
	* @since 1.2
	* @version 1.3
	* @return nothing
	*
	*/
	function check_php_mysql_version(){
		global $udb;
		$mysql = $udb->mysql_version() ? (float) $udb->mysql_version() : UCMS_MIN_MYSQL_VERSION;
		if((float) PHP_VERSION < UCMS_MIN_PHP_VERSION or $mysql < UCMS_MIN_MYSQL_VERSION){
			$this->panic(1);
		}
	}

	/**
	* Load language strings from selected file
	*
	* @package uCMS
	* @since 1.3
	* @version 1.3
	* @return nothing
	*
	*/
	function set_language($lang_file = ""){
		$exists = false;
		if($lang_file == ""){
			$lang_file = ABSPATH.LANGUAGES_PATH.SYSTEM_LANGUAGE.'.lang';
			if(!file_exists($lang_file)){
				$lang_file = ABSPATH.LANGUAGES_PATH.'en_US.lang';
			}
			$exists = true;
		}else{
			if(!file_exists($lang_file)){
				$lang_file = preg_replace("#(".SYSTEM_LANGUAGE.".lang)#", "", $lang_file);
				if(file_exists($lang_file.'en_US.lang')){
					$lang_file = $lang_file.'en_US.lang';
				}elseif(file_exists($lang_file.'ru_RU.lang')){
					$lang_file = $lang_file.'ru_RU.lang';
				}else return false;
			}
			$exists = true;
		}

		if($exists){
			$strings = file($lang_file);
			foreach ($strings as $string) {
				if(!empty($string) and substr($string, 0, 1) != '#'){
					$str = explode("=", $string, 2);
					if(is_array($str) and isset($str[0]) and isset($str[1]) and !empty($str[0]) and !isset($this->language_strings[$str[0]])){
						
						$this->language_strings[$str[0]] = $this->parse_line($str[1]);
					}
				}
			}
		}
	}

	/**
	* Load language data from selected file
	*
	* @package uCMS
	* @since 1.3
	* @version 1.3
	* @return nothing
	*
	*/
	function get_language_info($type = "", $lang_file = ""){
		$language_info = array();
		if($lang_file == ""){
			$lang_file = ABSPATH.LANGUAGES_PATH.SYSTEM_LANGUAGE.'.lang';
		}
		if(file_exists($lang_file)){
			$strings = file($lang_file);
			if(trim($strings[0]) === '[info]'){
				foreach ($strings as $string) {
					if(trim($string) === '[/info]') break;
					$str = explode(":", $string, 2);
					if(is_array($str) and isset($str[0]) and isset($str[1]) and !empty($str[0]) and !isset($language_info[$str[0]])){
						$language_info[$str[0]] = trim($str[1]);
					}
				}
				if(isset($language_info[$type]))
					return $language_info[$type];
				else
					return false;
			}
		}
		return false;
	}

	/**
	* Check string if it is from loaded language strings
	*
	* @package uCMS
	* @since 1.3
	* @version 1.3
	* @return bool
	*
	*/
	function is_language_string_id($string_id){
		if(isset($this->language_strings[$string_id])){
			return true;
		}
		return false;
	}

	/**
	* print out language string by its id
	*
	* @package uCMS
	* @since 1.3
	* @version 1.3
	* @return string if the second argument is true
	*
	*/
	function cout($string_id, $return = false){
		if($this->is_language_string_id($string_id)){
			$num_args = func_num_args();
			if($num_args > 2){
				$args = array_slice(func_get_args(), 2, $num_args);
				foreach ($args as $arg) {
					$patt[] = "/@s/";
				}
				$string = preg_replace($patt, $args, $this->language_strings[$string_id], 1);	
			}else{
				$string = $this->language_strings[$string_id];
			}
			if(!$return)
				echo $string;
			else
				return $string;
		}else{
			if(!$return)
				echo $string_id;
			else
				return $string_id;
		}
	}

	/**
	* Parse language string text
	*
	* @package uCMS
	* @since 1.3
	* @version 1.3
	* @return string
	*
	*/
	function parse_line($string){
		global $install;
		$patterns = array();
		$replacements = array();
		if(!isset($install)){
			$patterns[0] = '#(@SITE_DOMAIN@)#';
			$patterns[1] = '#(@UCMS_VERSION@)#';
			$replacements[0] = SITE_DOMAIN;
			$replacements[1] = UCMS_VERSION;
		}
		return preg_replace($patterns, $replacements, trim($string, "\n\t\r\0\x0B"));
	}

	/**
	* Alert message with selected type
	*
	* @package uCMS
	* @since 1.3
	* @version 1.3
	* @return string
	*
	*/
	function alert($type, $message){
		if($this->is_language_string_id($message))
			$message = $this->cout($message, true);
		switch ($type) {
			case 'error':
				echo "<div class=\"error\">$message</div>";
			break;

			case 'warning':
				echo "<div class=\"warning\">$message</div>";
			break;

			case 'success':
				echo "<div class=\"success\">$message</div>";
			break;

			default:
				return false;
			break;
		}
	}

	/**
	* Handler for PHP errors
	*
	* @package uCMS
	* @since 1.3
	* @version 1.3
	* @return nothing
	*
	*/
	static function error_handler($errno, $errstr, $errfile, $errline){
		if (!(error_reporting() & $errno)) {
   		    return false;
   		}
   		$die = false;
   		echo "<br>";
		switch ($errno) {
			case E_RECOVERABLE_ERROR:
				echo "<h3>PHP Catchable Fatal Error</h3>";
			break;
			
			case E_NOTICE:
				echo "<h3>PHP Notice</h3>";
			break;

			case E_WARNING:
				echo "<h3>PHP Warning</h3>";
			break;

			case E_ERROR:
				echo "<h3>PHP Fatal Error</h3>";
				$die = true;
			break;

			case E_PARSE:
				echo "<h3>PHP Parse Error</h3>";
				$die = true;
			break;

			case E_DEPRECATED:
				echo "<h3>PHP Deprecated Message</h3>";
			break;

			case E_STRICT:
				echo "<h3>PHP Strict Standars</h3>";
			break;

			default:
				echo "<h3>PHP Error $errno</h3>";
			break;
		}
		if(!UCMS_DEBUG){
			echo "<pre>";
			echo "$errstr in <b>$errfile</b> on line <b>$errline</b>";
			echo "</pre>";
		}else{
			echo "<pre>";
			echo "$errstr in <b>$errfile</b> on line <b>$errline</b><br>";
			echo '<p style="font-size: 8pt; padding: 10px;">';
			echo debug_print_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
			echo '</p>';
			echo "</pre>";
		}
		echo "<br>";
		if($die) die;
	}

	/**
	* Workaround to handle fatal PHP errors
	*
	* @package uCMS
	* @since 1.3
	* @version 1.3
	* @return nothing
	*
	*/
	static function fatal_error_handler(){
		$error = error_get_last();
		switch ($error['type']) {
			case E_ERROR: case E_PARSE:
				uCMS::error_handler($error["type"], $error["message"], $error["file"], $error["line"]);
			break;
		}
	}

	/**
	* Get an array with months names for those who don't know how to use date_format function
	*
	* @package uCMS
	* @since 1.3
	* @version 1.3
	* @return array
	*
	*/
	function get_months(){
		$months = array();
		$key = "";
		for ($i = 1; $i <= 12; $i++) {
			if($i < 10) $key = "0$i";
			else $key = "$i";
			$months[$key] = $this->date_format(MONTH_IN_SECONDS * $i,  "%B");
		}
		return $months;
	}

	/**
	* Load a template with an ability to replace it with provided by theme one
	*
	* @package uCMS
	* @since 1.3
	* @version 1.3
	* @return bool
	*
	*/
	function template($template_path, $theme_replace = true){
		global $ucms, $user, $udb, $pm, $url, $url_all, $action, $uc_tables, $uc_months, $theme, $plugin, $login, $widget, $event;
		$num_args = func_num_args();
		if($num_args > 2)
			$args = array_slice(func_get_args(), 2, $num_args);
		else $args = array();

		if($theme_replace){
			$file = explode("/", $template_path);
			$file = array_reverse($file);
			$file = $file[0];
			if(file_exists($theme->get_path().'forms/'.$file)){
				include $theme->get_path().'forms/'.$file;
				return true;
			}
		}
		
		if(file_exists($template_path)){
			include $template_path;
			return true;
		}else{
			$this->cout("ucms.template_not_found.message", false, $template_path);
		}
		return false;
	}

	function change_language($lang){
		global $udb;
		$this->language_strings = array();
		$this->set_language(get_module('path', 'users')."languages/$lang.lang");
		$this->set_language(get_module('path', 'posts')."languages/$lang.lang");

		if($this->is_language_string_id("module.posts.uncategorized.name")){
			$udb->query("UPDATE `".UC_PREFIX."categories` SET `name` = '".$this->cout("module.posts.uncategorized.name", true)."' WHERE `alias` = 'uncategorized'");
		}

		$groups = $udb->get_rows("SELECT `alias` FROM `".UC_PREFIX."groups` WHERE `id` < ".(DEFAULT_GROUPS_AMOUNT+1));

		for($i = 0; $i < DEFAULT_GROUPS_AMOUNT; $i++){
			if($this->is_language_string_id("module.users.group.".$groups[$i]['alias'].".name")){
				$udb->query("UPDATE `".UC_PREFIX."groups` SET `name` = '".$this->cout("module.users.group.".$groups[$i]['alias'].".name", true)."' WHERE `alias` = '".$groups[$i]['alias']."'");
			}
		}
	}
}
?>