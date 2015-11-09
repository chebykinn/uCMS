<?php
namespace uCMS\Core\Extensions\Entries;
use uCMS\Core\ORM\Model;
class Term extends Model{
	public function init(){
		$this->primaryKey('tid');
		$this->tableName('terms');
		$this->belongsTo("\\uCMS\\Core\\Extensions\\Entries\\Entry", array('through' => 'term_taxonomy', 'bind' => 'entry'));
	}
}
?>