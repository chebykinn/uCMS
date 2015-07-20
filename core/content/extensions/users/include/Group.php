<?php
namespace uCMS\Core\Extensions\Users;
use uCMS\Core\Database\Query;

class Group{
	private $permissions;
	private $gid;
	private $name;
	private $position;

	public function __construct(){
		$args = func_get_args();
		$data = array();
		if( count($args) == 1){
			if( is_array($args[0]) ){
				$data = $args[0];
			}else{
				$id = $args[0];
				$query = new Query('{groups}');
				$rows = $query->select('*', true)->where()->condition('gid', '=', $id)->_or()
				                                          ->condition('name', '=', $id)->execute();
				if(count($rows) == 1){
					$data = $rows[0];
				}
			}
		}
		$fields = array_keys( get_object_vars($this) );
		foreach ($data as $field => $value) {
			if(  in_array($field, $fields) ){
				$this->$field = $value;
			}
		}
		if( !is_array($this->permissions) ){
			$query = new Query('{group_permissions}');
			$results = $query->select(array('name', 'owner'))->where()->condition('gid', '=', $this->gid)->execute();
			if( count($results) > 0 ){
				foreach ($results as $row) {
					$this->permissions[$row['name']] = $row['owner'];
				}
			}
		}
	}

	public function getID(){
		if( !empty($this->gid) ){
			return $this->gid;
		}
		return 0;
	}

	public function getName(){
		if( !empty($this->name) ){
			return $this->name;
		}
		return "";
	}

	public function getPosition(){
		if( !empty($this->position) ){
			return $this->position;
		}
		return 0;
	}

	public function hasPermission($name){
		return isset($this->permissions[$name]);
	}

	public function getPermissions(){
		return $this->permissions;
	}

	public static function Add($group){
		/**
		* @todo add checks for object
		*/
		$groupName = $group->getName();
		if( is_object($group) && !empty($groupName) ){
			$query = new Query('{groups}');
			$query->insert( array("gid" => "NULL",
								  "name" => $group->getName(),
								  "position" => $group->getPosition()) )->execute();
			/** 
			* @todo add permissions
			*/
		}
	}

	public static function Update($group){

	}

	public static function Delete($groupID){

	}

	public static function GrantPermission($name, $group){
		if( !is_object($group) ) return false;
		if( !$group->hasPermission($name) ){
			$check = new Query('{group_permissions}');
			$data = $check->select('owner')->where()->condition('name', '=', $name)->limit(1)->execute(); //add query method to check
			if(count($data) > 0){
				$add = new Query('{group_permissions}');
				$add->insert(array('gid' => $group->getID(), 'name' => $name, 'owner' => $data[0]['owner']))->execute();
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