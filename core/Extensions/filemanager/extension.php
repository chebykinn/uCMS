<?php
namespace uCMS\Core\Extensions\FileManager;
class FileManager extends \uCMS\Core\Extensions\Extension{
	
	public function onInstall(){

	}

	public function onUninstall(){

	}

	public function onLoad(){
		
	}

	public function onAdminAction($action){
		return tr('File Manager');
	}
}
?>