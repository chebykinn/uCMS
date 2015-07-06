<?php
class Entries extends Extension{
	
	public function onInstall(){

	}

	public function onUninstall(){

	}

	public function onLoad(){
	}
	
	public function onAction($action){
		if($action == INDEX_ACTION){
			return array("template" => INDEX_ACTION);
		}else{
			Theme::LoadErrorPage(404);
		}
	}

	public function onAdminAction($action){

	}
}
?>