<?php
namespace uCMS\Core;

use uCMS\Core\Database\Query;
class Settings{
	const ADMIN_EMAIL          = 'admin_email';
	const BLOCKS_AMOUNT        = 'blocks_amount';
	const CLEAN_URL            = 'clean_url';
	const DATETIME_FORMAT      = 'datetime_format';
	const DO_UPDATE_BACKUP     = 'do_update_backup';
	const EMBEDDING_ALLOWED    = 'embedding_allowed';
	const ENABLE_CACHE         = 'enable_cache';
	const EXTENSIONS           = 'extensions';
	const INSTALLED_TABLES     = 'installed_tables';
	const LANGUAGE             = 'language';
	const MAINTENANCE_MESSAGE  = 'maintenance_message';
	const PER_PAGE             = 'per_page';
	const SITE_AUTHOR          = 'site_author';
	const SITE_DESCRIPTION     = 'site_description';
	const SITE_DOMAIN          = 'site_domain';
	const SITE_NAME            = 'site_name';
	const SITE_TITLE           = 'site_title';
	const THEME                = 'theme';
	const UCMS_DIR             = 'ucms_dir';
	const UCMS_MAINTENANCE     = 'ucms_maintenance';
	const UCMS_TIMEZONE        = 'ucms_timezone';

	const DEFAULT_AMOUNT = 21;

	private static $list = [];

	public static function Add($name, $value, $public = false){
		$owner = !$public ? Tools::GetCurrentOwner() : "";
		$query = new Query('{settings}');
		if( empty($name) ) return false;
		if( !isset(self::$list[$name]) ){
			$query->insert(
				['name', 'value', 'owner'],
				[[$name, $value, $owner]],
				true
			)->execute();
			self::$list[$name] = array('name' => $name, 'value' => Tools::PrepareSQL($value), 'owner' => $owner);
			return true;
		}
		return false;
	}

	public static function AddMultiple(array $namesAndValues){
		$owner = Tools::GetCurrentOwner();
		$query = new Query('{settings}');
		$rows = [];
		foreach ($namesAndValues as $name => $value) {
			if( isset(self::$list[$name]) ) continue;
			if( empty($name) ) continue;
			$rows[] = [$name, $value, $owner];
			self::$list[$name] = ['name' => $name, 'value' => Tools::PrepareSQL($value), 'owner' => $owner];
		}

		if( !empty($rows) ){
			$query->insert(
				['name', 'value', 'owner'],
				$rows,
				true
			)->execute();
			return true;
		}
		return false;
	}

	public static function Load(){
		$query = new Query('{settings}');
		$list = $query->select(array('name', 'value', 'owner'))->execute();
		if( !is_array($list) ) $list = [];
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

	public static function GetCount(){
		return count(self::$list);
	}
}
?>