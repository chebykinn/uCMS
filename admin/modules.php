<?php
include "config.php";
$title = $ucms->cout("admin.modules.title", true)." :: ";
include "head.php";
include "sidebar.php";
if(!$user->has_access("system") or !MODULES_ENABLED) header("Location: index.php");
include 'manage-modules.php';
if(isset($_GET['alert'])){
	switch ($_GET['alert']) {
		case 'updated':
			$ucms->alert("success", $ucms->cout("admin.modules.success.upd", true));
		break;

		case 'deleted':
			$ucms->alert("success", $ucms->cout("admin.modules.success.del", true));
		break;

		case 'deleted_multiple':
			$ucms->alert("success", $ucms->cout("admin.modules.success.delm", true));
		break;

		case 'added':
			$ucms->alert("success", $ucms->cout("admin.modules.success.add", true));
		break;

		case 'activated':
			$ucms->alert("success", $ucms->cout("admin.modules.success.act", true));
		break;

		case 'deactivated':
			$ucms->alert("success", $ucms->cout("admin.modules.success.deact", true));
		break;

		case 'activated_multiple':
			$ucms->alert("success", $ucms->cout("admin.modules.success.actm", true));
		break;

		case 'deactivated_multiple':
			$ucms->alert("success", $ucms->cout("admin.modules.success.deactm", true));
		break;
	}
}
?>
	<h2><?php $ucms->cout("admin.modules.header")?></h2><br>
	<h3><?php $ucms->cout("admin.modules.add.label")?></h3>
	<form action="modules.php" method="post" enctype="multipart/form-data">
	<input type="hidden" name="add">
	<input type="file" name="modulearch">
	<input type="submit" class="ucms-button-submit" value="<?php $ucms->cout("admin.modules.add.submit.button")?>">
	</form>
	<?php
	if($user->has_access("system") and MODULES_ENABLED){
		if(isset($_POST['add']))
			add_module($_POST);
		else if(isset($_GET['action'])){
			$action = $_GET['action'];
			$id = isset($_GET['id']) ? $_GET['id'] : false;
			switch ($action) {
				case 'activate':
					if($id) activate_module($id);
					break;
				case 'delete':
					if($id) delete_module($id);
					break;
			}
		}
		echo "<br><br>";
		manage_modules();
	}
include "footer.php"; ?>