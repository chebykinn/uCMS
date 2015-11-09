<?php
namespace uCMS\Core\Extensions\Comments;
use uCMS\Core\ORM\Model;
use uCMS\Core\Tools;
class Comment extends Model{
	public function init(){
		$this->tableName('comments');
		$this->primaryKey('cid');
		$this->belongsTo('\\uCMS\\Core\\Extensions\\Entries\\Entry', array('bind' => 'entry'));
		$this->belongsTo('\\uCMS\\Core\\Extensions\\Users\\User', array('bind' => 'author'));
	}

	public function getDate($row){	
		return Tools::FormatTime($row->created);
	}
}
?>