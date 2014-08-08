<?php
function manage_modules(){
	global $udb, $user, $ucms;
	$activated_modules = get_activated_modules();
	if (isset($_POST['item']) and isset($_POST['actions'])){
		$items = array();
		$action = (int) $_POST['actions'];
		foreach ($_POST['item'] as $id) {
			$id = $id;
			if($user->has_access("system")){
				$items[] = $id;
			}
		}
		$ids = implode(',', $items);
		if (count($items) > 0) {
			switch ($action) {
				case 1:
					$modules = array();
					foreach ($items as $id) {
						if(is_module($id)){
							$modules[] = $id;
						}
					}
					$upd = $ucms->update_setting("activated_modules", implode(",", array_merge($activated_modules, $modules)));
					header("Location: ".UCMS_DIR."/admin/modules.php?alert=activated_multiple".(isset($_GET['show_all']) ? "&show_all" : ""));
				break;

				case 2:
					$modules = $activated_modules;
					foreach ($items as $id) {
						if(is_activated_module($id)){
							$key = array_search($id, $activated_modules);
							unset($modules[$key]);
						}
					}
					$upd = $ucms->update_setting("activated_modules", implode(",", $modules));
					header("Location: ".UCMS_DIR."/admin/modules.php?alert=deactivated_multiple".(isset($_GET['show_all']) ? "&show_all" : ""));
				break;

				case 3:
					foreach ($items as $id) {
						delete_module($id);
					}
					if(!isset($_SESSION['no_del'])){
						if (count($items) > 1) {
							header("Location: ".UCMS_DIR."/admin/modules.php?alert=deleted_multiple".(isset($_GET['show_all']) ? "&show_all" : ""));
						}else 
 							header("Location: ".UCMS_DIR."/admin/modules.php?alert=deleted".(isset($_GET['show_all']) ? "&show_all" : ""));
					}
				break;
				
			}
		}
	}	
	$perpage = 25;
	$default_modules = get_default_modules();
	$modules = get_modules();
	
	$count = $modules ? count($modules) : 0;
	if(!isset($_GET['show_all'])) $count -= count($default_modules);
	$columns = array('name','author', 'date');
	$orderby = isset($_GET['orderby']) ? in_array($_GET['orderby'], $columns) ? $_GET['orderby'] : 'date' : 'date';
	$order = (isset($_GET['order']) and $_GET['order'] == 'desc') ? 'asc' : 'desc';
	
	$sort_func = "sort_by_".$orderby."_".$order;
	if($modules)
		usort($modules, $sort_func);

	$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
	if($page <= 0) $page = 1;
	
	$pages_count = 0;

	if($count != 0){ 
		$pages_count = ceil($count / $perpage); 
		if ($page > $pages_count):
			$page = $pages_count;
		endif; 
		$start_pos = ($page - 1) * $perpage;
	}else{
		$start_pos = 0;
	}
	if($modules)
		$modules = array_slice($modules, $start_pos, $perpage);
	$user_id = $user->get_user_id();

	$link1 = UCMS_DIR."/admin/modules.php?orderby=title&amp;order=".$order.(isset($_GET['page']) ? "&amp;page=".$_GET['page'] : "")
	.(isset($_GET['show_all']) ? "&amp;show_all" : "");
	$link2 = UCMS_DIR."/admin/modules.php?orderby=author&amp;order=".$order.(isset($_GET['page']) ? "&amp;page=".$_GET['page'] : "")
	.(isset($_GET['show_all']) ? "&amp;show_all" : "");
	$link3 = UCMS_DIR."/admin/modules.php?&amp;orderby=date&amp;order=".$order.(isset($_GET['page']) ? "&amp;page=".$_GET['page'] : "")
	.(isset($_GET['show_all']) ? "&amp;show_all" : "");
	$mark = $order == "asc" ? '↑' : '↓';
	?>
	<b><?php $ucms->cout("admin.modules.label.count"); ?></b> <?php echo $count; ?><br><br>
	<b><?php $ucms->cout("admin.modules.label.sort_by"); ?></b> <a href="<?php echo $link1; ?>"><?php $ucms->cout("admin.modules.sort.name"); ?> <?php echo $mark; ?></a> | <a href="<?php echo $link2; ?>"><?php $ucms->cout("admin.modules.sort.author"); ?> <?php echo $mark; ?></a> | <a href="<?php echo $link3; ?>"><?php $ucms->cout("admin.modules.sort.date"); ?> <?php echo $mark; ?></a>
	<br><br>
	<b><?php $ucms->cout("admin.modules.label.select_all"); ?></b> <input type="checkbox" name="select-all" value="1">
	<br><br>
	<form action="modules.php<?php echo (isset($_GET['show_all']) ? "?show_all" : ""); ?>" method="post">
	<?php if($user->has_access("system")){ ?>
	<select name="actions" style="width: 250px;">
		<option><?php $ucms->cout("admin.modules.option.selected"); ?></option>
		<option value="1"><?php $ucms->cout("admin.modules.option.activate"); ?></option>
		<option value="2"><?php $ucms->cout("admin.modules.option.deactivate"); ?></option>
		<option value="3"><?php $ucms->cout("admin.modules.option.delete"); ?></option>
	</select>
	<?php } ?>
	<input type="submit" value="<?php $ucms->cout("admin.modules.button.apply"); ?>" class="ucms-button-submit">
	<br>
	<?php
	if($pages_count > 1){
		echo "<br>";
		pages($page, $count, $pages_count, 15, false);
		echo '<br>';
	}?><br>
	<table class="themes">
	<?php
	if(!isset($_GET['show_all'])){
		$c_modules = count($modules);
		for ($i = 0; $i < $c_modules; $i++) { 
			if(in_array($modules[$i]['dir'], $default_modules)){
				unset($modules[$i]);
			}
		}
		usort($modules, $sort_func);
	}
	if($modules and $count > 0){
		for($i = 0; $i < $count; $i+=4){
			echo "<tr>";
			for($j = 0; $j < 4; $j++){
				$id = $i + $j;
				if(isset($modules[$id]['dir'])){
					?></p>
						<td <?php if($modules[$id]['activated'] == 1) echo "class=\"activated\""; ?>><p style="float:left;"><input type="checkbox" name="item[]" value="<?php echo $modules[$id]['dir']; ?>"></p><br><br>
							<?php
							echo '<div class="info-block"><ul>';
							echo "<li><span>".$ucms->cout("admin.modules.label.name", true)." </span>".get_module('local_name', $modules[$id]['dir'])."</li>";
							echo "<li><span>".$ucms->cout("admin.modules.label.version", true)." </span>".$modules[$id]['version']."</li>";
							echo "<li><span>".$ucms->cout("admin.modules.label.author", true)." </span>".$modules[$id]['author']."</li>";
							echo "<li><span>".$ucms->cout("admin.modules.label.site", true)." </span><a target=\"_blank\" href=\"".$modules[$id]['site']."\">".$modules[$id]['site']."</a></li>";
							echo "<li><span>".$ucms->cout("admin.modules.label.description", true)." </span><div class=\"description\">".get_module('local_description', $modules[$id]['dir'])."</div></li>";
							echo "<li><span>".$ucms->cout("admin.modules.label.dir", true)." </span>".$modules[$id]['dir']."</li>";
							echo '</ul></div><br>';
							if(file_exists(get_module("path", $modules[$id]['dir']).'settings.php') and $modules[$id]['activated'] == 1){
							 	?>
							 	<a href="<?php echo UCMS_DIR ?>/admin/settings.php?module=<?php echo $modules[$id]['dir'] 
							 	?>"><?php $ucms->cout("admin.modules.button.settings"); ?></a>
							 	<?php
							}else{
								echo "<br>";
							}
							?>
							<div class="mactions"><a class="install-button" href="<?php echo UCMS_DIR ?>/admin/modules.php?action=activate&amp;id=<?php echo $modules[$id]['dir']
							.(isset($_GET['show_all']) ? "&amp;show_all" : "");?>"><?php if(!$modules[$id]['activated']){ $ucms->cout("admin.modules.button.activate"); }else{ $ucms->cout("admin.modules.button.deactivate"); } ?></a><a class="edit-button" href="<?php echo UCMS_DIR ?>/admin/editor.php?type=modules&amp;action=edit&amp;dir=<?php echo $modules[$id]['dir'].(isset($_GET['show_all']) ? "&amp;show_all" : ""); ?>"><?php $ucms->cout("admin.modules.button.edit"); ?></a><?php if(!in_array($modules[$id]['dir'], get_default_modules())){ ?><a class="delete-button" href="<?php echo UCMS_DIR ?>/admin/modules.php?action=delete&amp;id=<?php echo $modules[$id]['dir'].(isset($_GET['show_all']) ? "&amp;show_all" : "");?>"><?php $ucms->cout("admin.modules.button.delete"); ?></a><?php } ?></div>
						</td>
					<?php
				}
			}
			echo "</tr>";
		}
	}else{
		?>
		<tr>
			<td colspan="9" style="text-align:center;"><?php $ucms->cout("admin.modules.no_modules.label"); ?></td>
		</tr>
		<?php
	}
		echo '</table></form>';
}

