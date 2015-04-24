<?php
class Posts extends Extension{
	
	public function install(){

	}

	public function uninstall(){

	}

	public function load(){
	}
	
	public function doAction($action){
		if($action == INDEX_ACTION){
			return array("template" => INDEX_ACTION);
		}else{
			error_404();
		}
	}

	public function doAdminAction($action){

	}
}
?>