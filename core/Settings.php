<?php
namespace uCMS\Core;

use uCMS\Core\Database\Query;
use uCMS\Core\Extensions\Extension;
class Settings{
	private static $list;

	public static function Add($name, $value, $public = false){
		$owner = !$public ? Tools::GetCurrentOwner() : "";
		$query = new Query('{settings}');
		$query->insert(array('name' => $name, 'value' => $value, 'owner' => $owner), true)->execute();
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

	public static function IsOwner($name){
		$owner = Tools::GetCurrentOwner();
		$checkName = self::IsExists($name);
		if( $checkName ){
			$settingOwner = self::GetOwner($name);
			return (empty($settingOwner) || $settingOwner === $owner);
		}
		return false;
	}

	public static function Update($name, $value){
		if( self::IsOwner($name) ){
			$set = new Query('{settings}');
			$set->update(array('value' => $value))
			    ->where()->condition('name', '=', $name)->execute();
			return true; // query result
		}
		return false;
	}

	public static function Increment($name){
		if( self::IsOwner($name) ){
			$set = new Query('UPDATE {settings} SET value = value + 1 WHERE name = :name', array(':name' => $name));
			$set->execute();
			return true;
		}
		return false;
	}

	public static function Decrement($name){
		if( self::IsOwner($name) ){
			$set = new Query('UPDATE {settings} SET value = value - 1 WHERE name = :name', array(':name' => $name));
			$set->execute();
			return true;
		}
		return false;
	}

	public static function Rename($name, $newName){
		if( self::IsOwner($name) ){
			$rename = new Query("{settings}");
			$rename->update(array('name' => $newName))->where()->condition('name', '=', $name)->execute();
			return true;
		}
		return false;
	}

	public static function Remove($name){
		if( self::IsOwner($name) ){
			$delete = new Query("{settings}");
			$delete->delete()->where()->condition('name', '=', $name)->execute();
			return true;
		}
		return false;
	}
}
?>