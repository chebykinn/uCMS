<?php
namespace uCMS\Core;

use uCMS\Core\Database\Query;
use uCMS\Core\ORM\Model;
class Setting extends Model{
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

	public function init(){
		$this->primaryKey('name');
		$this->tableName('settings');
	}

	public static function Load(){
		$list = (new self())->find();
		if( count($list) < self::DEFAULT_AMOUNT ){
			Debug::Log(self::Translate("Insufficient amount of settings"), Debug::LOG_CRITICAL, new self());
			Loader::GetInstance()->install();
		}
		foreach ($list as $setting) {
			self::$list[$setting->name] = $setting;
		}
	}

	public static function IsExists($name){
		return ( isset(self::$list[$name]) );
	}

	public static function Get($name){
		if( self::IsExists($name) ){
			return self::$list[$name]->value;
		}
	}

	public static function GetRow($name, Object $owner){
		if( !self::IsExists($name) ) return false;
		if( !self::IsOwner($name, $owner) ) return false;
		self::$list[$name]->assignOwner($owner);
		return self::$list[$name];

	}

	public static function IsOwner($name, Object $owner){
		if( !self::IsExists($name) ) return false;
		if( self::$list[$name]->owner == "" ) return true;
		if( $owner->getPackage() == Object::CORE_PACKAGE ) return true;
		if( $owner->getPackage() == self::$list[$name]->owner ) return true;
		return false;
	}

	public function create($row){
		if( !empty($this->getOwner()) ){
			$row->owner = $this->getOwner()->getPackage();
		}else{
			$row->owner = '';
		}
		$result = parent::create($row);
		if( $result ){
			self::$list[$row->name] = $row;
		}
		return $result;
	}

	public function update($row){
		if( empty($this->getOwner()) && !empty($row->owner) ) return false;

		if( !self::IsOwner($row->name, $this->getOwner()) ) return false;
		$result = parent::update($row);
		if( $result ){
			self::$list[$row->name] = $row;
		}
		return $result;
	}

	public function delete($row){
		if( empty($this->getOwner()) && !empty($row->owner) ) return false;

		if( !self::IsOwner($row->name, $this->getOwner()) ) return false;

		unset(self::$list[$row->name]);
		return parent::delete($row);
	}

	public static function Increment($name, Object $owner){
		$setting = self::GetRow($name, $owner);
		if( !$setting ) return false;
		$setting->value++;
		$result = $setting->update();
		return $result;
	}

	public static function Decrement($name, Object $owner){
		$setting = self::GetRow($name, $owner);
		if( !$setting ) return false;
		$setting->value--;
		$result = $setting->update();
		return $result;
	}

	public static function UpdateValue($name, $value, Object $owner){
		$setting = self::GetRow($name, $owner);
		if( !$setting ) return false;
		$setting->value = $value;
		return $setting->update();
	}

	public static function AddMultiple(array $namesAndValues, Object $owner){
		$package = $owner->getPackage();
		$query = new Query('{settings}');
		$rows = [];
		foreach ($namesAndValues as $name => $value) {
			if( isset(self::$list[$name]) ) continue;
			if( empty($name) ) continue;
			$rows[] = [$name, $value, $owner];
			self::$list[$name] = [
				'name' => $name,
				'value' => $owner->prepareSql($value),
				'owner' => $package
			];
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
}
?>