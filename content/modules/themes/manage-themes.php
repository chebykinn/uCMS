<?php
function manage_themes(){
	global $udb, $user, $theme, $ucms;
	if (isset($_POST['item']) and isset($_POST['actions'])){
		$items = array();
		$action = (int) $_POST['actions'];
		foreach ($_POST['item'] as $id) {
			if($user->has_access("themes", 4) and $id != 'ucms'){
				$items[] = $id;
			}
		}
		$ids = implode(',', $items);
		if (count($items) > 0) {
			switch ($action) {
				case 3:
					foreach ($items as $id) {
						delete_theme($id, false);
					}
					if (count($items) > 1) {
						header("Location: ".UCMS_DIR."/admin/manage.php?module=themes&alert=deleted_multiple");
					}else
						header("Location: ".UCMS_DIR."/admin/manage.php?module=themes&alert=deleted");
 					
				break;
				
			}
		}
	}	
		$perpage = 25;
		$themes_all = $theme->get_themes();
		$columns = array('name','author', 'date');
		$orderby = isset($_GET['orderby']) ? in_array($_GET['orderby'], $columns) ? $_GET['orderby'] : 'date' : 'date';
		$order = (isset($_GET['order']) and $_GET['order'] == 'desc') ? 'asc' : 'desc';
		
		$sort_func = "sort_by_".$orderby."_".$order;
		usort($themes_all, $sort_func);

		$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
		if($page <= 0) $page = 1;
		$count = count($themes_all);
		$pages_count = 0;

		if($count != 0){ 
			$pages_count = ceil($count / $perpage); 
			if ($page > $pages_count):
				$page = $pages_count;
			endif; 
			$start_pos = ($page - 1) * $perpage;
		}
		$themes = array_slice($themes_all, $start_pos, $perpage);
		$user_id = $user->get_user_id();

		$link1 = UCMS_DIR."/admin/manage.php?module=themes&amp;orderby=title&amp;order=".$order.(isset($_GET['page']) ? "&amp;page=".$page : "");
		$link2 = UCMS_DIR."/admin/manage.php?module=themes&amp;orderby=author&amp;order=".$order.(isset($_GET['page']) ? "&amp;page=".$page : "");
		$link3 = UCMS_DIR."/admin/manage.php?module=themes&amp;orderby=date&amp;order=".$order.(isset($_GET['page']) ? "&amp;page=".$page : "");
		$mark = $order == "asc" ? '↑' : '↓';
		?>
		<b><?php $ucms->cout("module.themes.total.label"); ?></b> <?php echo $count; ?><br><br>
		<b><?php $ucms->cout("module.themes.sort_by.label"); ?></b>
		 <a href="<?php echo $link1; ?>"><?php $ucms->cout("module.themes.sort_by.name.link"); echo $mark; ?></a>
		  | <a href="<?php echo $link2; ?>"><?php $ucms->cout("module.themes.sort_by.author.link"); echo $mark; ?></a>
		   | <a href="<?php echo $link3; ?>"><?php $ucms->cout("module.themes.sort_by.date.link"); echo $mark; ?></a>
		<br><br>
		<?php if($user->has_access("themes", 3)){ ?><b><?php $ucms->cout("module.themes.select_all.label"); ?></b> <input type="checkbox" name="select-all" value="1"><?php } ?>
		<br><br>
		<form action="manage.php?module=themes" method="post">
		<?php if($user->has_access("themes", 4)){ ?>
		<select name="actions" style="width: 250px;">
			<option><?php $ucms->cout("module.themes.selected.option"); ?></option>
			<option value="3"><?php $ucms->cout("module.themes.selected.delete.option"); ?></option>
		</select>
		<input type="submit" value="<?php $ucms->cout("module.themes.selected.apply.button"); ?>" class="ucms-button-submit">
		<?php } ?>
		<br>
		<?php

		if($pages_count > 1){
			echo "<br>";
			pages($page, $count, $pages_count, 15, false);
			echo '<br>';
		}?><br>
		<table class="themes">
		<?php
		if($themes){
			for($i = 0; $i < count($themes); $i+=4){
				echo "<tr>";
				for($j = 0; $j < 4; $j++){
					$id = $i + $j;
					if(isset($themes[$id]['dir'])){
						$is_installed = $themes[$id]['activated'] == 1 ? "(<b>".$ucms->cout("module.themes.current.label", true)."</b>)" : '';
						?></p>
							<td <?php if($themes[$id]['activated'] == 1) echo "class=\"activated\""; ?>><?php if($user->has_access("themes", 3)){ ?><p style="float:left;"><input type="checkbox" name="item[]" value="<?php echo $themes[$id]['dir']; ?>"></p><br><br><?php } ?>
								<img src="<?php if(file_exists("../".UC_THEMES_PATH.$themes[$id]['dir']."/screenshot.png")){ echo "../".UC_THEMES_PATH.$themes[$id]['dir']."/screenshot.png"; }else echo "images/noscreen.png"; ?>" width="150" height="150" alt="screenshot">
								<?php
								echo '<div class="info-block"><ul>';
								echo "<li><span>".$ucms->cout("module.themes.info.name.label", true)."</span>".$theme->get('local_name', $themes[$id]['dir'])." $is_installed</li>";
								echo "<li><span>".$ucms->cout("module.themes.info.version.label", true)."</span>".$themes[$id]['version']."</li>";
								echo "<li><span>".$ucms->cout("module.themes.info.author.label", true)."</span>".$themes[$id]['author']."</li>";
								echo "<li><span>".$ucms->cout("module.themes.info.site.label", true)."</span><a target=\"_blank\" href=\"".$themes[$id]['site']."\">".$themes[$id]['site']."</a></li>";
								echo "<li><span>".$ucms->cout("module.themes.info.description.label", true)."</span><div class=\"description\">".$theme->get('local_description', $themes[$id]['dir'])."</div></li>";
								echo "<li><span>".$ucms->cout("module.themes.info.directory.label", true)."</span>".$themes[$id]['dir']."</li>";
								echo '</ul></div><br>';
								$is_tryout_theme = (!empty($_SESSION['theme']) and $_SESSION['theme'] == $themes[$id]['dir']);
								if($user->has_access("themes", 2) and !$is_tryout_theme and $themes[$id]['activated'] == 0){ ?>
								<a target="_blank" href="<?php echo UCMS_DIR ?>/admin/manage.php?module=themes&amp;action=tryout&amp;id=<?php echo $themes[$id]['dir'];?>">
								<?php $ucms->cout("module.themes.preview_mode.button"); ?></a><?php } ?><br><br>
								<?php
									if(file_exists($theme->get("path", $themes[$id]['dir']).'settings.php')){
								 	?>
								 	<a href="<?php echo UCMS_DIR ?>/admin/settings.php?theme=<?php echo $themes[$id]['dir'] 
								 	?>"><?php $ucms->cout("module.themes.settings.button"); ?></a><br><br>
								 	<?php
								}
								?>
								<span class="actions">
								<?php if($user->has_access("themes", 3)){ if($themes[$id]['activated'] < 1){ 
									?><a class="install-button" href="<?php echo UCMS_DIR ?>/admin/manage.php?module=themes&amp;action=activate&amp;id=<?php echo $themes[$id]['dir'];?>">
								<?php $ucms->cout("module.themes.activate.button"); ?></a><?php }else{ 
								?><a class="install-button"><?php $ucms->cout("module.themes.activated.label"); ?></a><?php } 
								?><a class="edit-button" href="<?php echo UCMS_DIR ?>/admin/editor.php?type=themes&amp;action=edit&amp;dir=<?php echo $themes[$id]['dir']?>">
								<?php $ucms->cout("module.themes.edit.button"); ?></a><?php } 
								if($themes[$id]['dir'] != 'ucms' and $user->has_access("themes", 4)){ 
								?><a class="delete-button" href="<?php echo UCMS_DIR ?>/admin/manage.php?module=themes&amp;action=delete&amp;id=<?php echo $themes[$id]['dir'];?>">
								<?php $ucms->cout("module.themes.delete.button"); ?></a><?php } ?></span>
							</td>
						<?php
					}
				}
				echo "</tr>";
			}
		}else{
			?>
			<tr>
				<td colspan="9" style="text-align:center;"><?php $ucms->cout("module.themes.no_themes.label"); ?></td>
			</tr>
			<?php
		}
			echo '</table></form>';
}

