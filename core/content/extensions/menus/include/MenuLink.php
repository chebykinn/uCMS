<?php
namespace uCMS\Core\Extensions\Menus;
use uCMS\Core\ORM\Model;
use uCMS\Core\Page;
class MenuLink extends Model{
	public function init(){
		$this->tableName('menus');
		$this->primaryKey('lid');
		$this->belongsTo('\\uCMS\\Core\\Extensions\\Users\\User', array('bind' => 'author'));
	}

	public function getLink($row){
		$data = "";
		$action = $row->link;
		$page = explode('/', $row->link, 2);
		if( isset($page[1]) ){
			$action = $page[0];
			$data = $page[1];
		}
		return Page::FromAction($action, $data);
	}
}

?>