<?php
namespace uCMS\Core\Extensions\Entries;
use uCMS\Core\ORM\Model;
use uCMS\Core\uCMS;
class Term extends Model{
	const DEFAULT_AMOUNT = 1;
	public function init(){
		$this->primaryKey('tid');
		$this->tableName('terms');
		$this->belongsTo("\\uCMS\\Core\\Extensions\\Entries\\Entry", array('through' => 'term_taxonomy', 'bind' => 'entry'));
	}

	public function getDate($row, $fromCreation = false){
		$time = $fromCreation ? $row->created : $row->changed;
		
		return uCMS::FormatTime($time);

	}
}
?>