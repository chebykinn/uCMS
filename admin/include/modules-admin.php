<?php
$links = array();
$settings_links = array();
if($modules){
	for ($module_itor = 0; $module_itor < count($modules); $module_itor++) { 
		if($modules[$module_itor]['activated']){
			$ucms->set_language(ABSPATH.MODULES_PATH.$modules[$module_itor]['dir'].'/languages/'.SYSTEM_LANGUAGE.'.lang');
			
			if(file_exists(ABSPATH.MODULES_PATH.$modules[$module_itor]['dir']."/admin-load.php")){
				require ABSPATH.MODULES_PATH.$modules[$module_itor]['dir']."/admin-load.php";
			}
	
			if(isset($_GET['module']) and $modules[$module_itor]['dir'] == $_GET['module']){
				if(file_exists(ABSPATH.MODULES_PATH.$modules[$module_itor]['dir']."/admin.php")){
					require ABSPATH.MODULES_PATH.$modules[$module_itor]['dir']."/admin.php";
				}
			}
		}
	}
}

?>