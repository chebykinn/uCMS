<?php
/**
 *
 * uCMS Widgets API
 * @package uCMS Widgets
 * @since uCMS 1.3
 * @version uCMS 1.3
 *
*/
class uWidgets{

	/**
	 *
	 * Get array of activated widgets
	 * @package uCMS
	 * @since uCMS 1.3
	 * @version uCMS 1.3
	 * @return array
	 *
	*/
	function get_activated_widgets(){
		return explode(",", ACTIVATED_WIDGETS) == array(0 => "") ? array() : explode(",", ACTIVATED_WIDGETS);
	}

	/**
	 *
	 * Get array of all installed widgets
	 * @package uCMS
	 * @since uCMS 1.3
	 * @version uCMS 1.3
	 * @return array
	 *
	*/
	function get_widgets($dir = ''){
		global $ucms;
		if($dir == "")
			$dir = ABSPATH.WIDGETS_PATH;
		if ($dh = opendir($dir)) {
			$i = 0;
			while (($wdir = readdir($dh)) !== false) {
				if($wdir !== '.' && $wdir !== '..' && filetype($dir.$wdir) == 'dir' && file_exists($dir.$wdir.'/widgetinfo.txt')){
					$strings = file($dir.$wdir.'/widgetinfo.txt');
					if(is_array($strings) and count($strings) >= EXT_PARAMS_NUM){
						$widgets[$i]['name'] = trim(preg_replace("/(Name|Название): /", "", $strings[0]));
						$widgets[$i]['version'] = trim(preg_replace("/(Version|Версия): /", "", $strings[1]));
						$widgets[$i]['author'] = trim(preg_replace("/(Author|Автор): /", "", $strings[2]));
						$widgets[$i]['site'] = trim(preg_replace("/(Site|Сайт): /", "", $strings[3]));
						$widgets[$i]['description'] = trim(preg_replace("/(Description|Описание): /", "", $strings[4]));
						$widgets[$i]['dir'] = $wdir;
						$widgets[$i]['timestamp'] = filemtime($dir.$wdir);
						if(in_array($widgets[$i]['dir'], $this->get_activated_widgets())){
							$widgets[$i]['activated'] = true;
						}else{
							$widgets[$i]['activated'] = false;
						}
						$widget_path = ABSPATH.WIDGETS_PATH.$wdir;
						$ucms->set_language($widget_path.'/languages/'.SYSTEM_LANGUAGE.'.lang');
						if($ucms->is_language_string_id("widget.$wdir.name")){
							$widgets[$i]['local_name'] = $ucms->cout("widget.$wdir.name", true);
						}else{
							$widgets[$i]['local_name'] = $widgets[$i]['name'];
						}
						if($ucms->is_language_string_id("widget.$wdir.description")){
							$widgets[$i]['local_description'] = $ucms->cout("widget.$wdir.description", true);
						}else{
							$widgets[$i]['local_description'] = $widgets[$i]['description'];
						}
						if(isset($strings[5]) and $strings[5] != ''){
							$widgets[$i]['updates_location'] = trim(preg_replace("/(Updates Location|Источник обновлений): /", "", $strings[5]));
						}else{
							$widgets[$i]['updates_location'] = '';
						}
						$i++;
					}
				}
			}
			if(isset($widgets)) return $widgets;
			else return false;
		}
	}

