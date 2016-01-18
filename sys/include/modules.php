<?php
/**
 *
 * uCMS Modules API
 * @package uCMS
 * @since uCMS 1.3
 * @version uCMS 1.3
 *
*/

function modules_priority_sort($a, $b){
	if ($a['load_priority'] == $b['load_priority']) {
        return 0;
    }
    return ($a['load_priority'] > $b['load_priority']) ? -1 : 1;
}

/**
 *
 * Get modules array
 * @package uCMS
 * @since uCMS 1.3
 * @version uCMS 1.3
 * @return array
 *
*/
function get_modules($dir = ''){
	global $ucms;
	if($dir == "")
		$dir = ABSPATH.MODULES_PATH;
	if ($dh = opendir($dir)) {
		$i = 0;
		while (($wdir = readdir($dh)) !== false) {
			if($wdir !== '.' && $wdir !== '..' && filetype($dir.$wdir) == 'dir' && file_exists($dir.$wdir.'/moduleinfo.txt')){
				$strings = file($dir.$wdir.'/moduleinfo.txt');
				if(is_array($strings) and count($strings) >= EXT_PARAMS_NUM){
					$modules[$i]['name'] = trim(preg_replace("/(Name|Название): /", "", $strings[0]));
					$modules[$i]['version'] = trim(preg_replace("/(Version|Версия): /", "", $strings[1]));
					$modules[$i]['author'] = trim(preg_replace("/(Author|Автор): /", "", $strings[2]));
					$modules[$i]['site'] = trim(preg_replace("/(Site|Сайт): /", "", $strings[3]));
					$modules[$i]['description'] = trim(preg_replace("/(Description|Описание): /", "", $strings[4]));
					$modules[$i]['dir'] = $wdir;
					$modules[$i]['timestamp'] = filemtime($dir.$wdir);
					if(in_array($modules[$i]['dir'], get_activated_modules())){
						$modules[$i]['activated'] = true;
					}else{
						$modules[$i]['activated'] = false;
					}
					$module_path = ABSPATH.MODULES_PATH.$wdir;
					$ucms->set_language($module_path.'/languages/'.SYSTEM_LANGUAGE.'.lang');
					if($ucms->is_language_string_id("module.$wdir.name")){
						$modules[$i]['local_name'] = $ucms->cout("module.$wdir.name", true);
					}else{
						$modules[$i]['local_name'] = $modules[$i]['name'];
					}
					if($ucms->is_language_string_id("module.$wdir.description")){
						$modules[$i]['local_description'] = $ucms->cout("module.$wdir.description", true);
					}else{
						$modules[$i]['local_description'] = $modules[$i]['description'];
					}
					if(isset($strings[5]) and $strings[5] != ''){
						$modules[$i]['updates_location'] = trim(preg_replace("/(Updates Location|Источник обновлений): /", "", $strings[5]));
					}else{
						$modules[$i]['updates_location'] = '';
					}
					if(isset($strings[6]) and $strings[6] != ''){
						$modules[$i]['load_priority'] = (int) trim(preg_replace("/(Load Priority|Приоритет загрузки): /", "", $strings[6]));
					}else{
						$modules[$i]['load_priority'] = 0;
					}
					$i++;
				}
			}
		}
		if(isset($modules)){
			usort($modules, "modules_priority_sort");
			return $modules;
		}
	}
	return false;
}

/**
 *
 * Get the number of modules including system ones
 * @package uCMS
 * @since uCMS 1.3
 * @version uCMS 1.3
 * @return int
 *
*/
function get_modules_count(){
	$dir = ABSPATH.MODULES_PATH;
	if ($dh = opendir($dir)) {
		$i = 0;
		while (($wdir = readdir($dh)) !== false) {
			if($wdir !== '.' && $wdir !== '..' && filetype($dir.$wdir) == 'dir' && file_exists($dir.$wdir.'/moduleinfo.txt')){
				$strings = file($dir.$wdir.'/moduleinfo.txt');
				if(is_array($strings) and count($strings) >= EXT_PARAMS_NUM){
					$i++;
				}
			}
		}
		return $i;
	}
	return false;
}

/**
 *
 * Check if given $module_mark actually is a module
 * @package uCMS
 * @since uCMS 1.3
 * @version uCMS 1.3
 * @return bool
 *
*/
function is_module($module_mark){
	if(!$module_mark){
		return false;
	}
	$dir = ABSPATH.MODULES_PATH.$module_mark;
	$info = $dir.'/moduleinfo.txt';
	if(is_dir($dir) and file_exists($info)){
		$strings = file($info);
		if(is_array($strings) and count($strings) >= EXT_PARAMS_NUM){
			return true;
		}
	}
	return false;
}