function delete_theme($id, $alert = true){
	global $udb, $ucms, $theme;
	if(!$id){
		return false; 
	}
	if($id != UC_DEFAULT_THEME_DIR){
		if($theme->is_theme($id)){
			$ucms->remove_dir(ABSPATH.UC_THEMES_PATH.$id);
			if(THEMEDIR == $id){
				$ucms->update_setting("themedir", UC_DEFAULT_THEME_NAME);
				$ucms->update_setting("themename", UC_DEFAULT_THEME_DIR);
			}
			$event->do_actions("theme.deleted");
			if($alert)
				header("Location: ".UCMS_DIR."/admin/manage.php?module=themes&alert=deleted");
			
		}else{
			echo '<div class="error">'.$ucms->cout("module.themes.error.theme_is_not_found.label", true).'</div>';
		}
	}else{
		header("Location: ".UCMS_DIR."/admin/manage.php?module=themes");
	}
}

function activate_theme($id){
	if(!$id){
		return false;
	}else{
		global $udb, $ucms, $event, $theme;
		if($theme->is_theme($id)){
			if(THEMEDIR != $id){
				$ucms->update_setting("themedir", $id);
				$ucms->update_setting("themename", $theme->get("local_name", $id));
			}
			$event->do_actions("theme.activated");
			header("Location: ".UCMS_DIR."/admin/manage.php?module=themes&alert=activated");
		}else{
			echo '<div class="error">'.$ucms->cout("module.themes.error.theme_is_not_found.label", true).'</div>';
		}
	}
}

