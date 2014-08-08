<?php
function manage_plugins(){
	global $udb, $user, $plugin, $ucms, $event;
	$activated_plugins = $plugin->get_activated_plugins();
	if (isset($_POST['item']) and isset($_POST['actions'])){
		$items = array();
		$action = (int) $_POST['actions'];
		foreach ($_POST['item'] as $id) {
			if($user->has_access("plugins", 4)){
				$items[] = $id;
			}
		}
		$ids = implode(',', $items);
		if (count($items) > 0) {
			switch ($action) {
				case 1:
					$plugins = array();
					$event->do_actions("plugin.before.activated_multiple", $items);
					foreach ($items as $id) {
						if($plugin->is_plugin($id)){
							$plugins[] = $id;
						}
					}
					$upd = $ucms->update_setting("activated_plugins", implode(",", array_merge($activated_plugins, $plugins)));
					header("Location: ".UCMS_DIR."/admin/manage.php?module=plugins&alert=activated_multiple".(isset($_GET['show_all']) ? "&show_all" : ""));
				break;

				case 2:
					$event->do_actions("plugin.before.deactivated_multiple", $items);
					$plugins = $activated_plugins;
					foreach ($items as $id) {
						if($plugin->is_activated_plugin($id)){
							$key = array_search($id, $activated_plugins);
							unset($plugins[$key]);
						}
					}
					$upd = $ucms->update_setting("activated_plugins", implode(",", $plugins));
					header("Location: ".UCMS_DIR."/admin/manage.php?module=plugins&alert=deactivated_multiple".(isset($_GET['show_all']) ? "&show_all" : ""));
				break;

				case 3:
					$event->do_actions("plugin.before.deleted_multiple", $items);
					foreach ($items as $id) {
						delete_plugin($id, false);
					}
					header("Location: ".UCMS_DIR."/admin/manage.php?module=plugins&alert=deleted_multiple".(isset($_GET['show_all']) ? "&show_all" : ""));
				break;
				
			}
			unset($_SESSION['silent']);
		}
	}	
		$perpage = 25;
		$plugins_all = array();
		$plugins_all = $plugin->get_plugins();
		$columns = array('name','author', 'date');
		$orderby = isset($_GET['orderby']) ? in_array($_GET['orderby'], $columns) ? $_GET['orderby'] : 'date' : 'date';
		$order = (isset($_GET['order']) and $_GET['order'] == 'desc') ? 'asc' : 'desc';
		
		$sort_func = "sort_by_".$orderby."_".$order;

		usort($plugins_all, $sort_func);
		$default_plugins = $plugin->get_default_plugins();

		$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
		if($page <= 0) $page = 1;
		$count = count($plugins_all);
		if(!isset($_GET['show_all'])) $count -= count($default_plugins);
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
		$plugins = array_slice($plugins_all, $start_pos, $perpage);
		$user_id = $user->get_user_id();
		
		$link1 = UCMS_DIR."/admin/manage.php?module=plugins&amp;orderby=name&amp;order=".$order.(isset($_GET['page']) ? "&amp;page=".$_GET['page'] : "")
		.(isset($_GET['show_all']) ? "&amp;show_all" : "");
		$link2 = UCMS_DIR."/admin/manage.php?module=plugins&amp;orderby=author&amp;order=".$order.(isset($_GET['page']) ? "&amp;page=".$_GET['page'] : "")
		.(isset($_GET['show_all']) ? "&amp;show_all" : "");
		$link3 = UCMS_DIR."/admin/manage.php?module=plugins&amp;orderby=date&amp;order=".$order.(isset($_GET['page']) ? "&amp;page=".$_GET['page'] : "")
		.(isset($_GET['show_all']) ? "&amp;show_all" : "");
		$mark = $order == "asc" ? '↑' : '↓';
		?>
		<b><?php $ucms->cout("module.plugins.total.label"); ?></b><?php echo $count; ?><br><br>
		<b><?php $ucms->cout("module.plugins.sort_by.label"); ?></b><a href="<?php echo $link1; ?>"><?php $ucms->cout("module.plugins.sort_by.name"); ?><?php echo $mark; ?></a>
		 | <a href="<?php echo $link2; ?>"><?php $ucms->cout("module.plugins.sort_by.author"); ?><?php echo $mark; ?></a>
		 | <a href="<?php echo $link3; ?>"><?php $ucms->cout("module.plugins.sort_by.date"); ?><?php echo $mark; ?></a>
		<br><br>
		<?php if($user->has_access("plugins", 3)){ ?><b><?php $ucms->cout("module.plugins.select_all.label"); ?></b> <input type="checkbox" name="select-all" value="1"><?php } ?>
		<br><br>
		<form action="manage.php?module=plugins<?php echo (isset($_GET['show_all']) ? "&amp;show_all" : ""); ?>" method="post">
		<?php if($user->has_access("plugins", 3)){ ?>
		<select name="actions" style="width: 250px;">
			<option><?php $ucms->cout("module.plugins.selected.option"); ?></option>
			<option value="1"><?php $ucms->cout("module.plugins.selected.activate.option"); ?></option>
			<option value="2"><?php $ucms->cout("module.plugins.selected.deactivate.option"); ?></option>
			<?php if($user->has_access("plugins", 4)){ ?><option value="3"><?php $ucms->cout("module.plugins.selected.delete.option"); ?></option><?php } ?>
		</select>
		<input type="submit" value="<?php $ucms->cout("module.plugins.selected.apply.button"); ?>" class="ucms-button-submit">
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
		if(!isset($_GET['show_all'])){
			$c_plugins = count($plugins);
			for ($i = 0; $i < $c_plugins; $i++) { 
				if(in_array($plugins[$i]['dir'], $default_plugins)){
					unset($plugins[$i]);
				}
			}
			usort($plugins, $sort_func);
		}
		if($plugins and $count > 0){
			for($i = 0; $i < $count; $i+=4){
				echo "<tr>";
				for($j = 0; $j < 4; $j++){
					$id = $i + $j;
					if(isset($plugins[$id]['name'])){
						?></p>
							<td <?php if($plugins[$id]['activated']) echo "class=\"activated\""; ?> > <?php if($user->has_access("plugins", 3)){ ?><p style="float:left;"><input type="checkbox" name="item[]" value="<?php echo $plugins[$id]['dir']; ?>"></p><br><br><?php } ?>
								<?php
								echo '<div class="info-block"><ul>';
								echo "<li><span>".$ucms->cout("module.plugins.info.name.label", true)."</span>".$plugin->get('local_name', $plugins[$id]['dir'])."</li>";
								echo "<li><span>".$ucms->cout("module.plugins.info.version.label", true)."</span>".$plugins[$id]['version']."</li>";
								echo "<li><span>".$ucms->cout("module.plugins.info.author.label", true)."</span>".$plugins[$id]['author']."</li>";
								echo "<li><span>".$ucms->cout("module.plugins.info.site.label", true)."</span><a target=\"_blank\" href=\"".$plugins[$id]['site']."\">".$plugins[$id]['site']."</a></li>";
								echo "<li><span>".$ucms->cout("module.plugins.info.description.label", true)."</span><div class=\"description\">".$plugin->get('local_description', $plugins[$id]['dir'])."</div></li>";
								echo "<li><span>".$ucms->cout("module.plugins.info.dir.label", true)."</span>".$plugins[$id]['dir']."</li>";
								echo '</ul></div><br>';
								if(file_exists($plugin->get("path", $plugins[$id]['dir']).'settings.php') and $plugins[$id]['activated']){
								 	?>
								 	<a href="<?php echo UCMS_DIR ?>/admin/settings.php?plugin=<?php echo $plugins[$id]['dir'] 
								 	?>"><?php $ucms->cout("module.plugins.settings.button"); ?></a>
								 	<?php
								}else{
									echo "<br>";
								}
								?>
								<div class="mactions">
									<?php if($user->has_access("plugins", 3)){ 
									?><a class="install-button" href="<?php echo UCMS_DIR ?>/admin/manage.php?module=plugins&amp;action=<?php
									echo (!$plugins[$id]['activated'] ? "activate" : "deactivate"); ?>&amp;id=<?php echo $plugins[$id]['dir']
									.(isset($_GET['show_all']) ? "&amp;show_all" : "");?>"><?php
									if($plugins[$id]['activated'] < 1){ 
										?><?php $ucms->cout("module.plugins.manage.activate.button");
									}else{ 
										?><?php $ucms->cout("module.plugins.manage.deactivate.button"); } ?></a><a class="edit-button" href="<?php echo UCMS_DIR ?>/admin/editor.php?type=plugins&amp;action=edit&amp;dir=<?php
									echo $plugins[$id]['dir']?>"><?php $ucms->cout("module.plugins.manage.edit.button"); ?></a><?php
									} 
									if(!in_array($plugins[$id]['dir'], $default_plugins) and $user->has_access("plugins", 4)){ 
									?><a class="delete-button" href="<?php echo UCMS_DIR ?>/admin/manage.php?module=plugins&amp;action=delete&amp;id=<?php
									echo $plugins[$id]['dir']
									.(isset($_GET['show_all']) ? "&amp;show_all" : "");?>"><?php $ucms->cout("module.plugins.manage.delete.button"); ?></a><?php } ?>
								</div>
							</td>
						<?php
					}
				}
				echo "</tr>";
			}
		}else{
			?>
			<tr>
				<td colspan="9" style="text-align:center;"><?php $ucms->cout("module.plugins.no_plugins.label"); ?></td>
			</tr>
			<?php
		}
			echo '</table></form>';
}

function delete_plugin($id, $notify = true){
	global $udb, $ucms, $plugin, $event;
	if(!$id){
		return false; 
	}
	$default_plugins = $plugin->get_default_plugins();
	if(!in_array($id, $default_plugins)){
		if($plugin->is_plugin($id)){
			deactivate_plugin($id);
			$ucms->remove_dir(ABSPATH.PLUGINS_PATH.$id);
			$event->do_actions("plugin.deleted");
			if($notify){
				header("Location: ".UCMS_DIR."/admin/manage.php?module=plugins&alert=deleted".(isset($_GET['show_all']) ? "&show_all" : ""));
			}
		}else{
			header("Location: ".UCMS_DIR."/admin/manage.php?module=plugins".(isset($_GET['show_all']) ? "&show_all" : ""));
		}
	}
}

function activate_plugin($id, $notify = true){
	if(!$id){
		return false;
	}else{
		global $udb, $plugin, $ucms, $event;
		$plugins = $plugin->get_activated_plugins();
		if(!$plugin->is_activated_plugin($id)){
				$plugins[] = $id;
				$upd = $ucms->update_setting("activated_plugins", implode(",", $plugins));
			$event->do_actions("plugin.activated", array($id));
			if($notify){
				if($upd){
					header("Location: ".UCMS_DIR."/admin/manage.php?module=plugins&alert=activated".(isset($_GET['show_all']) ? "&show_all" : ""));
				}else
					echo "<div class=\"error\">".$ucms->cout("module.plugins.error.unknown.label", true)."</div>";
			}else{
				return true;
			}
		}else{
			header("Location: ".UCMS_DIR."/admin/manage.php?module=plugins".(isset($_GET['show_all']) ? "&show_all" : ""));
		}
	}
}

function deactivate_plugin($id, $notify = true){
	if(!$id){
		return false;
	}else{
		global $udb, $plugin, $ucms, $event;
		$plugins = $plugin->get_activated_plugins();
		if($plugin->is_plugin($id)){
			$key = array_search($id, $plugins);
			$name = $plugins[$key];
			unset($plugins[$key]);
			$upd = $ucms->update_setting("activated_plugins", implode(",", $plugins));
			$event->do_actions("plugin.deactivated", array($name));
			if($notify){
				if($upd){
					header("Location: ".UCMS_DIR."/admin/manage.php?module=plugins&alert=deactivated".(isset($_GET['show_all']) ? "&show_all" : ""));
				}else
					echo "<div class=\"error\">".$ucms->cout("module.plugins.error.unknown.label", true)."</div>";
			}
		}else{
			header("Location: ".UCMS_DIR."/admin/manage.php?module=plugins".(isset($_GET['show_all']) ? "&show_all" : ""));
		}
	}
}

function add_plugin($p){
	global $ucms, $udb, $event, $plugin;
	$template = $_FILES['pluginarch']['tmp_name'];
	if(!empty($template)){
		$name = preg_replace('#(.zip)#', '', $_FILES['pluginarch']['name']);
		$event->do_actions("plugin.before.added", array($name));
		$zip = new ZipArchive();
		$file = $_FILES['pluginarch']['tmp_name'];
		$res = $zip->open($file);
		if($res === TRUE){
			if(is_dir("../".PLUGINS_PATH."$name")){
				$strs = file("../".PLUGINS_PATH."$name/plugininfo.txt");
				$vers1 = preg_replace("#(Версия: )#", '', $strs[1]);
				$zip->extractTo("../".PLUGINS_PATH."$name-1");
				$zip->close();
				if(file_exists("../".PLUGINS_PATH."$name-1/plugininfo.txt")){
					$strs2 = file("../".PLUGINS_PATH."$name-1/plugininfo.txt");
					$vers2 = preg_replace("#(Версия: )#", '', $strs2[1]);
					if($vers2 != $vers1){
						$ucms->remove_dir("../".PLUGINS_PATH."$name");
						rename("../".PLUGINS_PATH."$name-1", "../".PLUGINS_PATH."$name");
					}else{
						$ucms->remove_dir("../".PLUGINS_PATH."$name-1");
					}
				}else{
					$ucms->remove_dir("../".PLUGINS_PATH."$name-1");
				}
			}else{
				$zip->extractTo("../".PLUGINS_PATH."$name");
				$zip->close();
			}
			if($plugin->is_plugin($name)){
				if(file_exists("../".PLUGINS_PATH."$name/install.php")){
					define("OWNER_ID", "p:".$name);
					include_once "../".PLUGINS_PATH."$name/install.php";
				}
				$event->do_actions("plugin.added", array($name));
				header("Location: ".UCMS_DIR."/admin/manage.php?module=plugins&alert=added".(isset($_GET['show_all']) ? "&show_all" : ""));
			}else{
				echo "<div class=\"error\">".$ucms->cout("module.plugins.error.no_plugin_in_archive.label", true)."</div>";
				$ucms->remove_dir("../".PLUGINS_PATH."$name");
			}
		}else{
			echo "<div class=\"error\">".$ucms->cout("module.plugins.error.opening_archive.label", true)."</div>";
		}
	}
}
?>