<?php
/**
 *
 * uCMS Themes API
 * @package uCMS Themes
 * @since uCMS 1.3
 * @version uCMS 1.3
 *
*/
class uThemes{
	/**
	 *
	 * Get array of all installed themes
	 * @package uCMS
	 * @since uCMS 1.3
	 * @version uCMS 1.3
	 * @return array
	 *
	*/
	function get_themes($dir = ""){
		global $ucms;
		if($dir == "")
			$dir = ABSPATH.UC_THEMES_PATH;
		if ($dh = opendir($dir)) {
			$i = 0;
			while (($wdir = readdir($dh)) !== false) {
				if($wdir !== '.' && $wdir !== '..' && filetype($dir.$wdir) == 'dir' && file_exists($dir.$wdir.'/themeinfo.txt')){
					$strings = file($dir.$wdir.'/themeinfo.txt');
					if(is_array($strings) and count($strings) >= EXT_PARAMS_NUM){
						$themes[$i]['name'] = trim(preg_replace("/(Name|Название): /", "", $strings[0]));
						$themes[$i]['version'] = trim(preg_replace("/(Version|Версия): /", "", $strings[1]));
						$themes[$i]['author'] = trim(preg_replace("/(Author|Автор): /", "", $strings[2]));
						$themes[$i]['site'] = trim(preg_replace("/(Site|Сайт): /", "", $strings[3]));
						$themes[$i]['description'] = trim(preg_replace("/(Description|Описание): /", "", $strings[4]));
						$themes[$i]['dir'] = $wdir;
						$themes[$i]['timestamp'] = filemtime($dir.$wdir);
						if($themes[$i]['dir'] == THEMEDIR){
							$themes[$i]['activated'] = true;
						}else{
							$themes[$i]['activated'] = false;
						}
						$theme_path = ABSPATH.UC_THEMES_PATH.$wdir;
						$ucms->set_language($theme_path.'/languages/'.SYSTEM_LANGUAGE.'.lang');
						if($ucms->is_language_string_id("theme.$wdir.name")){
							$themes[$i]['local_name'] = $ucms->cout("theme.$wdir.name", true);
						}else{
							$themes[$i]['local_name'] = $themes[$i]['name'];
						}
						if($ucms->is_language_string_id("theme.$wdir.description")){
							$themes[$i]['local_description'] = $ucms->cout("theme.$wdir.description", true);
						}else{
							$themes[$i]['local_description'] = $themes[$i]['description'];
						}
						if(isset($strings[5]) and $strings[5] != ''){
							$themes[$i]['updates_location'] = trim(preg_replace("/(Updates Location|Источник обновлений): /", "", $strings[5]));
						}else{
							$themes[$i]['updates_location'] = '';
						}
						$i++;
					}
				}
			}
			if(isset($themes)) return $themes;
			else return false;
		}
	}

	/**
	 *
	 * Get directory of current theme
	 * @package uCMS
	 * @since uCMS 1.3
	 * @version uCMS 1.3
	 * @return string
	 *
	*/
	function get_dir(){
		return THEMEDIR;
	}

	/**
	 *
	 * Get path to the current theme's directory
	 * @package uCMS
	 * @since uCMS 1.3
	 * @version uCMS 1.3
	 * @return string
	 *
	*/
	function get_path($url = false){
		if($url) $prefix = UCMS_DIR.'/';
		else $prefix = "";

		if(isset($_SESSION['theme']) and is_dir(UC_THEMES_PATH.$_SESSION['theme']))
			return $prefix.UC_THEMES_PATH.$_SESSION['theme'].'/';
		return $prefix.THEMEPATH;
	}

