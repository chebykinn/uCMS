<?php
namespace uCMS\Core\Extensions\Comments;
use uCMS\Core\Extensions\Extension;
use uCMS\Core\Extensions\IExtension;
use uCMS\Core\Admin\ControlPanel;
use uCMS\Core\Extensions\Users\Permission;
class Comments extends Extension implements IExtension{
	public function onLoad(){
		Permission::Register('manage comments', tr('Manage Comments'), 'Allow user to add, edit and delete comments.');
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
		ControlPanel::SetTitle(tr("Comments"));
	}
}
?>