/**
 *
 * Get by given $module_mark some module data
 * @package uCMS
 * @since uCMS 1.3
 * @version uCMS 1.3
 * @return string
 *
*/
function get_module($column, $module_mark){
	global $udb, $ucms;
	$columns = array('name',
	 				 'version',
	 				 'author',
	 				 'site',
	 				 'description',
	 				 'dir',
	 				 'sort',
	 				 'timestamp',
	 				 'local_name',
	 				 'local_description',
	 				 'id',
	 				 'updates_location',
	 				 'path',
	 				 'load_priority');
	if(!in_array($column, $columns)){
		return false;
	}else{
		$module_path = ABSPATH.MODULES_PATH.$module_mark;
		$module_file = $module_path.'/index.php';
		$module_data = $module_path.'/moduleinfo.txt';
		if(is_module($module_mark)){
			$strings = file($module_data);
			switch ($column) {
				case 'name':
					return trim(preg_replace("/(Name|Название): /", "", $strings[0]));
				break;

				case 'version':
					return trim(preg_replace("/(Version|Версия): /", "", $strings[1]));
				break;

				case 'author':
					return trim(preg_replace("/(Author|Автор): /", "", $strings[2]));
				break;

				case 'site':
					return trim(preg_replace("/(Site|Сайт): /", "", $strings[3]));
				break;

				case 'description':
					return trim(preg_replace("/(Description|Описание): /", "", $strings[4]));
				break;

				case 'timestamp':
					return filemtime($dir.$wdir);
				break;

				case 'local_name':
					$ucms->set_language($module_path.'/languages/'.SYSTEM_LANGUAGE.'.lang');
					if($ucms->is_language_string_id("module.$module_mark.name")){
						return $ucms->cout("module.$module_mark.name", true);
					}
					return trim(preg_replace("/(Name|Название): /", "", $strings[0]));
				break;
				
				case 'local_description':
					$ucms->set_language($module_path.'/languages/'.SYSTEM_LANGUAGE.'.lang');
					if($ucms->is_language_string_id("module.$module_mark.description")){
						return $ucms->cout("module.$module_mark.description", true);
					}
					return trim(preg_replace("/(Description|Описание): /", "", $strings[4]));
				break;

				case 'updates_location':
					if(isset($strings[5]) and $strings[5] != ''){
						return trim(preg_replace("/(Updates Location|Источник обновлений): /", "", $strings[5]));
					}else return '';
				break;

				case 'load_priority':
					if(isset($strings[6]) and $strings[6] != ''){
						return (int) trim(preg_replace("/(Load Priority|Приоритет загрузки): /", "", $strings[5]));
					}else return '';
				break;

				case 'path':
					return $module_path.'/';
				break;
			}
		}
		return false;
	}
}

/**
 *
 * Check if given by $module_mark module is activated
 * @package uCMS
 * @since uCMS 1.3
 * @version uCMS 1.3
 * @return bool
 *
*/
function is_activated_module($module_mark){
	if(is_module($module_mark) and in_array($module_mark, get_activated_modules()))
		return true;
	return false;
}

/**
 *
 * Get array of all activated modules
 * @package uCMS
 * @since uCMS 1.3
 * @version uCMS 1.3
 * @return array
 *
*/
function get_activated_modules(){
	return explode(",", ACTIVATED_MODULES) == array(0 => "") ? array() : explode(",", ACTIVATED_MODULES);
}

/**
 *
 * Get array of all default modules
 * @package uCMS
 * @since uCMS 1.3
 * @version uCMS 1.3
 * @return array
 *
*/
function get_default_modules(){
	return array(
		0 => "posts",
		1 => "comments",
		2 => "pages",
		3 => "users",
		4 => "themes",
		5 => "widgets",
		6 => "links",
		7 => "plugins",
		8 => "fileman",
		9 => "search"
	);
}

/**
 *
 * Attach title for page shown for given $action, you can use language string and you should use $args if you adding complex language string
 * @package uCMS
 * @since uCMS 1.3
 * @version uCMS 1.3
 * @return bool
 *
*/
function add_title($action, $title, $args = array()){
	global $titles, $ucms;
	if($ucms->is_language_string_id($title)){
		$args = array_merge(array($title, true), $args);
		$title = call_user_func_array(array($ucms, "cout"), $args);
	}
	if($action != ""){
		$titles[$action] = $title;
		return true;
	}else return false;
}

/**
 *
 * Attach URL action to your module at given $file
 * @package uCMS
 * @since uCMS 1.3
 * @version uCMS 1.3
 * @return nothing
 *
*/
function add_url_action($action, $exec_dir, $file = '', $require_param = false){
	global $url_actions, $action_exec_dirs, $include_files, $require_params;
	if(in_array($action, $url_actions)){
		$key = array_search($action, $url_actions);
	}else{
		$key = count($url_actions);
	}
	$url_actions[$key] = $action;
	$action_exec_dirs[$key] = $exec_dir;
	$require_params[$key] = $require_param;
	if($file != ''){
		$include_files[$key] = $file;
	}else{
		$include_files[$key] = '';
	}
}