	/**
	 *
	 * Get amount of all installed themes
	 * @package uCMS
	 * @since uCMS 1.3
	 * @version uCMS 1.3
	 * @return int
	 *
	*/
	function get_themes_count(){
		$dir = ABSPATH.UC_THEMES_PATH;
		if ($dh = opendir($dir)) {
			$i = 0;
			while (($wdir = readdir($dh)) !== false) {
				if($wdir !== '.' && $wdir !== '..'){
					if(filetype($dir.$wdir) == 'dir'){
						if(file_exists($dir.$wdir.'/themeinfo.txt')){
							$strings = file($dir.$wdir.'/themeinfo.txt');
							if(is_array($strings) and count($strings) == EXT_PARAMS_NUM){
								$i++;
							}
						}
					}
				}
			}
			return $i;
		}
	}

	/**
	 *
	 * Get $column info for theme by its $theme_mark
	 * @package uCMS
	 * @since uCMS 1.3
	 * @version uCMS 1.3
	 * @return string
	 *
	*/
	function get($column, $theme_mark){
		global $udb, $ucms;
		$columns = array('name', 'version', 'author', 'site', 'description', 'dir', 'timestamp', 'local_name', 'local_description', 'updates_location', 'path');
		if(!in_array($column, $columns)){
			return false;
		}else{
			$theme_path = ABSPATH.UC_THEMES_PATH.$theme_mark;
			$theme_file = $theme_path.'/index.php';
			$theme_data = $theme_path.'/themeinfo.txt';
			if(file_exists($theme_file) and file_exists($theme_data)){
				$strings = file($theme_data);
				if(is_array($strings) and count($strings) >= EXT_PARAMS_NUM){
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

						case 'local_name':
							$ucms->set_language($theme_path.'/languages/'.SYSTEM_LANGUAGE.'.lang');
							if($ucms->is_language_string_id("theme.$theme_mark.name")){
								return $ucms->cout("theme.$theme_mark.name", true);
							}
							if(trim(preg_replace("/(Name|Название): /", "", $strings[0])) != "")
								return trim(preg_replace("/(Name|Название): /", "", $strings[0]));
							else return $theme_mark;
						break;
						
						case 'local_description':
							$ucms->set_language($theme_path.'/languages/'.SYSTEM_LANGUAGE.'.lang');
							if($ucms->is_language_string_id("theme.$theme_mark.description")){
								return $ucms->cout("theme.$theme_mark.description", true);
							}
							return trim(preg_replace("/(Description|Описание): /", "", $strings[4]));
						break;

						case 'updates_location':
							if(isset($strings[5]) and $strings[5] != '')
								return trim(preg_replace("/(Updates Location|Источник обновлений): /", "", $strings[5]));
							else return '';
						break;

						case 'path':

						
							return $theme_path.'/';
						break;
					}
				}
			}
			return false;
		}
	}

	/**
	 *
	 * Check if tryout mode enabled on the site
	 * @package uCMS
	 * @since uCMS 1.3
	 * @version uCMS 1.3
	 * @return bool
	 *
	*/
	function tryout_mode_check(){
		global $ucms;
		if(isset($_SESSION['theme'])){
			return true;
		}
		return false;
	}

	/**
	 *
	 * Check if the theme is activated by its $theme_mark
	 * @package uCMS
	 * @since uCMS 1.3
	 * @version uCMS 1.3
	 * @return bool
	 *
	*/
	function is_activated_theme($theme_mark){
		if($this->is_theme($theme_mark) and in_array($theme_mark, get_activated_themes()))
			return true;
		return false;
	}

	/**
	 *
	 * Check if this $theme_mark is actually an installed theme
	 * @package uCMS
	 * @since uCMS 1.3
	 * @version uCMS 1.3
	 * @return bool
	 *
	*/
	function is_theme($theme_mark){
		if(!$theme_mark){
			return false;
		}
		$dir = ABSPATH.UC_THEMES_PATH.$theme_mark;
		$info = $dir.'/themeinfo.txt';
		if(is_dir($dir) and file_exists($info)){
			$strings = file($info);
			if(is_array($strings) and count($strings) >= EXT_PARAMS_NUM){
				return true;
			}
		}
		return false;
	}
}
?>