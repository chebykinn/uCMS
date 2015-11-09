<?php
namespace uCMS\Core\Extensions\Menus;
use uCMS\Core\Extensions\Extension;
use uCMS\Core\Extensions\IExtension;
use uCMS\Core\Extensions\Users\Permission;
use uCMS\Core\Admin\ControlPanel;
class Menus extends Extension implements IExtension{
	public function onLoad(){
		Permission::Register('manage menu links', tr('Manage Menu Links'), 'Allow user to add, edit and delete menu links.');
	}

	public function onInstall($stage){
		
	}

	public function onUninstall(){
		
	}

	public function onShutdown(){

	}

	public function onAction($action){
		
	}

	public function onAdminAction($action){
		ControlPanel::SetTitle(tr("Menu"));
	}
}
?>