function add_theme($p){
	global $ucms, $udb, $event, $theme;
	$template = $_FILES['themearch']['tmp_name'];
	if(!empty($template)){
		$name = preg_replace('#(.zip)#', '', $_FILES['themearch']['name']);
		$zip = new ZipArchive();
		$file = $_FILES['themearch']['tmp_name'];
		$res = $zip->open($file);
		if($res === TRUE){
			if(is_dir("../".UC_THEMES_PATH."$name")){
				$strs = file("../".UC_THEMES_PATH."$name/themeinfo.txt");
				$vers1 = preg_replace("/(Version|Версия): /", '', $strs[1]);
				$zip->extractTo("../".UC_THEMES_PATH."$name-1");
				$zip->close();
				if(file_exists("../".UC_THEMES_PATH."$name-1/themeinfo.txt")){
					$strs2 = file("../".UC_THEMES_PATH."$name-1/themeinfo.txt");
					$vers2 = preg_replace("/(Version|Версия): /", '', $strs2[1]);
					if($vers2 != $vers1){
						$ucms->remove_dir("../".UC_THEMES_PATH."$name");
						rename("../".UC_THEMES_PATH."$name-1", "../".UC_THEMES_PATH."$name");
					}else{
						$ucms->remove_dir("../".UC_THEMES_PATH."$name-1");
					}
				}else{
					$ucms->remove_dir("../".UC_THEMES_PATH."$name-1");
				}
			}else{
		  	 	$zip->extractTo("../".UC_THEMES_PATH."$name");
		   		$zip->close();
		   	}
		   	$themeinfo = "../".UC_THEMES_PATH."$name/themeinfo.txt";
			if($theme->is_theme($name)){
				if(file_exists("../".UC_THEMES_PATH."$name/install.php")){
					define("OWNER_ID", "t:".$name);
					include_once "../".UC_THEMES_PATH."$name/install.php";
				}
				$event->do_actions("theme.added");
				header("Location: ".UCMS_DIR."/admin/manage.php?module=themes&alert=added");
			}else{
				echo "<div class=\"error\">".$ucms->cout("module.themes.error.no_theme_in_archive.label", true)."</div>";
				$ucms->remove_dir("../".UC_THEMES_PATH."$name");
			}
		}else{
   			echo "<div class=\"error\">".$ucms->cout("module.themes.error.error_opening_archive.label", true)."</div>";
		}
	}
}

?>