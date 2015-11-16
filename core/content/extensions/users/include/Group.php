<?php
namespace uCMS\Core\Extensions\Users;
use uCMS\Core\Database\Query;
use uCMS\Core\ORM\Model;

class Group extends Model{
	const ADMINISTRATOR = 1;
	const MODERATOR     = 2;
	const TRUSTED       = 3;
	const USER          = 4;
	const BANNED        = 5;
	const GUEST         = 6;

	public function init(){
		$this->primaryKey('gid');
		$this->tableName('groups');
		$this->hasMany("\\uCMS\\Core\\Extensions\\Users\\User", array('bind' => 'users'));
		$this->hasMany("\\uCMS\\Core\\Extensions\\Users\\Permission", array('bind' => 'permissions', 'key' => 'gid'));
	}

	public function hasPermission($row, $name){
		foreach ($row->permissions as $permission) {
			if( $permission->name === $name ){
				return true;
			}
		}
		return false;
	}

	public function getPermissions($row){
		return $row->permissions;
	}

	public static function GrantPermission($name, $group){
		if( !is_object($group) ) return false;
		if( !$group->hasPermission($name) ){
			$check = new Query('{group_permissions}');
			$data = $check->select('owner')->where()->condition('name', '=', $name)->limit(1)->execute(); //add query method to check
			if(count($data) > 0){
				$add = new Query('{group_permissions}');
				$add->insert(
					['gid', 'name', 'owner'],
					[[$group->getID(), $name, $data[0]['owner']]]
				)->execute();
			}
		}
	}

	public static function DenyPermission($name, $group){
		if( !is_object($group) ) return false;
		if( $group->hasPermission($name) ){
			$query = new Query('{group_permissions}');
			$query->delete()->where()->condition('gid', '=', $group->getID())->execute();
		}
	}
}
?>