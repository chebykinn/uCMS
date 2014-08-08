<?php
require 'config.php';
$title = $ucms->cout("admin.updates.title", true);
include 'head.php';
include 'sidebar.php';
if(!$user->has_access("system")){
	header("Location: index.php");
	exit;
}

if(isset($_GET['alert'])){
	switch ($_GET['alert']) {
		case 'incorrect_file':
			$ucms->alert('error', "admin.updates.incorrect_file");
		break;

		case 'error_opening_update':
			$ucms->alert("error", "admin.updates.error_opening_update");
		break;

		case 'no_item_selected':
			$ucms->alert("error", "admin.updates.no_item_selected");
		break;

		case 'success':
			$ucms->alert("success", "admin.updates.success");
		break;
	}
}
 
echo '<h2>'.$ucms->cout("admin.updates.header", true).'</h2><br>';

if(isset($_POST['do-update'])){
	if(isset($_FILES['updatefile'])){
		check_update_file($_FILES['updatefile']);
	}else
		do_update($_POST);
}else{
	if(is_dir(ABSPATH.UCMS_DIR."admin/update"))
		$ucms->remove_dir(ABSPATH.UCMS_DIR."admin/update");

	if(update_available()){
		$_SESSION['ucms-update-available'] = get_update_version();
		$ucms->cout("admin.updates.new_version", false, get_update_version());
		?>
		<br>
		<form action="update.php" method="post">
			<input class="ucms-button-submit" type="submit" name="do-update" value="<?php $ucms->cout("admin.updates.submit.button"); ?>">
		</form>
		<br>
		<?php
	}else{
		if(!empty($_SESSION['ucms-update-available'])) unset($_SESSION['ucms-update-available']);
		$ucms->cout("admin.updates.no_update_available");
	}

	$ucms->cout("admin.updates.reinstall.label");
	?>
	<br><br>
	<form action="update.php" method="post">
		<input type="hidden" name="reinstall">
		<input class="ucms-button-submit" type="submit" name="do-update" value="<?php $ucms->cout("admin.updates.reinstall.button"); ?>">
	</form>
	<?php
	if(MODULES_ENABLED){ ?>
		<br><br>
		<h3><?php $ucms->cout("admin.updates.update_by_upload.label"); ?></h3>
		<form action="update.php" method="post" enctype="multipart/form-data">
			<input type="file" name="updatefile" required>
			<input type="submit" name="do-update" class="ucms-button-submit" value="<?php $ucms->cout("admin.updates.add.submit.button")?>">
			</form>
		<?php 
	}
	echo "<br><br>";
	$ext = array(
		0 => 'themes',
		1 => 'widgets',
		2 => 'plugins',
		3 => 'modules');
	for ($c = 0; $c < 4; $c++) {
		if(is_activated_module($ext[$c]) or ($ext[$c] == 'modules' AND MODULES_ENABLED)){
			$ucms->cout("admin.updates.".$ext[$c].".header");
			if(update_available($ext[$c])){
				$ucms->cout("admin.updates.".$ext[$c].".update_available");
				$extentions = get_updating_extentions($ext[$c]);
				echo '<form action="update.php" method="post">
				<input type="hidden" name="extention" value='.$ext[$c].'> 
				<input type="submit" name="do-update" value="'.$ucms->cout('admin.updates.extentions.submit.button', true).'" class="ucms-button-submit"><br><br>';
				echo '<table class="manage">';
				echo '<tr>';
				echo '<th style="width: 15px;"><input type="checkbox" name="select-all" value="1"></th>';
				echo '<th>'.$ucms->cout('admin.updates.update.name', true).'</th>';
				echo '<th>'.$ucms->cout('admin.updates.update.version', true).'</th>';
				echo '</tr>';
				for ($i = 0; $i < count($extentions); $i++) {
					switch ($ext[$c]) {
						case 'themes':
							$local_name = $theme->get('local_name', $extentions[$i]['dir']);
							$local_description = $theme->get('local_description', $extentions[$i]['dir']);
						break;
					
						case 'widgets':
							$local_name = $widget->get('local_name', $extentions[$i]['dir']);
							$local_description = $widget->get('local_description', $extentions[$i]['dir']);
						break;
	
						case 'plugins':
							$local_name = $plugin->get('local_name', $extentions[$i]['dir']);
							$local_description = $plugin->get('local_description', $extentions[$i]['dir']);
						break;
	
						case 'modules':
							$local_name = get_module('local_name', $extentions[$i]['dir']);
							$local_description = get_module('local_description', $extentions[$i]['dir']);
						break;
					}
					echo '<tr>'; 
					echo '<td><input type="checkbox" name="item[]" value="'.$extentions[$i]['dir'].'"></td>';
					echo '<td style="width: 30%"><b>'.$local_name.'</b> - '.$local_description.'</td>';
					echo '<td>'.$ucms->cout('admin.updates.version.old', true).$extentions[$i]['version'].
					$ucms->cout('admin.updates.version.new', true).$extentions[$i]['new_version'].'</td>';
					echo '</tr>';
				}
				echo '</table></form><br>';
			}else{
				$ucms->cout("admin.updates.".$ext[$c].".no_update_available");
			}
		}
	}
}

