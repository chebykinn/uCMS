<?php
namespace uCMS\Core\Extensions\Entries;
use uCMS\Core\Page;
use uCMS\Core\Extensions\Theme;
class Entries extends \uCMS\Core\Extensions\Extension{
	
	public function onInstall(){

	}

	public function onUninstall(){

	}
	
	public function onAction($action){
		if($action == Page::INDEX_ACTION){
			return array("template" => Page::INDEX_ACTION);
		}else{
			Theme::LoadErrorPage(404);
		}
	}

	public function onAdminAction($action){

	}
}
?>