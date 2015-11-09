<?php
namespace uCMS\Core\Extensions\FileManager;
use uCMS\Core\Admin\ControlPanel;
use uCMS\Core\Extensions\Users\Permission;
class FileManager extends \uCMS\Core\Extensions\Extension implements \uCMS\Core\Extensions\IExtension {
	
	public function onInstall($stage){

	}

	public function onUninstall(){

	}

	public function onShutdown(){

	}

	public function onLoad(){
		Permission::Register('manage files', tr('Manage Files'), 'Allow user to add, edit and delete files.');
	}

	public function onAction($action){
		
	}

	public function onAdminAction($action){
		ControlPanel::SetTitle(tr('File Manager'));
	}
}
?>