	/**
	 *
	 * Get amount of all installed widgets
	 * @package uCMS
	 * @since uCMS 1.3
	 * @version uCMS 1.3
	 * @return int
	 *
	*/
	function get_widgets_count(){
		$dir = ABSPATH.WIDGETS_PATH;
		if ($dh = opendir($dir)) {
			$i = 0;
			while (($wdir = readdir($dh)) !== false) {
				if($wdir !== '.' && $wdir !== '..'){
					if(filetype($dir.$wdir) == 'dir'){
						if(file_exists($dir.$wdir.'/widgetinfo.txt')){
							$strings = file($dir.$wdir.'/widgetinfo.txt');
							if(is_array($strings) and count($strings) >= EXT_PARAMS_NUM){
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
	 * Get array of default widgets
	 * @package uCMS
	 * @since uCMS 1.3
	 * @version uCMS 1.3
	 * @return array
	 *
	*/
	function get_default_widgets(){
		return array(
			0 => "post_categories",
			1 => "new_comments",
			2 => "site_links",
			3 => "site_stats",
			4 => "search_form",
			5 => "user_menu",
			6 => "post_tags",
			7 => "sysinfo",
			8 => "post_archives",
			9 => "new_materials",
			10 => "menu_links"
		);
	}

	/**
	 *
	 * Get $column info for widget by its $widget_mark
	 * @package uCMS
	 * @since uCMS 1.3
	 * @version uCMS 1.3
	 * @return string
	 *
	*/
	function get($column, $widget_mark){
		global $udb, $ucms;
		$columns = array('name', 'version', 'author', 'site', 'description', 'dir', 'timestamp', 'local_name', 'local_description', 'updates_location', 'path');
		if(!in_array($column, $columns)){
			return false;
		}else{
			$widget_path = ABSPATH.WIDGETS_PATH.$widget_mark;
			$widget_file = $widget_path.'/index.php';
			$widget_data = $widget_path.'/widgetinfo.txt';
			if(file_exists($widget_file) and file_exists($widget_data)){
				$strings = file($widget_data);
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
							$ucms->set_language($widget_path.'/languages/'.SYSTEM_LANGUAGE.'.lang');
							if($ucms->is_language_string_id("widget.$widget_mark.name")){
								return $ucms->cout("widget.$widget_mark.name", true);
							}
							return trim(preg_replace("/(Name|Название): /", "", $strings[0]));
						break;
						
						case 'local_description':
							$ucms->set_language($widget_path.'/languages/'.SYSTEM_LANGUAGE.'.lang');
							if($ucms->is_language_string_id("widget.$widget_mark.description")){
								return $ucms->cout("widget.$widget_mark.description", true);
							}
							return trim(preg_replace("/(Description|Описание): /", "", $strings[4]));
						break;

						case 'updates_location':
							if(isset($strings[5]) and $strings[5] != ''){
								return trim(preg_replace("/(Updates Location|Источник обновлений): /", "", $strings[5]));
							}else return '';
						break;

						case 'path':
							return $widget_path.'/';
						break;
					}
				}
			}
			return false;
		}
	}


	/**
	 *
	 * Load widget data by given $widget_mark
	 * @package uCMS
	 * @since uCMS 1.2
	 * @version uCMS 1.3
	 * @return bool
	 *
	*/
	function load($widget_mark){
		global $ucms, $user, $udb, $pm, $url, $url_all, $action, $uc_tables, $uc_months, $theme, $plugin, $login;

		if($this->is_widget($widget_mark) and file_exists($this->get('path', $widget_mark).'index.php') and in_array($widget_mark, $this->get_activated_widgets())){
			$ucms->set_language($this->get('path', $widget_mark).'/languages/'.SYSTEM_LANGUAGE.'.lang');
			include $this->get('path', $widget_mark).'index.php';
			return true;
		}else{
			$ucms->cout("module.widgets.load_error.label");
			return false;
		}
	}

	/**
	 *
	 * Check if the widget is activated by its $widget_mark
	 * @package uCMS
	 * @since uCMS 1.3
	 * @version uCMS 1.3
	 * @return bool
	 *
	*/
	function is_activated_widget($widget_mark){
		if($this->is_widget($widget_mark) and in_array($widget_mark, $this->get_activated_widgets()))
			return true;
		return false;
	}

	/**
	 *
	 * Check if this $widget_mark is actually an installed widget
	 * @package uCMS
	 * @since uCMS 1.3
	 * @version uCMS 1.3
	 * @return bool
	 *
	*/
	function is_widget($widget_mark){
		if(!$widget_mark){
			return false;
		}
		$dir = ABSPATH.WIDGETS_PATH.$widget_mark;
		$info = $dir.'/widgetinfo.txt';
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