<?php
namespace uCMS\Core;

use uCMS\Core\Database\Query;
use uCMS\Core\Extensions\Extension;
class Settings{
	private static $list;

	public static function Add($name, $value){
		/**
		* @todo owner
		*/
		$owner = Tools::GetCurrentOwner();
		$query = new Query('{settings}');
		$query->insert(array('name' => $name, 'value' => $value, 'owner' => $owner))->execute();
	}

	public static function Load(){
		$query = new Query('{settings}');
		self::$list = $query->select(array('name', 'value', 'owner'))->execute();
	} 

	public static function IsExists($name){
		if( empty(self::$list) ) return "";
		foreach (self::$list as $setting) {
			if($setting['name'] === $name) return true;
		}
		return false;
	}

	public static function Get($name){
		if( empty(self::$list) ) return "";
		foreach (self::$list as $setting) {
			if($setting['name'] === $name) return $setting['value'];
		}
		return "";
	}

	public static function GetOwner($name){
		if( empty(self::$list) ) return "";
		foreach (self::$list as $setting) {
			if($setting['name'] === $name) return $setting['owner'];
		}
		return "";
	}

	public static function Update($name, $value){
		$owner = Tools::GetCurrentOwner();
		$checkName = self::IsExists($name);
		if( $checkName && self::GetOwner($name) == $owner ){
			$set = new Query('{settings}');
			$set->update(array('value' => $value))
			    ->where()->condition('name', '=', $name)->execute();
			return true; // query result
		}
		return false;
	}

	public static function Remove($name, $value){
		/**
		* @todo owner
		*/
	}
}
?>