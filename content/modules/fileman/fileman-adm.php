<?php
include get_module("path", $module_accessID).'manage-files.php';
if(!is_dir(ABSPATH.UPLOADS_PATH)) mkdir(ABSPATH.UPLOADS_PATH);
if(isset($_GET['alert'])){
	switch ($_GET['alert']) {
		case 'deleted':
			$ucms->alert("success", "module.fileman.alert.success.deleted");
		break;

		case 'deleted_multiple':
			$ucms->alert("success", "module.fileman.alert.success.deleted_multiple");
		break;

		case 'created_dir':
			$ucms->alert("success", "module.fileman.alert.success.created_dir");
		break;

		case 'added_multiple':
			$ucms->alert("success", "module.fileman.alert.success.added_multiple");
		break;

		case 'added':
			$ucms->alert("success", "module.fileman.alert.success.added");
		break;

		case 'extracted':
			$ucms->alert("success", "module.fileman.alert.success.extracted");
		break;

		case 'extract_failed':
			$ucms->alert("error", "module.fileman.alert.error.extract_failed");
		break;

		case 'renamed':
			$ucms->alert("success", "module.fileman.alert.success.renamed");
		break;

		case 'copied':
			$ucms->alert("success", "module.fileman.alert.success.copied");
		break;

		case 'moved':
			$ucms->alert("success", "module.fileman.alert.success.moved");
		break;

		case 'same_dir':
			$ucms->alert("error", 'module.fileman.alert.error.same_dir');
		break;

		case 'paste_error':
			$ucms->alert("error", 'module.fileman.alert.error.paste_error');
		break;
	}
}
if($user->has_access('fileman', 1)){
	if(isset($_GET['dir']))
		$dir_url = $_GET['dir'];
	elseif(isset($_GET['backdir']))
		$dir_url = $_GET['backdir'];
	else $dir_url = "";

	if(isset($_FILES['file']) and $user->has_access('fileman', 2))
		add_file($_POST);
	else if(isset($_GET['action']) and $_GET['action'] != 'rename'){
		$action = $_GET['action'];
		$id = false;
		$dir = false;
		if(isset($_GET['directory'])){
			$id = $_GET['directory'] != "" ? ABSPATH.UPLOADS_PATH.$dir_url.$_GET['directory'] : false;	
			$dir = true;
		}elseif(isset($_GET['file']))
			$id = $_GET['file'] != "" ? ABSPATH.UPLOADS_PATH.$dir_url.$_GET['file'] : false;
		switch ($action) {
			case 'delete':
				if($id){
					if($dir and $_GET['directory'] == $user->get_user_login()){
						$accessLVL = 3;
					}else{
						$accessLVL = 4;
					}
					if($user->has_access('fileman', $accessLVL)){
						if($dir){
							$ucms->remove_dir($id);
						}else{
							unlink($id);
						}
						header("Location: ?module=fileman&dir=$dir_url&alert=deleted");
					}else header("Location: ?module=fileman&dir=$dir_url");
				}
			break;

			case 'mkdir':
				create_directory();
			break;

			case 'extract':
				extract_archive();
			break;

			default:
				header("Location: manage.php?module=fileman");
				exit;
			break;

		}
	}else{
		echo "<h2>".$ucms->cout("module.fileman.header", true)."</h2><br>";
		if($user->has_access("fileman", 2)){
			?>
			<h3><?php $ucms->cout("module.fileman.upload.label"); ?></h3>
			<form action="manage.php?module=fileman" method="post" enctype="multipart/form-data">
			<input type="file" name="file[]" multiple>
			<input type="hidden" name="backdir" value="<?php echo $dir_url; ?>">
			<input type="submit" class="ucms-button-submit" value="<?php $ucms->cout("module.fileman.upload.button"); ?>">
			</form>
			<br><br>
			<form method="get" action="manage.php">
			<input type="hidden" name="action" value="mkdir">
			<input type="hidden" name="module" value="fileman">
			<input type="hidden" name="backdir" value="<?php echo $dir_url; ?>">
			<input type="text" name="newdir" style="width: 150px;" placeholder="<?php $ucms->cout("module.fileman.newdir.placeholder"); ?>">
			<input type="submit" class="ucms-button-submit" name="add" value="<?php $ucms->cout("module.fileman.newdir.add.button"); ?>">
			</form><br>
			<?php
		}
		manage_files();
	}
}
?>