include 'footer.php'; 


function do_update($p){
	global $ucms;
	if(!isset($_POST['extention'])){
		$file = ABSPATH."admin/update/update.zip";
		if(!isset($p['from-file'])){
			if(isset($p['reinstall'])){
				$ucmsver = UCMS_VERSION;
			}else{
				$verfile = UCMS_SITE_URL."/pub/version";
				$ucmsver = file($verfile);
				$ucmsver = $ucmsver[0];
				
			}
			$version = explode(" ", $ucmsver);
			$version = $version[0];
			$dir = 'ucms-'.$version;
			$ver = trim(mb_strtolower(preg_replace("/\s/", "-", $ucmsver), "UTF-8"));
			$update_file = UCMS_SITE_URL."/pub/$dir/ucms-$ver.zip";
			$file_headers = @get_headers($update_file);
			if($file_headers[0] == 'HTTP/1.1 404 Not Found') {
				$exists = false;
			}else {
				$exists = true;
			}
			if($exists){
				if(!is_dir(ABSPATH."admin/update"))
					mkdir(ABSPATH."admin/update");
				@copy($update_file, $file);
			}
		}
		$zip = new ZipArchive();
		$res = $zip->open($file);
		if($res === TRUE){
			$zip->extractTo(ABSPATH);
			$zip->close();
			$ucms->remove_dir(ABSPATH."admin/update");
			if(MODULES_ENABLED)
				$_SESSION['modules_enabled'] = true;
			unset($_SESSION['ucms-update-available']);
			header("Location: ".SITE_DOMAIN.UCMS_DIR."/sys/install/index.php");
			return true;
		}
		header("Location: update.php?alert=error_opening_update");
	}else{
		if(empty($_POST['item'])){
			header("Location: update.php?alert=no_item_selected");
			return false;
		}
		foreach ($_POST['item'] as $extention) {
			switch ($_POST['extention']) {
				case 'themes':
					global $theme;
					$location = $theme->get('updates_location', $extention);
					$path = ABSPATH.UC_THEMES_PATH;
				break;
				
				case 'widgets':
					global $widget;
					$location = $widget->get('updates_location', $extention);
					$path = ABSPATH.WIDGETS_PATH;
				break;
	
				case 'plugins':
					global $plugin;
					$location = $plugin->get('updates_location', $extention);
					$path = ABSPATH.PLUGINS_PATH;
				break;
	
				case 'modules':
					$location = get_module('updates_location', $extention);
					$path = ABSPATH.MODULES_PATH;
				break;
			}
			$location = substr($location, -1) != '/' ? $location.'/' : $location;
			if(!preg_match("#(http://)#", $location)){
				$location = "http://".$location;
			}

			$update_file = $location.$extention.'.zip';

			$file_headers = @get_headers($update_file);
			if($file_headers[0] == 'HTTP/1.1 404 Not Found') {
				$exists = false;
			}else {
				$exists = true;
			}
			$file = ABSPATH."admin/update/$extention.zip";
			if($exists){
				if(!is_dir(ABSPATH."admin/update"))
					mkdir(ABSPATH."admin/update");
				@copy($update_file, $file);
			}
			$zip = new ZipArchive();
			$res = $zip->open($file);
			if($res === TRUE){
				$zip->extractTo($path.$extention);
				$zip->close();
			}else{
				header("Location: update.php?alert=error_opening_update");
				return false;
			}
		}
		$ucms->remove_dir(ABSPATH."admin/update");
		header("Location: ".SITE_DOMAIN.'/'.UCMS_DIR."admin/update.php?alert=success");
		return true;
	}
	return false;
}

