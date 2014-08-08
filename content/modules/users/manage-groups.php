<?php
	function add_group_form(){
		global $group, $ucms;
		?>
		<form action="manage.php?module=users&amp;section=groups" method="post">
			<table class="forms">
				<tr>
					<td><b><?php $ucms->cout("module.users.groups.form.name.label"); ?></b></td>
					<td><input name="name" type="text" required></td>
				</tr>
				<tr>
					<td><b><?php $ucms->cout("module.users.groups.form.alias.label"); ?></b></td>
					<td><input name="alias" type="text" ></td>
				</tr>
				<?php
					$permissions = $group->get_user_permissions();
					$modules = array_keys($permissions);
					for($i = 0; $i < count($permissions); $i++){
						$dir = $modules[$i];
						$name = get_module('local_name', $dir);
						if($name and is_activated_module($dir)){
							echo "<tr>";
							$module_path = ABSPATH.MODULES_PATH.$dir;
							$ucms->set_language($module_path.'/languages/'.SYSTEM_LANGUAGE.'.lang');
							echo "<td><b>$name</b></td>";
							echo "<td><select name=\"permissions[]\">";
							for($level = 0; $level < MAX_PERMISSION_LEVEL+1; $level++){
								if($ucms->is_language_string_id("module.$dir.permission.level_$level.label")){
									echo "<option value=\"$dir:$level\">".$ucms->cout("module.$dir.permission.level_$level.label", true)."</option>\n";
								}
							}
							echo "</select></td></tr>";
						}
					}
				?>
				<tr>
					<td colspan=2><input name="add" type="submit" value="<?php $ucms->cout("module.users.groups.form.add.button"); ?>" class="ucms-button-submit"></td>
				</tr>
			</table>
			<br><?php $ucms->cout("module.users.groups.form.important_note.label"); ?>
		</form>
		<?php
	}

	function add_group($p){
		global $udb, $ucms;
		if(isset($p['name']) and $p['name'] != ''){
			$gname = $udb->parse_value($p['name']);
			$galias = $udb->parse_value($p['alias']);
			if(empty($galias)){
				$galias = $ucms->transliterate($gname);
			}
			$galias = strtolower(preg_replace('/\s/', "_", $galias));
			$galias = strtolower(preg_replace('/[^a-zA-Zа-яА-Я0-9_-]/ui', "", $galias));
			$test = $udb->get_row("SELECT `id` FROM `".UC_PREFIX."groups` WHERE `alias` = '$galias'");
			if($test){
				$i = 0;
				$testalias = "";
				while($test){
					$i++;
					$testalias = $galias.'-'.$i;
					$test = $udb->get_row("SELECT `id` FROM `".UC_PREFIX."groups` WHERE `alias` = '$testalias'");
					$testalias = "";
				}
				$galias .= "-$i";
			}
			$permissions = implode(",", $p['permissions']);
			$add = $udb->query("INSERT INTO `".UC_PREFIX."groups` (`id`, `name`, `alias`, `permissions`) VALUES(NULL, '$gname', '$galias', '$permissions')");
			if($add){
				header("Location: ".UCMS_DIR."/admin/manage.php?module=users&section=groups&alert=added");
			}
		}
	}

	function update_group_form($id){
		global $udb, $group, $ucms;
		$id = (int) $id;
		$grp = $udb->get_row("SELECT * FROM `".UC_PREFIX."groups` WHERE `id` = '$id'");
		if($grp and $id > 1 and count($grp) > 0){
			$i = 1;
			$permissions = $grp['permissions'];
			$p_array = $group->get_permissions_array($permissions);
			$modules = array_keys($p_array);
		?>
		<form action="manage.php?module=users&amp;section=groups" method="post">
			<input type="hidden" name="id" value="<?php echo $grp['id']; ?>">
			<table class="forms">
				<tr>
					<td><b><?php $ucms->cout("module.users.groups.form.name.label"); ?></b></td>
					<td><input name="name" type="text" value="<?php echo $grp['name']; ?>" required></td>
				</tr>
				<tr>
					<td><b><?php $ucms->cout("module.users.groups.form.alias.label"); ?></b></td>
					<td><input name="alias" type="text" value="<?php echo $grp['alias']; ?>"></td>
				</tr>
				<?php

					for($i = 0; $i < count($p_array); $i++){
						$dir = $modules[$i];
						$name = get_module('local_name', $dir);
						if($name and is_activated_module($dir)){
							echo "<tr>";
							$module_path = ABSPATH.MODULES_PATH.$dir;
							$ucms->set_language($module_path.'/languages/'.SYSTEM_LANGUAGE.'.lang');
							echo "<td><b>$name</b></td>";
							echo "<td><select name=\"permissions[]\">";
							for($level = 0; $level < MAX_PERMISSION_LEVEL+1; $level++){
								if($ucms->is_language_string_id("module.$dir.permission.level_$level.label")){
									echo "<option value=\"$dir:$level\" ".($p_array[$dir] == $level ? "selected" : "").">".$ucms->cout("module.$dir.permission.level_$level.label", true)."</option>\n";
								}
							}
							echo "</select></td></tr>";
						}
					}
				?>
				<tr>
					<td colspan=2><input name="update" type="submit" value="<?php $ucms->cout("module.users.groups.form.update.button"); ?>" class="ucms-button-submit"></td>
				</tr>
			</table>
			<br><?php $ucms->cout("module.users.groups.form.important_note.label"); ?>
		</form>
		<?php
		}else header("Location: groups.php");
	}

	function update_group($p){
		global $udb, $ucms;
		$id = (int) $_POST['id'];
		if(isset($_POST['name']) and $_POST['name'] != '' and $id > 1){
			$gname = $udb->parse_value($_POST['name']);
			$galias = $udb->parse_value($p['alias']);
			if(empty($galias)){
				$galias = $ucms->transliterate($gname);
			}
			$galias = strtolower(preg_replace('/\s/', "_", $galias));
			$galias = strtolower(preg_replace('/[^a-zA-Zа-яА-Я0-9_-]/ui', "", $galias));
			$test = $udb->get_row("SELECT `id` FROM `".UC_PREFIX."groups` WHERE `alias` = '$galias'");
			if($test and $test['id'] != $id){
				$i = 0;
				$testalias = "";
				while($test){
					$i++;
					$testalias = $galias.'-'.$i;
					$test = $udb->get_row("SELECT `id` FROM `".UC_PREFIX."groups` WHERE `alias` = '$testalias'");
					$testalias = "";
				}
				$galias .= "-$i";
			}
			$permissions = implode(",", $p['permissions']);
			$upd = $udb->query("UPDATE `".UC_PREFIX."groups` SET `name` = '$gname', `alias` = '$galias', `permissions` = '$permissions' WHERE `id` = '$id'");
			if($upd){
				header("Location: ".UCMS_DIR."/admin/manage.php?module=users&section=groups&alert=updated");
			}
		}else{
			header("Location: ".UCMS_DIR."/admin/manage.php?module=users&section=groups");
		}
	}

	function delete_group($id){
		global $udb;
		$id = (int) $id;
		if(!$id){
			return false;
		}else{
			if($id > DEFAULT_GROUPS_AMOUNT){
				$users = $udb->query("UPDATE `".UC_PREFIX."users` SET `group` = '4' WHERE `group` = '$id'");
				$del = $udb->query("DELETE FROM `".UC_PREFIX."groups` WHERE `id` = '$id'");
				if($del){
					header("Location: ".UCMS_DIR."/admin/manage.php?module=users&section=groups&alert=deleted");
				}
			}else header("Location: ".UCMS_DIR."/admin/manage.php?module=users&section=groups");
		}
		
	}

	function manage_groups(){
		global $user, $udb, $ucms;
		if (isset($_POST['item']) and isset($_POST['actions'])){
			$items = array();
			$action = (int) $_POST['actions'];
			foreach ($_POST['item'] as $id) {
				$id = (int) $id;
				if($id > DEFAULT_GROUPS_AMOUNT){
					$items[] = $id;
				}
			}
			$ids = implode(',', $items);
			if (count($items) > 0) {
				switch ($action) {
					case 1:
						
					break;
	
					case 2:
						
					break;
	
					case 3:
						$del = $udb->query("DELETE FROM `".UC_PREFIX."groups` WHERE `id` IN ($ids)");
						if (count($items) > 1) {
							header("Location: ".UCMS_DIR."/admin/manage.php?module=users&section=groups&alert=deleted_multiple");
						}else 
							header("Location: ".UCMS_DIR."/admin/manage.php?module=users&section=groups&alert=deleted");
					break;
					
				}
			}
		}
		$call = $udb->num_rows("SELECT * FROM `".UC_PREFIX."groups`");
		$user_id = $user->get_user_id();
		$perpage = 10;
		$columns = array('name', 'permissions');
		$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
		$orderby = isset($_GET['orderby']) ? in_array($_GET['orderby'], $columns) ? $_GET['orderby'] : 'id' : 'id';
		$order = (isset($_GET['order']) and $_GET['order'] == 'DESC') ? 'ASC' : 'DESC';
		if($page <= 0) $page = 1;
		$count = $udb->num_rows("SELECT * FROM `".UC_PREFIX."groups` ORDER BY `$orderby` $order");
		$pages_count = 0;
		if($count != 0){ 
			$pages_count = ceil($count / $perpage); 
			if ($page > $pages_count):
				$page = $pages_count;
			endif; 
			$start_pos = ($page - 1) * $perpage;
			$sql = "SELECT * FROM `".UC_PREFIX."groups` ORDER BY `$orderby` $order LIMIT $start_pos, $perpage";	
		}else $sql  = "SELECT * FROM `".UC_PREFIX."groups` WHERE `id` = '0'";
		?>
		<br>
		<?php $ucms->cout("module.users.groups.total.label", false, $call); ?>
		<br><br>
		<form action="manage.php?module=users&amp;section=groups" method="post">
		<?php if($user->has_access("users", 6)){ ?>
		<select name="actions" style="width: 250px;">
			<option><?php $ucms->cout("module.users.groups.selected.option"); ?></option>
			<option value="3"><?php $ucms->cout("module.users.groups.selected.delete.option"); ?></option>
		</select>
		<?php } ?>
		<input type="submit" value="<?php $ucms->cout("module.users.groups.selected.apply.button"); ?>" class="ucms-button-submit">
		<br>
		<?php
		if($pages_count > 1){
			echo "<br>";
			pages($page, $count, $pages_count, 15, false);
			echo '<br>';
		}
		$link1 = UCMS_DIR."/admin/manage.php?module=users&amp;section=groups&amp;orderby=name&amp;order=".$order.(isset($_GET['page']) ? "&amp;page=".$_GET['page'] : "");
		$link2 = UCMS_DIR."/admin/manage.php?module=users&amp;section=groups&amp;orderby=permissions&amp;order=".$order.(isset($_GET['page']) ? "&amp;page=".$_GET['page'] : "");
		$mark = $order == "ASC" ? '↑' : '↓';
		$group = $udb->get_rows($sql);
		?><br>
		<table class="manage">
			<tr>
				<th style="width: 15px;"><input type="checkbox" name="select-all" value="1"></th>
				<th style="width: 15%"><a href="<?php echo $link1; ?>"><?php echo $ucms->cout("module.users.groups.table.header.name", true).$mark; ?></a></th>
				<th><a href="<?php echo $link2; ?>"><?php echo $ucms->cout("module.users.groups.table.header.permissions", true).$mark; ?></a></th>
				<th style="width: 115px;"><?php $ucms->cout("module.users.groups.table.header.users_in_group"); ?></th>
				<th style="width: 115px;"><?php $ucms->cout("module.users.groups.table.header.manage"); ?></th>
			</tr>
			<?php for($i = 0; $i < count($group); $i++){ 
				$count_users = $udb->num_rows("SELECT `id` FROM `".UC_PREFIX."users` WHERE `group` = '".$group[$i]['id']."'");
			?>
			<tr>
				<td><input type="checkbox" name="item[]" value="<?php echo $group[$i]['id']; ?>"></td>
				<td><?php echo $group[$i]['name']; ?></td>
				<td><?php permissions_info($group[$i]['permissions']); ?></td>
				<td style="text-align: center;"><?php echo $count_users; ?></td>
				<td><span class="actions"><?php 
					if($user->has_access("users", 6)){ 
						if($group[$i]['id'] != 1){ 
							?>
							<a href="manage.php?module=users&amp;section=groups&amp;action=update&amp;id=<?php echo $group[$i]['id']; 
							?>"><?php $ucms->cout("module.users.groups.table.manage.edit.button"); ?></a>
							<?php
						} 
						if($group[$i]['id'] > DEFAULT_GROUPS_AMOUNT){ ?> | <a href="<?php echo htmlspecialchars(get_current_url("alert", "action", "id")) ?>&amp;action=delete&amp;id=<?php echo $group[$i]['id']; 
						?>"><?php $ucms->cout("module.users.groups.table.manage.delete.button"); ?></a>
						<?php 
						}  
					} 
				?>
				</span></td>
			</tr>
			<?php } ?>
		</table>
		<?php
	}

	function permissions_info($permissions){
		global $ucms, $group;
		$p_array = $group->get_permissions_array($permissions);
		$modules = array_keys($p_array);
		for($i = 0; $i < count($p_array); $i++) {
			$dir = $modules[$i];
			$name = get_module('local_name', $dir);
			$level = $p_array[$dir];
			if($name and is_activated_module($dir)){
				echo "<br><b>$name</b><br>";
				$module_path = ABSPATH.MODULES_PATH.$dir;
				$ucms->set_language($module_path.'/languages/'.SYSTEM_LANGUAGE.'.lang');
				if($ucms->is_language_string_id("module.$dir.permission.level_$level.label")){
					$ucms->cout("module.$dir.permission.level_$level.label");
				}
			}
		}
		echo "<br><br>";
	}
?>