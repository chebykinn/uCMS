<?php
include get_module("path", $module_accessID).'manage-widgets.php';
if(isset($_GET['alert'])){
	switch ($_GET['alert']) {
		case 'updated':
			$ucms->alert("success", "module.widgets.alert.success.updated");
		break;

		case 'deleted':
			$ucms->alert("success", "module.widgets.alert.success.deleted");
		break;

		case 'deleted_multiple':
			$ucms->alert("success", "module.widgets.alert.success.deleted_multiple");
		break;

		case 'added':
			$ucms->alert("success", "module.widgets.alert.success.added");
		break;

		case 'activated':
			$ucms->alert("success", "module.widgets.alert.success.activated");
		break;

		case 'deactivated':
			$ucms->alert("success", "module.widgets.alert.success.deactivated");
		break;

		case 'activated_multiple':
			$ucms->alert("success", "module.widgets.alert.success.activated_multiple");
		break;

		case 'deactivated_multiple':
			$ucms->alert("success", "module.widgets.alert.success.deactivated_multiple");
		break;
	}
}
echo "<h2>".$ucms->cout("module.widgets.header.label", true)."</h2><br>";

if($user->has_access("widgets", 2)){
?>
	<h3><?php $ucms->cout("module.widgets.header.add.label"); ?></h3>
	<form action="manage.php?module=widgets" method="post" enctype="multipart/form-data">
	<input type="hidden" name="add">
	<input type="file" name="widgetarch">
	<input type="submit" class="ucms-button-submit" value="<?php $ucms->cout("module.widgets.install.button"); ?>">
	</form>
<?php
}
if($user->has_access("widgets", 1)){
	if(isset($_POST['add']))
		add_widget($_POST);
	else if(isset($_GET['action'])){
		$action = $_GET['action'];
		$id = isset($_GET['id']) ? $_GET['id'] : false;
		switch ($action) {
			case 'activate':
				if($id and $user->has_access("widgets", 3)) activate_widget($id);

			break;
			case 'deactivate':
				if($id and $user->has_access("widgets", 3)) deactivate_widget($id);
			break;

			case 'delete':
				if($id and $user->has_access("widgets", 4)) delete_widget($id);
			break;
		}
	}
	echo "<br><br>";
	manage_widgets();
}
?>