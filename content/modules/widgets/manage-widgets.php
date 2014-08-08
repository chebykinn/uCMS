<?php

function manage_widgets(){
	global $udb, $user, $widget, $ucms;
	$activated_widgets = $widget->get_activated_widgets();
	if (isset($_POST['item']) and isset($_POST['actions'])){
		$items = array();
		$action = (int) $_POST['actions'];
		foreach ($_POST['item'] as $id) {
			if($user->has_access("widgets", 3)){
				$items[] = $id;
			}
		}
		$ids = implode(',', $items);
		if (count($items) > 0) {
			switch ($action) {
				case 1:
					$widgets = array();
					foreach ($items as $id) {
						if($widget->is_widget($id)){
							$widgets[] = $id;
						}
					}
					$upd = $ucms->update_setting("activated_widgets", implode(",", array_merge($activated_widgets, $widgets)));
					header("Location: ".UCMS_DIR."/admin/manage.php?module=widgets&alert=activated_multiple");
				break;

				case 2:
					$widgets = $activated_widgets;
					foreach ($items as $id) {
						if($widget->is_activated_widget($id)){
							$key = array_search($id, $activated_widgets);
							unset($widgets[$key]);
						}
					}
					$upd = $ucms->update_setting("activated_widgets", implode(",", $widgets));
					header("Location: ".UCMS_DIR."/admin/manage.php?module=widgets&alert=deactivated_multiple");
				break;

				case 3:
					if($user->has_access("widgets", 4)){
						foreach ($items as $id) {
							delete_widget($id, false);
						}
						header("Location: ".UCMS_DIR."/admin/manage.php?module=widgets&alert=deleted_multiple");
					}
				break;
				
			}
		}
	}	
	$perpage = 25;
	$widgets_all = $widget->get_widgets();
	$columns = array('name','author', 'date');
	$orderby = isset($_GET['orderby']) ? in_array($_GET['orderby'], $columns) ? $_GET['orderby'] : 'date' : 'date';
	$order = (isset($_GET['order']) and $_GET['order'] == 'desc') ? 'asc' : 'desc';
	
	$sort_func = "sort_by_".$orderby."_".$order;
	usort($widgets_all, $sort_func);

	$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
	if($page <= 0) $page = 1;
	$count = count($widgets_all);
	$pages_count = 0;

	if($count != 0){ 
		$pages_count = ceil($count / $perpage); 
		if ($page > $pages_count):
			$page = $pages_count;
		endif; 
		$start_pos = ($page - 1) * $perpage;
	}
	$widgets = array_slice($widgets_all, $start_pos, $perpage);
	$default_widgets = $widget->get_default_widgets();
	$user_id = $user->get_user_id();
	
	$link1 = UCMS_DIR."/admin/manage.php?module=widgets&amp;orderby=name&amp;order=".$order.(isset($_GET['page']) ? "&amp;page=".$page : "");
	$link2 = UCMS_DIR."/admin/manage.php?module=widgets&amp;orderby=author&amp;order=".$order.(isset($_GET['page']) ? "&amp;page=".$page : "");
	$link3 = UCMS_DIR."/admin/manage.php?module=widgets&amp;orderby=date&amp;order=".$order.(isset($_GET['page']) ? "&amp;page=".$page : "");
	$mark = $order == "asc" ? '↑' : '↓';
	?>
	<b><?php $ucms->cout("module.widgets.total.label"); ?></b><?php echo $count; ?><br><br>
	<b><?php $ucms->cout("module.widgets.sort_by.label"); ?></b><a href="<?php echo $link1; ?>"><?php $ucms->cout("module.widgets.sort_by.name.label"); echo $mark; ?></a>
	 | <a href="<?php echo $link2; ?>"><?php $ucms->cout("module.widgets.sort_by.author.label"); echo $mark; ?></a>
	 | <a href="<?php echo $link3; ?>"><?php $ucms->cout("module.widgets.sort_by.date.label"); echo $mark; ?></a>
	<br><br>
	<?php if($user->has_access("widgets", 3)){ ?><b><?php $ucms->cout("module.widgets.select_all.label"); ?></b> <input type="checkbox" name="select-all" value="1"><?php } ?>
	<br><br>
	<form action="manage.php?module=widgets" method="post">
	<?php if($user->has_access("widgets", 3)){ ?>
	<select name="actions" style="width: 250px;">
		<option><?php $ucms->cout("module.widgets.selected.option"); ?></option>
		<option value="1"><?php $ucms->cout("module.widgets.selected.activate.option"); ?></option>
		<option value="2"><?php $ucms->cout("module.widgets.selected.deactivate.option"); ?></option>
		<?php if($user->has_access("widgets", 4)){ ?><option value="3"><?php $ucms->cout("module.widgets.selected.delete.option"); ?></option><?php } ?>
	</select>
	<input type="submit" value="<?php $ucms->cout("module.widgets.selected.apply.button"); ?>" class="ucms-button-submit">
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
	if($widgets){
		for($i = 0; $i < count($widgets); $i+=4){
			echo "<tr>";
			for($j = 0; $j < 4; $j++){
				$id = $i + $j;
				if(isset($widgets[$id]['dir'])){
					?></p>
						<td <?php if($widgets[$id]['activated']) echo "class=\"activated\""; ?>><p style="float:left;"><?php if($user->has_access("widgets", 3)){ ?><input type="checkbox" name="item[]" value="<?php echo $widgets[$id]['dir']; ?>"></p><br><br><?php } ?>
							<?php
							echo '<div class="block-info"><ul>';
							echo "<li><span>".$ucms->cout("module.widgets.info.name.label", true)."</span>".$widget->get('local_name', $widgets[$id]['dir'])."</li>";
							echo "<li><span>".$ucms->cout("module.widgets.info.version.label", true)."</span>".$widgets[$id]['version']."</li>";
							echo "<li><span>".$ucms->cout("module.widgets.info.author.label", true)."</span>".$widgets[$id]['author']."</li>";
							echo "<li><span>".$ucms->cout("module.widgets.info.site.label", true)."</span><a target=\"_blank\" href=\"".$widgets[$id]['site']."\">".$widgets[$id]['site']."</a></li>";
							echo "<li><span>".$ucms->cout("module.widgets.info.description.label", true)."</span><div class=\"description\">".$widget->get('local_description', $widgets[$id]['dir'])."</div></li>";
							echo "<li><span>".$ucms->cout("module.widgets.info.directory.label", true)."</span>".$widgets[$id]['dir']."</li>";
							echo '</ul></div><br>';
							if(file_exists($widget->get("path", $widgets[$id]['dir']).'settings.php') and $widgets[$id]['activated']){
							 	?>
							 	<a href="<?php echo UCMS_DIR ?>/admin/settings.php?widget=<?php echo $widgets[$id]['dir'] 
							 	?>"><?php $ucms->cout("module.widgets.settings.button"); ?></a>
							 	<?php
							}else{
								echo "<br>";
							}
							?>
							<div class="mactions"><?php if($user->has_access("widgets", 3)){ 
							?><a class="install-button" href="<?php echo UCMS_DIR ?>/admin/manage.php?module=widgets&amp;action=<?php
							echo (!$widgets[$id]['activated'] ? "activate" : "deactivate"); ?>&amp;id=<?php echo $widgets[$id]['dir'];?>"><?php 
							if($widgets[$id]['activated'] < 1){ 
								$ucms->cout("module.widgets.activate.button"); 
							}else{ 
								$ucms->cout("module.widgets.deactivate.button"); 
							} 
							?></a><a class="edit-button" href="<?php echo UCMS_DIR ?>/admin/editor.php?type=widgets&amp;action=edit&amp;dir=<?php 
							echo $widgets[$id]['dir']?>"><?php $ucms->cout("module.widgets.edit.button"); ?></a><?php }
							if(!in_array($widgets[$id]['dir'], $default_widgets) and $user->has_access("widgets", 4)){ 
							?><a class="delete-button" href="<?php echo UCMS_DIR ?>/admin/manage.php?module=widgets&amp;action=delete&amp;id=<?php 
							echo $widgets[$id]['dir'];?>"><?php $ucms->cout("module.widgets.delete.button"); ?></a><?php } ?></div>
						</td>
					<?php
				}
			}
			echo "</tr>";
		}
	}else{
		?>
		<tr>
			<td colspan="9" style="text-align:center;"><?php $ucms->cout("module.widgets.no_widgets.label"); ?></td>
		</tr>
		<?php
	}
		echo '</table></form>';
}

function delete_widget($id, $notify = true){
	global $udb, $ucms, $widget, $event;
	if(!$id){
		return false; 
	}
	$default_widgets = $widget->get_default_widgets();
	if(!in_array($id, $default_widgets)){
		if($widget->is_widget($id)){
			deactivate_widget($id);
			$ucms->remove_dir(ABSPATH.WIDGETS_PATH.$id);
			$event->do_actions("widget.deleted");
			if($notify){
				header("Location: ".UCMS_DIR."/admin/manage.php?module=widgets&alert=deleted");
			}
		}else{
			header("Location: ".UCMS_DIR."/admin/manage.php?module=widgets");
		}
	}
}

function activate_widget($id, $notify = true){
	if(!$id){
		return false;
	}else{
		global $udb, $widget, $ucms, $event;
		$widgets = $widget->get_activated_widgets();
		if(!$widget->is_activated_widget($id)){
				$widgets[] = $id;
				$upd = $ucms->update_setting("activated_widgets", implode(",", $widgets));
			$event->do_actions("widget.activated");
			if($notify){
				if($upd){
					header("Location: ".UCMS_DIR."/admin/manage.php?module=widgets&alert=activated");
				}else
					echo "<div class=\"error\">".$ucms->cout("module.widgets.error.unknown.label", true)."</div>";
			}else{
				return true;
			}
		}else{
			header("Location: ".UCMS_DIR."/admin/manage.php?module=widgets");
		}
	}
}

function deactivate_widget($id, $notify = true){
	if(!$id){
		return false;
	}else{
		global $udb, $widget, $ucms, $event;
		$widgets = $widget->get_activated_widgets();
		if($widget->is_widget($id)){
			$key = array_search($id, $widgets);
			unset($widgets[$key]);
			$upd = $ucms->update_setting("activated_widgets", implode(",", $widgets));
			$event->do_actions("widget.deactivated");
			if($notify){
				if($upd){
					header("Location: ".UCMS_DIR."/admin/manage.php?module=widgets&alert=deactivated");
				}else
					echo "<div class=\"error\">".$ucms->cout("module.widgets.error.unknown.label", true)."</div>";
			}
		}else{
			header("Location: ".UCMS_DIR."/admin/manage.php?module=widgets");
		}
	}
}

function add_widget($p){
	global $ucms, $udb, $event, $widget;
	$template = $_FILES['widgetarch']['tmp_name'];
	if(!empty($template)){
		$name = preg_replace('#(.zip)#', '', $_FILES['widgetarch']['name']);
		$zip = new ZipArchive();
		$file = $_FILES['widgetarch']['tmp_name'];
		$res = $zip->open($file);
		if($res === TRUE){
			if(is_dir("../".WIDGETS_PATH."$name")){
				$strs = file("../".WIDGETS_PATH."$name/widgetinfo.txt");
				$vers1 = preg_replace("#(Версия: )#", '', $strs[1]);
				$zip->extractTo("../".WIDGETS_PATH."$name-1");
				$zip->close();
				if(file_exists("../".WIDGETS_PATH."$name-1/widgetinfo.txt")){
					$strs2 = file("../".WIDGETS_PATH."$name-1/widgetinfo.txt");
					$vers2 = preg_replace("#(Версия: )#", '', $strs2[1]);
					if($vers2 != $vers1){
						$ucms->remove_dir("../".WIDGETS_PATH."$name");
						rename("../".WIDGETS_PATH."$name-1", "../".WIDGETS_PATH."$name");
					}else{
						$ucms->remove_dir("../".WIDGETS_PATH."$name-1");
					}
				}else{
					$ucms->remove_dir("../".WIDGETS_PATH."$name-1");
				}
			}else{
				$zip->extractTo("../".WIDGETS_PATH."$name");
				$zip->close();
			}
			if($widget->is_widget($name)){
				if(file_exists("../".WIDGETS_PATH."$name/install.php")){
					define("OWNER_ID", "w:".$name);
					include_once "../".WIDGETS_PATH."$name/install.php";
				}
				$event->do_actions("widget.added", array($name));
				header("Location: ".UCMS_DIR."/admin/manage.php?module=widgets&alert=added");
			}else{
				echo "<div class=\"error\">".$ucms->cout("module.widgets.error.no_widget_in_archive.label", true)."</div>";
				$ucms->remove_dir("../".WIDGETS_PATH."$name");
			}
		}else{
			echo "<div class=\"error\">".$ucms->cout("module.widgets.error.opening_archive.label", true)."</div>";
		}
	}
}
?>