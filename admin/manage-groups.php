<?php
	function add_group_form(){
		?>
		<form action="groups.php" method="post">
			<table class="forms">
				<tr>
					<td><b>Название группы</b></td>
					<td><input name="name" type="text"></td>
				</tr>
				<tr>
					<td><b>Посты</b></td>
					<td><select name="posts">
						<option value="0">Нет доступа</option>
						<option value="1">Чтение</option>
						<option value="2">Добавление, изменение своих записей</option>
						<option value="3">Удаление своих записей</option>
						<option value="4">Изменение всех записей, не принадлежащих администратору</option>
						<option value="5">Удаление всех записей, не принадлежащих администратору</option>
						<option value="6">Изменение всех записей</option>
						<option value="7">Удаление всех записей</option>
					</select></td>
				</tr>
				<tr>
					<td><br><b>Комментарии</b></td>
					<td><select name="comments">
						<option value="0">Нет доступа</option>
						<option value="1">Чтение</option>
						<option value="2">Добавление своих комментариев</option>
						<option value="3">Изменение, удаление своих комментариев</option>
						<option value="4">Изменение всех комментариев, не принадлежащих администратору</option>
						<option value="5">Удаление всех комментариев, не принадлежащих администратору</option>
						<option value="6">Изменение всех комментариев</option>
						<option value="7">Удаление всех комментариев</option>
					</select></td>
				</tr>
				<tr>
					<td><br><b>Страницы</b></td>
					<td><select name="pages">
						<option value="0">Нет доступа</option>
						<option value="1">Чтение</option>
						<option value="2">Добавление, изменение своих записей</option>
						<option value="3">Удаление своих записей</option>
						<option value="4">Изменение всех записей, не принадлежащих администратору</option>
						<option value="5">Удаление всех записей, не принадлежащих администратору</option>
						<option value="6">Изменение всех записей</option>
						<option value="7">Удаление всех записей</option>
					</select></td>
				</tr>
				<tr>
					<td><b>Пользователи</b></td>
					<td><select name="users">
						<option value="0">Нет доступа</option>
						<option value="1">Чтение</option>
						<option value="2">Изменение своего профиля</option>
						<option value="3">Удаление своего профиля</option>
						<option value="4">Изменение всех профилей, кроме администратора</option>
						<option value="5">Удаление всех профилей, кроме администратора</option>
						<option value="6">Изменение всех профилей</option>
						<option value="7">Удаление всех профилей</option>
					</select></td>
				</tr>

				<tr>
					<td colspan=2><input name="add" type="submit" value="Добавить" class="ucms-button-submit"></td>
				</tr>
			</table>
			<br>*Каждый последующий уровень включает в себя права предыдущих.
		</form>
		<?php
	}

	function add_group($p){
		global $udb, $ucms;
		if(isset($_POST['name']) and $_POST['name'] != ''){
			$gname = $udb->parse_value($_POST['name']);
			$galias = $ucms->transliterate($gname);
			$permissions = (int) ($_POST['posts']).(int) ($_POST['comments']).(int) ($_POST['pages']).(int) ($_POST['users']);
			$add = $udb->query("INSERT INTO `".UC_PREFIX."groups` VALUES(NULL, '$gname', '$galias', '$permissions')");
			if($add){
				$_SESSION['success_add'] = true;
				header("Location: ".UCMS_DIR."/admin/groups.php");
			}
		}
	}

	function update_group_form($id){
		global $udb;
		$id = (int) $id;
		$group = $udb->get_row("SELECT * FROM `".UC_PREFIX."groups` WHERE `id` = '$id'");
		$p_array[0] = (int) ($group['permissions'] / 1000);
		$p_array[1] = (int) (($group['permissions'] / 100) % 10);
		$p_array[2] = (int) (($group['permissions'] / 10) % 10);
		$p_array[3] = (int) ($group['permissions'] % 10);
		if($group and $id > 1 and count($group) > 0){
		?>
		<form action="groups.php" method="post">
			<input type="hidden" name="id" value="<?php echo $group['id']; ?>">
			<table class="forms">
				<tr>
					<td><b>Название группы</b></td>
					<td><input name="name" type="text" value="<?php echo $group['name']; ?>"></td>
				</tr>
				<tr>
					<td><b>Посты</b></td>
					<td><select name="posts">
						<option value="0" <?php if($p_array[0] == 0) echo "selected"; ?>>Нет доступа</option>
						<option value="1" <?php if($p_array[0] == 1) echo "selected"; ?>>Чтение</option>
						<option value="2" <?php if($p_array[0] == 2) echo "selected"; ?>>Добавление, изменение своих записей</option>
						<option value="3" <?php if($p_array[0] == 3) echo "selected"; ?>>Удаление своих записей</option>
						<option value="4" <?php if($p_array[0] == 4) echo "selected"; ?>>Изменение всех записей, не принадлежащих администратору</option>
						<option value="5" <?php if($p_array[0] == 5) echo "selected"; ?>>Удаление всех записей, не принадлежащих администратору</option>
						<option value="6" <?php if($p_array[0] == 6) echo "selected"; ?>>Изменение всех записей</option>
						<option value="7" <?php if($p_array[0] == 7) echo "selected"; ?>>Удаление всех записей</option>
					</select></td>
				</tr>
				<tr>
					<td><br><b>Комментарии</b></td>
					<td><select name="comments">
						<option value="0" <?php if($p_array[1] == 0) echo "selected"; ?>>Нет доступа</option>
						<option value="1" <?php if($p_array[1] == 1) echo "selected"; ?>>Чтение</option>
						<option value="2" <?php if($p_array[1] == 2) echo "selected"; ?>>Добавление своих комментариев</option>
						<option value="3" <?php if($p_array[1] == 3) echo "selected"; ?>>Изменение, удаление своих комментариев</option>
						<option value="4" <?php if($p_array[1] == 4) echo "selected"; ?>>Изменение всех комментариев, не принадлежащих администратору</option>
						<option value="5" <?php if($p_array[1] == 5) echo "selected"; ?>>Удаление всех комментариев, не принадлежащих администратору</option>
						<option value="6" <?php if($p_array[1] == 6) echo "selected"; ?>>Изменение всех комментариев</option>
						<option value="7" <?php if($p_array[1] == 7) echo "selected"; ?>>Удаление всех комментариев</option>
					</select></td>
				</tr>
				<tr>
					<td><br><b>Страницы</b></td>
					<td><select name="pages">
						<option value="0" <?php if($p_array[2] == 0) echo "selected"; ?>>Нет доступа</option>
						<option value="1" <?php if($p_array[2] == 1) echo "selected"; ?>>Чтение</option>
						<option value="2" <?php if($p_array[2] == 2) echo "selected"; ?>>Добавление, изменение своих записей</option>
						<option value="3" <?php if($p_array[2] == 3) echo "selected"; ?>>Удаление своих записей</option>
						<option value="4" <?php if($p_array[2] == 4) echo "selected"; ?>>Изменение всех записей, не принадлежащих администратору</option>
						<option value="5" <?php if($p_array[2] == 5) echo "selected"; ?>>Удаление всех записей, не принадлежащих администратору</option>
						<option value="6" <?php if($p_array[2] == 6) echo "selected"; ?>>Изменение всех записей</option>
						<option value="7" <?php if($p_array[2] == 7) echo "selected"; ?>>Удаление всех записей</option>
					</select></td>
				</tr>
				
				<tr>
					<td><b>Пользователи</b></td>
					<td><select name="users">
						<option value="0" <?php if($p_array[3] == 0) echo "selected"; ?>>Нет доступа</option>
						<option value="1" <?php if($p_array[3] == 1) echo "selected"; ?>>Чтение</option>
						<option value="2" <?php if($p_array[3] == 2) echo "selected"; ?>>Изменение своего профиля</option>
						<option value="3" <?php if($p_array[3] == 3) echo "selected"; ?>>Удаление своего профиля</option>
						<option value="4" <?php if($p_array[3] == 4) echo "selected"; ?>>Изменение всех профилей, кроме администратора</option>
						<option value="5" <?php if($p_array[3] == 5) echo "selected"; ?>>Удаление всех профилей, кроме администратора</option>
						<option value="6" <?php if($p_array[3] == 6) echo "selected"; ?>>Изменение всех профилей</option>
						<option value="7" <?php if($p_array[3] == 7) echo "selected"; ?>>Удаление всех профилей</option>
					</select></td>
				</tr>

				<tr>
					<td colspan=2><input name="update" type="submit" value="Обновить" class="ucms-button-submit"></td>
				</tr>
			</table>
			<br>*Каждый последующий уровень включает в себя права предыдущих.
		</form>
		<?php
		}else header("Location: groups.php");
	}

	function update_group($p){
		global $udb, $ucms;
		$id = (int) $_POST['id'];
		if(isset($_POST['name']) and $_POST['name'] != '' and $id > 1){
			$gname = $udb->parse_value($_POST['name']);
			$permissions = (int) ($_POST['posts']).(int) ($_POST['comments']).(int) ($_POST['pages']).(int) ($_POST['users']);
			$upd = $udb->query("UPDATE `".UC_PREFIX."groups` SET `name` = '$gname', `permissions` = '$permissions' WHERE `id` = '$id'");
			if($upd){
				$_SESSION['success_upd'] = true;
				header("Location: ".UCMS_DIR."/admin/groups.php");
			}
		}else{
			header("Location: ".UCMS_DIR."/admin/groups.php");
		}
	}

	function delete_group($id){
		global $udb;
		$id = (int) $id;
		if(!$id){
			return false;
		}else{
			if($id > 7){
				$users = $udb->query("UPDATE `".UC_PREFIX."users` SET `group` = '4' WHERE `group` = '$id'");
				$del = $udb->query("DELETE FROM `".UC_PREFIX."groups` WHERE `id` = '$id'");
				if($del){
					$_SESSION['success_del'] = true;
					header("Location: ".UCMS_DIR."/admin/groups.php");
				}
			}else header("Location: ".UCMS_DIR."/admin/groups.php");
		}
		
	}

	function manage_groups(){
		global $user, $udb;
		if (isset($_POST['item']) and isset($_POST['actions'])){
			$items = array();
			$action = (int) $_POST['actions'];
			foreach ($_POST['item'] as $id) {
				$id = (int) $id;
				if($id > 7){
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
							$_SESSION['success_delm'] = true;
						}else 
							$_SESSION['success_del'] = true;
 						header("Location: ".UCMS_DIR."/admin/groups.php");
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
		Всего групп: <?php echo $call; ?>
		<br><br>
		<form action="groups.php" method="post">
		<?php if($user->has_access(5, 7)){ ?>
		<select name="actions" style="width: 250px;">
			<option>Отмеченные</option>
			<option value="3">Удалить</option>
		</select>
		<?php } ?>
		<input type="submit" value="Применить" class="ucms-button-submit">
		<br>
		<?php
		if($pages_count > 1){
			echo "<br>";
			pages($page, $count, $pages_count, 15, false);
			echo '<br>';
		}
		$link1 = UCMS_DIR."/admin/groups.php?orderby=name&amp;order=".$order.(isset($_GET['page']) ? "&amp;page=".$_GET['page'] : "");
		$link2 = UCMS_DIR."/admin/groups.php?orderby=permissions&amp;order=".$order.(isset($_GET['page']) ? "&amp;page=".$_GET['page'] : "");
		$mark = $order == "ASC" ? '↑' : '↓';
		$group = $udb->get_rows($sql);
		?><br>
		<table class="manage">
			<tr>
				<th style="width: 15px;"><input type="checkbox" name="select-all" value="1"></th>
				<th style="width: 15%"><a href="<?php echo $link1; ?>">Название <?php echo $mark; ?></a></th>
				<th><a href="<?php echo $link2; ?>">Права <?php echo $mark; ?></a></th>
				<th style="width: 115px;">Управление</th>
			</tr>
			<?php for($i = 0; $i < count($group); $i++){ ?>
			<tr>
				<td><input type="checkbox" name="item[]" value="<?php echo $group[$i]['id']; ?>"></td>
				<td><?php echo $group[$i]['name']; ?></td>
				<td><?php permissions_info($group[$i]['permissions']); ?></td>
				<td><span class="actions"><?php 
					if($user->has_access(4, 6)){ 
						if($group[$i]['id'] != 1){ 
							?>
							<a href="groups.php?action=update&amp;id=<?php echo $group[$i]['id']; ?>">Изменить</a>
							<?php
						} 
						if($group[$i]['id'] > 7){ ?> | <a href="groups.php?action=delete&amp;id=<?php echo $group[$i]['id']; ?>">Удалить</a>
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
		$p_array[0] = (int) ($permissions / 1000);
		$p_array[1] = (int) (($permissions / 100) % 10);
		$p_array[2] = (int) (($permissions / 10) % 10);
		$p_array[3] = (int) ($permissions % 10);
		$i = 1;
		foreach ($p_array as $level) {
			switch ($i) {
				case 1:
					echo "<b>Посты</b><br>";
				break;
				
				case 2:
					echo "<br><b>Комментарии</b><br>";
				break;

				case 3:
					echo "<br><b>Страницы</b><br>";
				break;

				case 4:
					echo "<br><b>Пользователи</b><br>";
				break;
			}
			switch ($level) {
				case 0:
					echo "Нет доступа";
				break;
				
				case 1:
					echo "Чтение";
				break;

				case 2:
					switch ($i) {
						case 1: case 3:
							echo "Добавление, изменение своих записей";
						break;

						case 2:
							echo "Добавление своих комментариев";
						break;

						case 4:
							echo "Изменение своего профиля";
						break;
					}
				break;

				case 3:
					switch ($i) {
						case 1: case 3:
							echo "Удаление своих записей";
						break;

						case 2:
							echo "Изменение, удаление своих комментариев";
						break;

						case 4:
							echo "Удаление своего профиля";
						break;
					}
				break;

				case 4:
					switch ($i) {
						case 1: case 3:
							echo "Изменение всех записей, не принадлежащих администратору";
						break;

						case 2:
							echo "Изменение всех комментариев, не принадлежащих администратору";
						break;

						case 4:
							echo "Изменение всех профилей, кроме администратора";
						break;
					}
				break;

				case 5:
					switch ($i) {
						case 1: case 3:
							echo "Удаление всех записей, не принадлежащих администратору";
						break;

						case 2:
							echo "Удаление всех комментариев, не принадлежащих администратору";
						break;

						case 4:
							echo "Удаление всех профилей, кроме администратора";
						break;
					}
				break;

				case 6:
					switch ($i) {
						case 1: case 3:
							echo "Изменение всех записей";
						break;

						case 2:
							echo "Изменение всех комментариев";
						break;

						case 4:
							echo "Изменение всех профилей";
						break;
					}
				break;

				case 7:
					switch ($i) {
						case 1: case 3:
							echo "Удаление всех записей";
						break;

						case 2:
							echo "Удаление всех комментариев";
						break;

						case 4:
							echo "Удаление всех профилей";
						break;
					}
				break;

				default:
					echo $level;
				break;
			}
			$i++;
		}
	}
?>