/**
 *
 * Add settings button to admin sidebar 
 * @package uCMS
 * @since uCMS 1.3
 * @version uCMS 1.3
 * @return nothing
 *
*/
function add_settings_item($name, $file, $accessID, $accessLVL = 1, $order = "", $args = array()){
	global $settings_links, $modules, $ucms;
	if($ucms->is_language_string_id($name)){
		$args = array_merge(array($name, true), $args);
		$name = call_user_func_array(array($ucms, "cout"), $args);
	}
	if($modules){
		for ($i = 0; $i < count($modules); $i++) { 
			if($file == $modules[$i]['dir']){
				$file = UCMS_DIR."/admin/settings.php?module=".$modules[$i]['dir'];
				break;
			}
		}
	}
	$pos = count($settings_links);
	if($order === "")
		$order = $pos+1;
	$settings_links[$pos]['name'] = '@'.$name;
	$settings_links[$pos]['file'] = $file;
	$settings_links[$pos]['accessID'] = $accessID;
	$settings_links[$pos]['accessLVL'] = $accessLVL;
	$settings_links[$pos]['order'] = $order;
}

/**
 *
 * Add module button to admin sidebar 
 * @package uCMS
 * @since uCMS 1.3
 * @version uCMS 1.3
 * @return nothing
 *
*/
function add_sidebar_item($name, $file, $accessID, $accessLVL, $delimeter = false, $order = "", $url_params = '', $args = array()){
	global $links, $modules, $ucms;
	if($ucms->is_language_string_id($name)){
		$args = array_merge(array($name, true), $args);
		$name = call_user_func_array(array($ucms, "cout"), $args);
	}
	$pos = count($links);
	if($order === "")
		$order = $pos+1;

	if($modules){
		for ($i = 0; $i < count($modules); $i++) { 
			if($file == $modules[$i]['dir']){
				$file = UCMS_DIR."/admin/manage.php?module=".$modules[$i]['dir'];
				break;
			}
		}
	}
	$links[$pos]["name"] = $name;
	$links[$pos]["file"] = $file.$url_params;
	$links[$pos]["accessID"] = $accessID;
	$links[$pos]["accessLVL"] = $accessLVL;
	$links[$pos]["order"] = $order;
	$links[$pos]["delimeter"] = $delimeter;
}

/**
 *
 * Check if given $action is in current URL
 * @package uCMS
 * @since uCMS 1.3
 * @version uCMS 1.3
 * @return bool
 *
*/
function in_url($action){
	global $url_all;
	if(in_array($action, $url_all)){
		return true;
	}
	return false;
}

/**
 *
 * Check if given $action is in current URL and get its param, if it is not in URL then function returns $default
 * @package uCMS
 * @since uCMS 1.3
 * @version uCMS 1.3
 * @return string
 *
*/
function get_url_action_value($action, $default){
	global $url_all;
	if(in_url($action)){
		$key = array_search($action, $url_all);
		if(!empty($url_all[$key+1])){
			return $url_all[$key+1];
		}
	}
	return $default;
}

/**
 *
 * Check if URL array contains given $key (array like SITE_DOMAIN/1/2/3/.../10 etc)
 * @package uCMS
 * @since uCMS 1.3
 * @version uCMS 1.3
 * @return bool
 *
*/
function is_url_key($key){
	global $url_all;
	if(!empty($url_all[$key])){
		return true;
	}
	return false;
}

/**
 *
 * Check if URL array contains given $key and get its param, if it is not in URL then function returns $default
 * @package uCMS
 * @since uCMS 1.3
 * @version uCMS 1.3
 * @return string
 *
*/
function get_url_key_value($key, $default){
	global $url_all;
	if(is_url_key($key)){
		return $url_all[$key];
	}
	return $default;
}

/**
 *
 * Get page from URL
 * @package uCMS
 * @since uCMS 1.3
 * @version uCMS 1.3
 * @return int
 *
*/
function get_current_page(){
	$page = NICE_LINKS ? get_url_action_value('page', 1) : (isset($_GET['page']) ? $_GET['page'] : 1);
	$page = intval($page);
	if( $page <= 0 ) $page = 1;
	return $page;
}

/**
 *
 * Get current URL. You could exclude get parameters from this url.
 * @package uCMS
 * @since uCMS 1.3
 * @version uCMS 1.3
 * @return string
 *
*/
function get_current_url(){
	$exclude_get = func_get_args();
	$url = $_SERVER['PHP_SELF'];
	$c = 0;
	foreach ($_GET as $action => $value) {
		if(!in_array($action, $exclude_get)){
			$d = $c == 0 ? "?" : "&"; 

			$url .= $d.$action.'='.$value;
			$c++;
		}
	}
	return $url;
}
?>