function check_update_file($file){
	global $ucms;
	$zip = new ZipArchive();
	if(!empty($file['tmp_name'])){
		$res = $zip->open($file['tmp_name']);
		if($res === TRUE){
			$dir = ABSPATH.UCMS_DIR."admin/update";
			$package_data = $zip->extractTo(ABSPATH.UCMS_DIR."admin/update", array('update', 'index.php'));
			if($package_data){
				$version = file($dir.'/update');
				$version = $version[0];
				$zip->deleteName('update');
				$zip->close();
				move_uploaded_file($file['tmp_name'], $dir.'/update.zip');
				$ucms->cout("admin.updates.new_version_in_file", false, $version);
				?>
				<br><br>
				<form action="update.php" method="post">
					<input type="hidden" name="from-file">
					<input class="ucms-button-submit" type="submit" name="do-update" value="<?php $ucms->cout("admin.updates.submit.button"); ?>">
				</form>
				<?php
				return true;
			}
			$zip->close();
		}
	}
	header("Location: update.php?alert=incorrect_file");
	return false;
}

function get_update_version($file = ''){
	if($file == '')
		$file = UCMS_SITE_URL."/pub/version";
	$file_headers = @get_headers($file);
	$strings = @file($file);
	if(!empty($strings[0])) return $strings[0];
	return false;
}

function update_available($type = ''){
	if($type == ""){
		if(get_update_version() != UCMS_VERSION){	
			return true;
		}
	}else{
		if(!is_activated_module($type)) return false;
		switch ($type) {
			case 'themes':
				global $theme;
				$extentions = $theme->get_themes();
				
			break;

			case 'widgets':
				global $widget;
				$extentions = $widget->get_widgets();
			break;

			case 'plugins':
				global $plugin;
				$extentions = $plugin->get_plugins();
			break;

			case 'modules':
				$extentions = get_modules();
			break;
		}
		foreach ($extentions as $extention) {
			if($extention['updates_location'] != ''){
				$location = substr($extention['updates_location'], -1) != '/' ? $extention['updates_location'].'/' : $extention['updates_location'];
				if(!preg_match("#(http://)#", $location)){
					$location = "http://".$location;
				}
				if( $extention['version'] != get_update_version($location.'version') and get_update_version($location.'version') !== false )
					return true;
			}
		}
	}
	return false;
}

function get_update_file(){
	$version = explode(" ", get_update_version());
	$version = $version[0];
	$dir = 'ucms-'.$version;
	$ver = trim(mb_strtolower(preg_replace("/\s/", "-", $strings[0]), "UTF-8"));
	return UCMS_SITE_URL."/pub/$dir/ucms-$ver.zip";
}

function get_updating_extentions($type){
	switch ($type) {
		case 'themes':
			global $theme;
			$extentions = $theme->get_themes();
		break;

		case 'widgets':
			global $widget;
			$extentions = $widget->get_widgets();
		break;

		case 'plugins':
			global $plugin;
			$extentions = $plugin->get_plugins();
		break;

		case 'modules':
			$extentions = get_modules();
		break;
	}
	if(!empty($extentions)){
		$updating_list = array();
		foreach ($extentions as $extention) {
			if($extention['updates_location'] != ''){
				$location = substr($extention['updates_location'], -1) != '/' ? $extention['updates_location'].'/' : $extention['updates_location'];
				if(!preg_match("#(http://)#", $location)){
					$location = "http://".$location;
				}
				if( $extention['version'] != get_update_version($location.'version') and get_update_version($location.'version') !== false ){
					$extention['new_version'] = get_update_version($location.'version');
					$updating_list[] = $extention; 
				}
			}
		}
		if(!empty($updating_list)) return $updating_list;
	}
	return false;
	
}
?>
