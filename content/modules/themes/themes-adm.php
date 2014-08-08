<?php
include get_module("path", $module_accessID).'manage-themes.php';
if(isset($_GET['alert'])){
	switch ($_GET['alert']) {
		case 'updated':
			$ucms->alert("success", "module.themes.alert.success.updated");
		break;

		case 'deleted':
			$ucms->alert("success", "module.themes.alert.success.deleted");
		break;

		case 'deleted_multiple':
			$ucms->alert("success", "module.themes.alert.success.deleted_multiple");
		break;

		case 'added':
			$ucms->alert("success", "module.themes.alert.success.added");
		break;

		case 'activated':
			$ucms->alert("success", "module.themes.alert.success.activated");
		break;
	}
}
if($user->has_access("themes", 1)){
	$action = isset($_GET['action']) ? $_GET['action'] : "";
	$id = isset($_GET['id']) ? $_GET['id'] : false;
	?>
	<h2><?php $ucms->cout("module.themes.header.label"); ?></h2><br>
	<?php
	if($user->has_access("themes", 2)){ 
		if(!empty($_SESSION['theme'])){
			echo '<a class="ucms-add-button" href="'.UCMS_DIR.'/admin/manage.php?module=themes&amp;action=disable_tryout&amp;id='.$_SESSION['theme'].'">
			'.$ucms->cout("module.themes.disable_preview.button" ,true, $theme->get('local_name', $_SESSION['theme'])).'</a><br><br>';
		}
		?>
			<h3><?php $ucms->cout("module.themes.header.add.label"); ?></h3>
			<form action="manage.php?module=themes" method="post" enctype="multipart/form-data">
			<input type="hidden" name="add">
			<input type="file" name="themearch">
			<input type="submit" class="ucms-button-submit" value="<?php $ucms->cout("module.themes.install.button"); ?>">
			</form>
		<?php
	}
	if(isset($_POST['add']))
		add_theme($_POST);
	switch ($action) {
		case 'activate':
			if($id and $user->has_access("themes", 3)) activate_theme($id);
		break;

		case 'delete':
			if($id and $user->has_access("themes", 4)) delete_theme($id);
		break;

		case 'tryout':
			$_SESSION['theme'] = $id;
			header("Location: ".SITE_DOMAIN.UCMS_DIR."/");
		break;

		case 'disable_tryout':
			unset($_SESSION['theme']);
			header("Location: ".SITE_DOMAIN.UCMS_DIR."/admin/manage.php?module=themes");
		break;
	}
	echo "<br><br>";
	manage_themes();

}
?>