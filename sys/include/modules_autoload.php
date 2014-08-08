<?php
$modules = get_modules();

if($modules){
	for ($module_itor = 0; $module_itor < count($modules); $module_itor++) { 
		if($modules[$module_itor]['activated']){
			$ucms->set_language(ABSPATH.MODULES_PATH.$modules[$module_itor]['dir'].'/languages/'.SYSTEM_LANGUAGE.'.lang');
			if(file_exists(ABSPATH.MODULES_PATH.$modules[$module_itor]['dir']."/load.php")){
				require ABSPATH.MODULES_PATH.$modules[$module_itor]['dir']."/load.php";

			}
		}else{
			if($modules[$module_itor]['dir'] == 'users'){
				$posts_access = 7;
				$comments_access = 7;
				$pages_access = 7;
				$users_access = 7;
			}
		} 
	}
}


?>