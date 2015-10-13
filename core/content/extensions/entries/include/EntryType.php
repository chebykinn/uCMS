<?php
namespace uCMS\Core\Extensions\Entries;

use uCMS\Core\ORM\Model;
use uCMS\Core\Settings;
class EntryType extends Model{
	public function init(){
		$limit = (int) Settings::Get('entries_per_page');
		$this->primaryKey('type');
		$this->tableName('entry_types');
		$this->hasMany('\\uCMS\\Core\\Extensions\\Entries\\Entry', 
			array(
			'bind' => 'entries',
			'conditions' => array('limit' => $limit),
			'key' => 'type')
		);
	}
}
?>