function delete_module($id){
	global $udb, $ucms;
	if(!$id || !is_module($id)){
		return false; 
	}
	if(!in_array($id, get_default_modules())){
		$ucms->remove_dir(ABSPATH.MODULES_PATH.$id);
		$modules = get_activated_modules();
		if(is_activated_module($id)){
			$key = array_search($id, $modules);
			unset($modules[$key]);
			$upd = $ucms->update_setting("activated_modules", implode(",", $modules));
		}
		header("Location: ".UCMS_DIR."/admin/modules.php?alert=deleted".(isset($_GET['show_all']) ? "&show_all" : ""));
	}
}

function activate_module($id){
	if(!$id || !is_module($id)){
		return false;
	}else{
		global $udb, $ucms;
		$modules = get_activated_modules();
		if(!is_activated_module($id)){
			$modules[] = $id;
			$alert = 'activated';
			$upd = $ucms->update_setting("activated_modules", implode(",", $modules));
		}else{
			$key = array_search($id, $modules);
			unset($modules[$key]);
			$alert = 'deactivated';
			$upd = $ucms->update_setting("activated_modules", implode(",", $modules));
		}
		if($upd){
			header("Location: ".UCMS_DIR."/admin/modules.php?alert=$alert".(isset($_GET['show_all']) ? "&show_all" : ""));
		}else
			echo "<div class=\"error\">".$ucms->cout("admin.modules.alert.error.unknown", true)."</div>";
	}
}

