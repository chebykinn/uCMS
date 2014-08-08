<?php
if($modules){
	for ($module_itor = 0; $module_itor < count($modules); $module_itor++) { 
		if(file_exists(ABSPATH.MODULES_PATH.$modules[$module_itor]['dir']."/url_actions.php") and $modules[$module_itor]['activated']){
			//echo MODULES_PATH.$modules[$module_itor]['dir']."/url_actions.php<br>";
			require ABSPATH.MODULES_PATH.$modules[$module_itor]['dir']."/url_actions.php";
		}
	}
}
?>