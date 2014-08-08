<?php
include get_module("path", $module_accessID).'manage-plugins.php';
if(isset($_GET['alert'])){
	switch ($_GET['alert']) {
		case 'updated':
			$ucms->alert("success", "module.plugins.alert.success.updated");
		break;

		case 'deleted':
			$ucms->alert("success", "module.plugins.alert.success.deleted");
		break;

		case 'deleted_multiple':
			$ucms->alert("success", "module.plugins.alert.success.deleted_multiple");
		break;

		case 'added':
			$ucms->alert("success", "module.plugins.alert.success.added");
		break;

		case 'activated':
			$ucms->alert("success", "module.plugins.alert.success.activated");
		break;

		case 'deactivated':
			$ucms->alert("success", "module.plugins.alert.success.deactivated");
		break;

		case 'activated_multiple':
			$ucms->alert("success", "module.plugins.alert.success.activated_multiple");
		break;

		case 'deactivated_multiple':
			$ucms->alert("success", "module.plugins.alert.success.deactivated_multiple");
		break;
	}
}

echo "<h2>".$ucms->cout("module.plugins.header.label", true)."</h2><br>";
if($user->has_access("plugins", 2)){ 
?>
	<h3><?php $ucms->cout("module.plugins.add.header"); ?></h3>
	<form action="manage.php?module=plugins" method="post" enctype="multipart/form-data">
	<input type="hidden" name="add">
	<input type="file" name="pluginarch">
	<input type="submit" class="ucms-button-submit" value="<?php $ucms->cout("module.plugins.add.button"); ?>">
	</form>
<?php
}
if($user->has_access("plugins", 1)){
	if(isset($_POST['add']) and $user->has_access("plugins", 2))
		add_plugin($_POST);
	else if(isset($_GET['action'])){
		$action = $_GET['action'];
		$id = isset($_GET['id']) ? $_GET['id'] : false;
		switch ($action) {
			case 'activate':
				if($id and $user->has_access("plugins", 3)) activate_plugin($id);
			break;
			case 'deactivate':
				if($id and $user->has_access("plugins", 3)) deactivate_plugin($id);
			break;
			case 'delete':
				if($id and $user->has_access("plugins", 4)) delete_plugin($id);
			break;
		}
	}
	echo "<br><br>";
	manage_plugins();
}
?>