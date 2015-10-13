<?php
namespace uCMS\Core\Extensions\Users;
use uCMS\Core\ORM\Model;
class Permission extends Model{

	public function init(){
		$this->primaryKey('name');
		$this->tableName('group_permissions');
		$this->belongsTo('\\uCMS\\Core\\Extensions\\Users\\Group');
	}
}
?>