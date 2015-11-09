<?php
namespace uCMS\Core;

use uCMS\Core\Database\Query;
use uCMS\Core\Extensions\Extension;
class Settings{
	private static $list = array();

	public static function Add($name, $value, $public = false){
		$owner = !$public ? Tools::GetCurrentOwner() : "";
		$query = new Query('{settings}');
		$query->insert(array('name' => $name, 'value' => $value, 'owner' => $owner), true)->execute();
		self::$list[$name] = array('name' => $name, 'value' => Tools::PrepareSQL($value), 'owner' => $owner);
	}

	public static function Load(){
		$query = new Query('{settings}');
		$list = $query->select(array('name', 'value', 'owner'))->execute();
		foreach ($list as $setting) {
			self::$list[$setting['name']] = $setting;
		}
	} 

	public static function IsExists($name){
		if( empty(self::$list) ) return "";
		return isset(self::$list[$name]);
		return false;
	}

	public static function Get($name){
		if( empty(self::$list) ) return "";
		if( isset(self::$list[$name]) ){
			return self::$list[$name]['value'];
		}
		return "";
	}

	public static function GetOwner($name){
		if( empty(self::$list) ) return "";
		if( isset(self::$list[$name]) ){
			return self::$list[$name]['owner'];
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
			self::$list[$name]['value'] = Tools::PrepareSQL($value);
			return true; // query result
		}
		return false;
	}

	public static function Increment($name){
		if( self::IsOwner($name) ){
			$set = new Query('UPDATE {settings} SET value = value + 1 WHERE name = :name', array(':name' => $name));
			$set->execute();
			self::$list[$name]['value']++;
			return true;
		}
		return false;
	}

	public static function Decrement($name){
		if( self::IsOwner($name) ){
			$set = new Query('UPDATE {settings} SET value = value - 1 WHERE name = :name', array(':name' => $name));
			$set->execute();
			self::$list[$name]['value']--;
			return true;
		}
		return false;
	}

	public static function Rename($name, $newName){
		if( self::IsOwner($name) ){
			$rename = new Query("{settings}");
			$rename->update(array('name' => $newName))->where()->condition('name', '=', $name)->execute();
			self::$list[$newName] = self::$list[$name];
			unset(self::$list[$name]);
			return true;
		}
		return false;
	}

	public static function Remove($name){
		if( self::IsOwner($name) ){
			$delete = new Query("{settings}");
			$delete->delete()->where()->condition('name', '=', $name)->execute();
			unset(self::$list[$name]);
			return true;
		}
		return false;
	}
}
?>