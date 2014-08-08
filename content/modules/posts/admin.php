<?php
if(!isset($_GET['section'])){
	$module_accessID = "posts";
	$module_accessLVL = 2;
	$title = "module.posts.admin.title";
	$manage_file = "posts-adm.php";
}else{
	switch ($_GET['section']) {
		case 'categories':
			$module_accessID = "posts";
			$module_accessLVL = 4;
			$title = "module.posts.admin.categories.title";
			$manage_file = "categories-adm.php";
		break;
		
		default:
			$module_accessID = "posts";
			$module_accessLVL = 2;
			$title = "module.posts.admin.title";
			$manage_file = "posts-adm.php";
		break;
	}
	
}
?>