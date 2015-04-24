<?php
class Settings{
	private static $list;

	public static function add($name, $value){
		/**
		* @todo owner
		*/
		$owner = self::getCurrentOwner();
		$query = new Query('{settings}');
		$query->insert(array('name' => $name, 'value' => $value, 'owner' => $owner))->execute();
	}

	public static function load(){
		$query = new Query('{settings}');
		self::$list = $query->select(array('name', 'value'))->execute();
	} 

	public static function get($name){
		if( empty(self::$list) ) return "";
		foreach (self::$list as $setting) {
			if($setting['name'] === $name) return $setting['value'];
		}
		return "";
	}

	public static function getOwner($name){
		if( empty(self::$list) ) return "";
		foreach (self::$list as $setting) {
			if($setting['name'] === $name) return $setting['owner'];
		}
		return "";
	}

	public static function getCurrentOwner(){
		// varDump(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS));
		$name = "core";
		$trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
		foreach ($trace as $level) {
			foreach ($level as $key => $value) {
				$found = false;
				if($key == 'file' && is_file($value) ){

					if( mb_strpos($value, EXTENSIONS_PATH) !== false ){
						$name = str_replace(EXTENSIONS_PATH, '', dirname($value));
						if( Extensions::isLoaded($name) ){
							$found = true;
						}
					}

					if( mb_strpos($value, THEMES_PATH) !== false ){
						/**
						* @todo themes, widgets ?
						*/
					}
				}
				if($found) break;
			}
		}
		return $name;
	}

	public static function set($name, $value){
		//echo self::getCurrentOwner();
		/**
		* @todo owner
		*/
	}

	public static function remove($name, $value){
		/**
		* @todo owner
		*/
	}
}
?>