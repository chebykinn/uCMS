<?php
class Settings{
	private static $list;

	public static function add($name, $value){
		$query = new Query('{settings}');
		$query->insert(array('name' => $name, 'value' => $value))->execute();
	}

	public static function load(){
		$query = new Query('{settings}');
		self::$list = $query->select(array('name', 'value'))->execute();
	} 

	public static function get($name){
		if( empty(self::$list) ) return false;
		foreach (self::$list as $setting) {
			if($setting['name'] === $name) return $setting['value'];
		}
		return "";
	}

	public static function set($name, $value){
	
	}

	public static function remove($name, $value){

	}
}
?>