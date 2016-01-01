<?php
namespace uCMS\Core\Extensions\Entries;

use uCMS\Core\ORM\Model;
use uCMS\Core\Setting;
class EntryType extends Model{
	const DEFAULT_AMOUNT = 2;
	const ARTICLE = 'article';
	const PAGE = 'page';
	public function init(){
		$limit = (int) Setting::Get('entries_per_page');
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