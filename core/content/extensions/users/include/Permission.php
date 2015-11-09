<?php
namespace uCMS\Core\Extensions\Users;
use uCMS\Core\ORM\Model;
use uCMS\Core\Tools;
class Permission extends Model{
	private static $list;
	public function init(){
		$this->primaryKey('name');
		$this->tableName('group_permissions');
		$this->belongsTo('\\uCMS\\Core\\Extensions\\Users\\Group');
	}

	public static function Register($name, $title, $description){
		$owner = Tools::GetCurrentOwner();
		self::$list[$name] = array('title' => $title, 'description' => $description, 'owner' => $owner);
	}

	public function getInfo($row){
		if( self::IsExists($row->name) ){
			return self::$list[$row->name];
		}else{
			// TODO: handle missing info
		}
		return array();
	}

	public static function GetPermissionsList(){
		return self::$list;
	}

	public static function IsExists($name){
		return isset(self::$list[$name]);
	}
}
?>