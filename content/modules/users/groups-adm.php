<?php
if(!$user->has_access("users", 7)) header("Location: index.php");
require get_module("path", $module_accessID).'manage-groups.php';
if(isset($_GET['alert'])){
	switch ($_GET['alert']) {
		case 'added':
			$ucms->alert("success", "module.users.groups.alert.success.added");
		break;

		case 'updated':
			$ucms->alert("success", "module.users.groups.alert.success.updated");
		break;

		case 'deleted':
			$ucms->alert("success", "module.users.groups.alert.success.deleted");
		break;

		case 'deleted_multiple':
			$ucms->alert("success", "module.users.groups.alert.success.deleted_multiple");
		break;

	}
}
		
if(isset($_GET['action'])){
	$action = $_GET['action'];
	$id = isset($_GET['id']) ? (int) $_GET['id'] : false;
	switch ($action) {
		case 'add':
			echo "<h2>".$ucms->cout("module.users.groups.header.add.label", true)."</h2><br>";
			if($user->has_access("users", 6)) add_group_form();
		break;
		case 'update':
			echo "<h2>".$ucms->cout("module.users.groups.header.update.label", true)."</h2><br>";
			if($user->has_access("users", 6) and $id) update_group_form($id);
		break;
		case 'delete':
			if($user->has_access("users", 7) and $id) delete_group($id);
		break;
	}
}else{
	if(isset($_POST['add']) and $user->has_access("users", 6)):
		add_group($_POST);
	elseif(isset($_POST['update']) and $user->has_access("users", 6)):
		update_group($_POST);
	endif;
	echo "<h2>".$ucms->cout("module.users.groups.header.label", true)."</h2><br>";
	?>
	<a class="ucms-add-button" href="manage.php?module=users&amp;section=groups&amp;action=add"><?php $ucms->cout("module.users.groups.add.button"); ?></a>
	<br><br>
	<?php 
	manage_groups();
}
?>