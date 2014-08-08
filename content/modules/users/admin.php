<?php
if(!isset($_GET['section'])){
	$module_accessID = "users";
	$module_accessLVL = 2;
	$title = "module.users.title";
	$manage_file = "users-adm.php";
}else{
	switch ($_GET['section']) {
		case 'groups':
			$module_accessID = "users";
			$module_accessLVL = 7;
			$title = "module.users.groups.title";
			$manage_file = "groups-adm.php";
		break;
		
		default:
			$module_accessID = "users";
			$module_accessLVL = 2;
			$title = "module.users.title";
			$manage_file = "users-adm.php";
		break;
	}
	
}
?>