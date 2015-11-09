<?php
namespace uCMS\Core\Extensions\Search;
use uCMS\Core\Extensions\Extension;
use uCMS\Core\Extensions\IExtension;
use uCMS\Core\Admin\ControlPanel;
class Search extends Extension implements IExtension{

	public function onInstall($stage){

	}

	public function onUninstall(){

	}

	public function onShutdown(){

	}

	public function onLoad(){
		
	}

	public function onAction($action){
		
	}

	public function onAdminAction($action){
		ControlPanel::SetTitle(tr('Search'));
	}
}
?>