function add_module($p){
	global $ucms, $udb;
	$template = $_FILES['modulearch']['tmp_name'];
	if(!empty($template)){
		$name = preg_replace('#(.zip)#', '', $_FILES['modulearch']['name']);
		$zip = new ZipArchive();
		$file = $_FILES['modulearch']['tmp_name'];
		$res = $zip->open($file);
		if($res === TRUE){
			if(is_dir("../".MODULES_PATH."$name")){
				$strs = file("../".MODULES_PATH."$name/moduleinfo.txt");
				$vers1 = preg_replace("#(Версия: )#", '', $strs[1]);
				$zip->extractTo("../".MODULES_PATH."$name-1");
				$zip->close();
				if(file_exists("../".MODULES_PATH."$name-1/moduleinfo.txt")){
					$strs2 = file("../".MODULES_PATH."$name-1/moduleinfo.txt");
					$vers2 = preg_replace("#(Версия: )#", '', $strs2[1]);
					if($vers2 != $vers1){
						$ucms->remove_dir("../".MODULES_PATH."$name");
						rename("../".MODULES_PATH."$name-1", "../".MODULES_PATH."$name");
					}else{
						$ucms->remove_dir("../".MODULES_PATH."$name-1");
					}
				}else{
					$ucms->remove_dir("../".MODULES_PATH."$name-1");
				}
			}else{
		  	 	$zip->extractTo("../".MODULES_PATH."$name");
		   		$zip->close();
		   	}
			if(is_module($name)){
				if(file_exists("../".MODULES_PATH."$name/install.php")){
					define("OWNER_ID", "m:".$name);
					include_once "../".MODULES_PATH."$name/install.php";
				}
				header("Location: ".UCMS_DIR."/admin/modules.php?alert=added".(isset($_GET['show_all']) ? "&show_all" : ""));
			}else{
				echo "<div class=\"error\">".$ucms->cout("admin.modules.alert.error.no_module", true)."</div>";
				$ucms->remove_dir("../".MODULES_PATH."$name");
			}
		}else{
   			echo "<div class=\"error\">".$ucms->cout("admin.modules.alert.error.opening", true)."</div>";
		}
	}
}
?>