<?php
namespace uCMS\Core\Extensions\Menus;
use uCMS\Core\ORM\Model;
use uCMS\Core\Page;
class MenuLink extends Model{
	public function init(){
		$this->tableName('menu_links');
		$this->primaryKey('lid');
		$this->belongsTo('\\uCMS\\Core\\Extensions\\Users\\User', ['bind' => 'author']);
		$this->belongsTo('\\uCMS\\Core\\Extensions\\Menus\\Menu', ['bind' => 'baseMenu']);
	}

	public function getLink($row){
		$action = $row->link;
		// TODO: Allowed protocols
		if( preg_match("/^(https?):\/\//", $action) || $row->external ){
			return $action;
		}
		if( $row->link instanceof Page ){
			return $row->link;
		}

		return Page::FromAction($action);
	}

	protected function prepareFields($row){
		if( empty($row->owner) ){
			if( empty($this->getOwner()) ) return false;
			$row->owner = $this->getOwner()->getPackage();
		}
		return true;
	}

	public function isCurrentPage($row){
		$page = (string)Page::GetCurrent();
		$link = (string)$row->getLink();
		return ($link === $page);
	}
}

?>
