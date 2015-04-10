<?php
class Posts extends Extention{
	
	public function install(){

	}

	public function uninstall(){

	}

	public function load(){

	}
	
	public function doAction($action){
		if($action == 'index'){
			return array("template" => 'index', "title" => tr("Site Name"));
		}else{
			error_404();
		}
	}

	public function doAdminAction($action){

	}
}
?>