<?php
namespace uCMS\Core\Extensions\Users\UserInfo;
use uCMS\Core\ORM\Model;
class UserInfo extends Model{
	public function init(){
		$this->primaryKey('name');
		$this->tableName('user_info');
		$this->belongsTo('\\uCMS\\Core\\Extensions\\Users\\User', array('bind' => 'info'));
	}
}
?>