<?php
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
}
?>