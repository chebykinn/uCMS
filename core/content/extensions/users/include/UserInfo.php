<?php
namespace uCMS\Core\Extensions\Users;
use uCMS\Core\ORM\Model;
class UserInfo extends Model{
	public function init(){
		$this->primaryKey('name');
		$this->tableName('user_info');
		$this->belongsTo('\\uCMS\\Core\\Extensions\\Users\\User', ['bind' => 'user']);
		$this->belongsTo('\\uCMS\\Core\\Extensions\\Users\\UserInfoField', ['bind' => 'data']);
	}
}
?>