<?php
namespace uCMS\Core\Extensions\Users;
use uCMS\Core\ORM\Model;
class UserInfoField extends Model{
	public static $types = ["string", "int", "float", "bool", "serialized"];


	public function init(){
		$this->primaryKey('name');
		$this->tableName('user_fields');
		$this->hasMany('\\uCMS\\Core\\Extensions\\Users\\UserInfo', ['bind' => 'values']);
	}

	protected function prepareFields($row){
		if( empty($row->type) || !in_array($row->type, self::$types) ){
			$row->type = 'string';
		}
		return true;
	}
}

?>
