<?php
require get_module("path", $module_accessID)."manage-users.php"; 
if(isset($_GET['alert'])){
	switch ($_GET['alert']) {
		case 'added':
			$ucms->alert("success", "module.users.alert.success.added");
		break;

		case 'updated':
			$ucms->alert("success", "module.users.alert.success.updated");
		break;

		case 'deleted':
			$ucms->alert("success", "module.users.alert.success.deleted");
		break;

		case 'deleted_multiple':
			$ucms->alert("success", "module.users.alert.success.deleted_multiple");
		break;

		case 'activated_multiple':
			$ucms->alert("success", "module.users.alert.success.activated_multiple");
		break;

		case 'deactivated_multiple':
			$ucms->alert("success", "module.users.alert.success.deactivated_multiple");
		break;

		case 'activated':
			$ucms->alert("success", "module.users.alert.success.activated");
		break;

		case 'deactivated':
			$ucms->alert("success", "module.users.alert.success.deactivated");
		break;
	}
}

if(isset($_GET['action'])){
	$action = $_GET['action'];
	$id = isset($_GET['id']) ? (int) $_GET['id'] : false;
	if($id){
		if($id == $user->get_user_id()){
			$accessLVL = 2;
		}elseif($user->get_user_group($id) == 1){
			$accessLVL = 6;
		}else{
			$accessLVL = 4;
		}
	}else{
		$accessLVL = 4;
	}
	switch ($action) {
		case 'add':
			echo '<h2>'.$ucms->cout("module.users.header.add.label", true).'</h2><br>';
			if($user->has_access("users", 4)) add_user_form(); else header("Location: manage.php?module=users");
		break;

		case 'update':
			echo '<h2>'.$ucms->cout("module.users.header.update.label", true).'</h2><br>';
			if($user->has_access("users", $accessLVL) and $id) update_user_form($id); else header("Location: manage.php?module=users");
		break;

		case 'delete':
			if($user->has_access("users", $accessLVL+1) and $id) delete_user($id); else header("Location: manage.php?module=users");
		break;

		case 'activate':
			if($user->has_access("users", $accessLVL) and $id) activate_user($id); else header("Location: manage.php?module=users");
		break;
	}
}else{
	echo '<h2>'.$ucms->cout('module.users.header.label', true).'</h2><br>';
	if(isset($_GET['query'])){
		echo '<h3>'.$ucms->cout("module.users.search.header", true, htmlspecialchars($_GET['query'])).'</h3><br>';
	}
	$ucms->template(get_module('path', 'search').'forms/search-form-min.php', false);

	if($user->has_access("users", 4)){ 
		?>
		<a class="ucms-add-button" href="manage.php?module=users&amp;action=add"><?php echo $ucms->cout("module.users.add.button"); ?></a><br><br>
		<?php 
	}
	if(isset($_POST['update'])){
		echo '<div class="error">';
		echo $ucms->cout("module.users.error.update.label", true).'<br><br>';
		echo update_user($_POST);
		echo '</div>';
	}elseif(isset($_POST['add'])){
		echo '<div class="error">';
		echo $ucms->cout("module.users.error.add.label", true).'<br><br>';
		echo add_user($_POST);
		echo '</div>';
	}
	manage_users();
}
?>