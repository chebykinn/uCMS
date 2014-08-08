<?php
/**
 *
 * uCMS Plugins API
 * @package uCMS Plugins
 * @since uCMS 1.3
 * @version uCMS 1.3
 *
*/
class uPlugins {

	/**
	 *
	 * Get array of activated plugins
	 * @package uCMS
	 * @since uCMS 1.3
	 * @version uCMS 1.3
	 * @return array
	 *
	*/
	function get_activated_plugins(){
		return explode(",", ACTIVATED_PLUGINS) == array(0 => "") ? array() : explode(",", ACTIVATED_PLUGINS);
	}

	/**
	 *
	 * Get array of all installed plugins
	 * @package uCMS
	 * @since uCMS 1.3
	 * @version uCMS 1.3
	 * @return array
	 *
	*/
	function get_plugins($dir = ''){
		global $ucms;
		if($dir == "")
			$dir = ABSPATH.PLUGINS_PATH;
		if ($dh = opendir($dir)) {
			$i = 0;
			while (($wdir = readdir($dh)) !== false) {
				if($wdir !== '.' && $wdir !== '..' && filetype($dir.$wdir) == 'dir' && file_exists($dir.$wdir.'/plugininfo.txt')){
					$strings = file($dir.$wdir.'/plugininfo.txt');
					if(is_array($strings) and count($strings) >= EXT_PARAMS_NUM){
						$plugins[$i]['name'] = trim(preg_replace("/(Name|Название): /", "", $strings[0]));
						$plugins[$i]['version'] = trim(preg_replace("/(Version|Версия): /", "", $strings[1]));
						$plugins[$i]['author'] = trim(preg_replace("/(Author|Автор): /", "", $strings[2]));
						$plugins[$i]['site'] = trim(preg_replace("/(Site|Сайт): /", "", $strings[3]));
						$plugins[$i]['description'] = trim(preg_replace("/(Description|Описание): /", "", $strings[4]));
						$plugins[$i]['dir'] = $wdir;
						$plugins[$i]['timestamp'] = filemtime($dir.$wdir);
						if(in_array($plugins[$i]['dir'], $this->get_activated_plugins())){
							$plugins[$i]['activated'] = true;
						}else{
							$plugins[$i]['activated'] = false;
						}
						$plugin_path = ABSPATH.PLUGINS_PATH.$wdir;
						$ucms->set_language($plugin_path.'/languages/'.SYSTEM_LANGUAGE.'.lang');
						if($ucms->is_language_string_id("plugin.$wdir.name")){
							$plugins[$i]['local_name'] = $ucms->cout("plugin.$wdir.name", true);
						}else{
							$plugins[$i]['local_name'] = $plugins[$i]['name'];
						}
						if($ucms->is_language_string_id("plugin.$wdir.description")){
							$plugins[$i]['local_description'] = $ucms->cout("plugin.$wdir.description", true);
						}else{
							$plugins[$i]['local_description'] = $plugins[$i]['description'];
						}
						if(isset($strings[5]) and $strings[5] != ''){
							$plugins[$i]['updates_location'] = trim(preg_replace("/(Updates Location|Источник обновлений): /", "", $strings[5]));
						}else{
							$plugins[$i]['updates_location'] = '';
						}
						$i++;
					}
				}
			}
			if(isset($plugins)) return $plugins;
			else return array();
		}
	}

	/**
	 *
	 * Get amount of all installed plugins
	 * @package uCMS
	 * @since uCMS 1.3
	 * @version uCMS 1.3
	 * @return int
	 *
	*/
	function get_plugins_count(){
		$dir = ABSPATH.PLUGINS_PATH;
		if ($dh = opendir($dir)) {
			$i = 0;
			while (($wdir = readdir($dh)) !== false) {
				if($wdir !== '.' && $wdir !== '..'){
					if(filetype($dir.$wdir) == 'dir'){
						if(file_exists($dir.$wdir.'/plugininfo.txt')){
							$strings = file($dir.$wdir.'/plugininfo.txt');
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
	 * Get array of default plugins
	 * @package uCMS
	 * @since uCMS 1.3
	 * @version uCMS 1.3
	 * @return array
	 *
	*/
	function get_default_plugins(){
		return array(
			"captcha",
			"notifications"
		);
	}

	/**
	 *
	 * [INTERNAL] Run all activated plugins
	 * @package uCMS
	 * @since uCMS 1.3
	 * @version uCMS 1.3
	 * @return nothing
	 * @access private
	 *
	*/
	function deploy_activated(){
		global $ucms, $user, $udb, $pm, $url, $url_all, $action, $uc_tables, $uc_months, $theme, $event;
		$plugins = $this->get_activated_plugins();
		foreach ($plugins as $plugin) {
			$dir = ABSPATH.PLUGINS_PATH.$plugin;
			$info = $dir.'/plugininfo.txt';
			$index = $dir.'/index.php';
			if(is_dir($dir) and file_exists($info) and file_exists($index)){
				$ucms->set_language($dir.'/languages/'.SYSTEM_LANGUAGE.'.lang');
				include $index;
			} 
		}
	}

	/**
	 *
	 * Get $column info for plugin by its $plugin_mark
	 * @package uCMS
	 * @since uCMS 1.3
	 * @version uCMS 1.3
	 * @return string
	 *
	*/
	function get($column, $plugin_mark){
		global $udb, $ucms;
		$columns = array('name', 'version', 'author', 'site', 'description', 'dir', 'timestamp', 'local_name', 'local_description', 'updates_location', 'path');
		
		if(!in_array($column, $columns)){
			return false;
		}else{
			$plugin_path = ABSPATH.PLUGINS_PATH.$plugin_mark;
			$plugin_data = $plugin_path.'/plugininfo.txt';
			if(file_exists($plugin_data)){
				$strings = file($plugin_data);
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

							$ucms->set_language($plugin_path.'/languages/'.SYSTEM_LANGUAGE.'.lang');
							if($ucms->is_language_string_id("plugin.$plugin_mark.name")){
								return $ucms->cout("plugin.$plugin_mark.name", true);
							}
							return trim(preg_replace("/(Name|Название): /", "", $strings[0]));
						break;
						
						case 'local_description':
							$ucms->set_language($plugin_path.'/languages/'.SYSTEM_LANGUAGE.'.lang');
							if($ucms->is_language_string_id("plugin.$plugin_mark.description")){
								return $ucms->cout("plugin.$plugin_mark.description", true);
							}
							return trim(preg_replace("/(Description|Описание): /", "", $strings[4]));
						break;

						case 'updates_location':
							if(isset($strings[5]) and $strings[5] != ''){
								return trim(preg_replace("/(Updates Location|Источник обновлений): /", "", $strings[5]));
							}else return '';
						break;

						case 'path':
							return $plugin_path.'/';
						break;
					}
				}
			}
			return false;
		}
	}

	/**
	 *
	 * Check if the plugin is activated by its $plugin_mark
	 * @package uCMS
	 * @since uCMS 1.3
	 * @version uCMS 1.3
	 * @return bool
	 *
	*/
	function is_activated_plugin($plugin_mark){
		if($this->is_plugin($plugin_mark) and in_array($plugin_mark, $this->get_activated_plugins()))
			return true;
		return false;
	}

	/**
	 *
	 * Check if this $plugin_mark is actually an installed plugin
	 * @package uCMS
	 * @since uCMS 1.3
	 * @version uCMS 1.3
	 * @return bool
	 *
	*/
	function is_plugin($plugin_mark){
		if(!$plugin_mark){
			return false;
		}
		$dir = ABSPATH.PLUGINS_PATH.$plugin_mark;
		$info = $dir.'/plugininfo.txt';
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