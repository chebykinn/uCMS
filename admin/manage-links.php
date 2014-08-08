<?php
function manage_links(){
	global $udb, $user, $ucms;
	if (isset($_POST['item']) and isset($_POST['actions'])){
		$items = array();
		$action = (int) $_POST['actions'];
		$accessLVL = 7;
		foreach ($_POST['item'] as $id) {
			$id = (int) $id;

			if($user->has_access(5, 7)){
				$items[] = $id;
			}
		}
		$ids = implode(',', $items);
		if (count($items) > 0) {
			switch ($action) {
				case 1:
					$upd = $udb->query("UPDATE `".UC_PREFIX."links` SET `publish` = '1' WHERE `id` IN ($ids)");
					if (count($items) > 1) {
						$_SESSION['success_updm'] = true;
					}else 
						$_SESSION['success_upd'] = true;
 					header("Location: ".UCMS_DIR."/admin/links.php");
				break;
	
				case 2:
					$upd = $udb->query("UPDATE `".UC_PREFIX."links` SET `publish` = '0' WHERE `id` IN ($ids)");
					if (count($items) > 1) {
						$_SESSION['success_updm'] = true;
					}else 
						$_SESSION['success_upd'] = true;
 					header("Location: ".UCMS_DIR."/admin/links.php");
				break;
	
				case 3:
					$del = $udb->query("DELETE FROM `".UC_PREFIX."links` WHERE `id` IN ($ids)");
					if (count($items) > 1) {
						$_SESSION['success_delm'] = true;
					}else 
						$_SESSION['success_del'] = true;
 					header("Location: ".UCMS_DIR."/admin/links.php");
				break;
				
			}
		}
	}
	$call = $udb->num_rows("SELECT * FROM `".UC_PREFIX."links`");
	$cpublished = $udb->num_rows("SELECT * FROM `".UC_PREFIX."links` WHERE `publish` > 0");
	$cdraft = $udb->num_rows("SELECT * FROM `".UC_PREFIX."links` WHERE `publish` = 0");
	$perpage = 25;
	$user_id = $user->get_user_id();
	$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
	$columns = array('name','author', 'date');
	$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
	$orderby = isset($_GET['orderby']) ? in_array($_GET['orderby'], $columns) ? $_GET['orderby'] : 'id' : 'id';
	$order = (isset($_GET['order']) and $_GET['order'] == 'DESC') ? 'ASC' : 'DESC';
	$status = isset($_GET['status']) ? $_GET['status'] : "";
	switch ($status) {
		case 'published':
			$swhere = "WHERE `publish` = 1";
			break;
		case 'hidden':
			$swhere = "WHERE `publish` = 0";
			break;
		default:
			$swhere = "";
		break;
	}
	if($page <= 0) $page = 1;
	$count = $udb->num_rows("SELECT * FROM `".UC_PREFIX."links` $swhere ORDER BY `$orderby` $order");
	$pages_count = 0;
	if($count != 0){ 
		$pages_count = ceil($count / $perpage); 
		if ($page > $pages_count):
			$page = $pages_count;
		endif; 
		$start_pos = ($page - 1) * $perpage;
		$sql = "SELECT * FROM `".UC_PREFIX."links` $swhere ORDER BY `$orderby` $order LIMIT $start_pos, $perpage";
	}else $sql = "SELECT * FROM `".UC_PREFIX."links` WHERE `id` = '0'";

	$lall = $status != '' ? "<a href=\"".UCMS_DIR."/admin/links.php".(isset($_GET['orderby']) ? "?orderby=".$_GET['orderby'] : "").(isset($_GET['order']) ? "&amp;order=".$_GET['order'] : "").(isset($_GET['page']) ? ((isset($_GET['orderby']) and isset($_GET['order'])) ? "&amp;" : "?")."page=".$_GET['page'] : "")."\">Все</a>" : "<b>Все</b>"; 
	$lpublished = $status != 'published' ? "<a href=\"".UCMS_DIR."/admin/links.php?status=published".(isset($_GET['orderby']) ? "&amp;orderby=".$_GET['orderby'] : "").(isset($_GET['order']) ? "&amp;order=".$_GET['order'] : "").(isset($_GET['page']) ? "&amp;page=".$_GET['page'] : "")."\">Опубликованные</a>" : "<b>Опубликованные</b>"; 
	$ldraft =  $status != 'hidden' ? "<a href=\"".UCMS_DIR."/admin/links.php?status=hidden".(isset($_GET['orderby']) ? "&amp;orderby=".$_GET['orderby'] : "").(isset($_GET['order']) ? "&amp;order=".$_GET['order'] : "").(isset($_GET['page']) ? "&amp;page=".$_GET['page'] : "")."\">Скрытые</a>" : "<b>Скрытые</b>"; 
	?>
	<br>
	Показывать: <?php echo $lall." ($call)"; ?> | <?php echo $lpublished." ($cpublished)"; ?> | <?php echo $ldraft." ($cdraft)"; ?>
	<br><br>
	<form action="links.php" method="post">
	<?php if($user->has_access(5, 7)){ ?>
	<select name="actions" style="width: 250px;">
		<option>Отмеченные</option>
		<option value="1">Опубликовать</option>
		<option value="2">Скрыть</option>
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
	} ?><br>
	<table class="manage">
	<?php 
	$link1 = UCMS_DIR."/admin/links.php?orderby=name&amp;order=".$order.(isset($_GET['page']) ? "&amp;page=".$_GET['page'] : "");
	$link2 = UCMS_DIR."/admin/links.php?orderby=author&amp;order=".$order.(isset($_GET['page']) ? "&amp;page=".$_GET['page'] : "");
	$link3 = UCMS_DIR."/admin/links.php?orderby=date&amp;order=".$order.(isset($_GET['page']) ? "&amp;page=".$_GET['page'] : "");
	$mark = $order == "ASC" ? '↑' : '↓';
	$links = $udb->get_rows($sql);
	?>
	<tr>
		<th style="width: 15px;"><input type="checkbox" name="select-all" value="1"></th>
		<th><a href="<?php echo $link1; ?>">Название <?php echo $mark; ?></a></th>
		<th>Адрес</th>
		<th>Описание</th>
		<th><a href="<?php echo $link2; ?>">Автор <?php echo $mark; ?></a></th>
		<th>Статус</th>
		<th><a href="<?php echo $link3; ?>">Дата <?php echo $mark; ?></a></th>
		<th style="width: 115px;">Управление</th>
	</tr>
	<?php	
	if($links){ 
		for ($i = 0; $i < count($links); $i++) { 
			$authors[] = $udb->parse_value($links[$i]['author']);
		}

		$authors = implode("','", $authors);
		$authors = "'".$authors."'";
		$authors_meta = $udb->get_rows("SELECT `id`, `login` FROM `".UC_PREFIX."users` WHERE `id` in ($authors) ");

 		for ($i = 0; $i < count($links); $i++){
 			for($j = 0; $j < count($authors_meta); $j++){
				if($links[$i]['author'] === $authors_meta[$j]['id']){
					$links_author_login = $authors_meta[$j]['login'];
					break;
				}
			}
			$status = $links[$i]['publish'] == 1 ? 'Опубликована' : 'Скрыта';
			$link = NICE_LINKS ? SITE_DOMAIN.UCMS_DIR.'/redirect/'.$links[$i]['url'] : SITE_DOMAIN.UCMS_DIR.'/?action=redirect&amp;url='.$links[$i]['url'];
			echo "<tr>";
			echo "<td><input type=\"checkbox\" name=\"item[]\" value=\"".$links[$i]['id']."\"></td>";
			echo "<td>".$links[$i]['name']."</td>";
			echo "<td><a target=\"_blank\" rel=\"external\" href=\"".$link."\">".$links[$i]['url']."</a></td>";
			echo "<td>".$links[$i]['description']."</td>";
			echo "<td>".((int) $links[$i]['author'] > 0 ? $links_author_login : $links[$i]['author'])."</td>";
			echo "<td>".$status."</td>";
			echo "<td>".$ucms->format_date($links[$i]['date'], false)."</td>";
			echo "<td><a href=\"links.php?action=update&amp;id=".$links[$i]['id']."\">Изменить</a> | <a href=\"links.php?action=delete&amp;id=".$links[$i]['id']."\">Удалить</a></td>";
			echo "</tr>";
		} 
	}else{
		?>
		<tr><td colspan="8" style="text-align:center;">У вас еще нет добавленных ссылок.</td></tr>
		<?php
	}
	?>
	</table></form>
	<?php
}

function add_link_form(){
	global $user;
		?>
		<form method="post" action="links.php">
			<input type="hidden" name="add" value="true">
			<table class="forms">
				<tr>
					<td width="80px"><label for="title">Название:</label></td>
					<td><input type="text" name="title" id="title" required></td> 
				</tr>
				<tr>
					<td width="80px"><label for="url">Адрес:</label></td>
					<td><input type="text" name="url" id="url"></td> 
				</tr>
				<tr>
					<td><label for="body">Описание:</label></td> 
					<td><input type="text" name="body" id="body"></td> 
				</tr>
				<tr>		
					<td><label for="author">Автор:</label></td>
					<td><input type="text" name="author" id="author" value="<?php echo $user->get_user_login(); ?>"></td>
				</tr>
				<tr>		
					<td><label for="target">Открывать ссылку:</label></td>
					<td>
					<input type="radio" name="target" id="target" value="_blank" checked>
					В новой вкладке<br>
					<input type="radio" name="target" id="target" value="_top">
					В том же окне</td>	
				</tr>
				<tr>
					<td>Опубликовать</td>
					<td><input type="checkbox" value="1" id="publish" name="publish" checked></td>
				</tr>
				<tr>
					<td></td>
					<td><input type="submit" name="submit" class="ucms-button-submit" value="Добавить"></td>
				</tr>
			</table>
		</form>
		<?php
}

function update_link_form($id){
	global $user, $udb;
	$id = (int) $id;
	if(!empty($id) and $id > 0){
		$link = $udb->get_row("SELECT * FROM `".UC_PREFIX."links` WHERE `id` = '$id'");
		if($link and count($link) > 0){
		?>
		<form method="post" action="links.php">
			<input type="hidden" name="update" value="true">
			<input type="hidden" name="id" value="<?php echo $id; ?>">
			<table class="forms">
				<tr>
					<td width="80px"><label for="title">Название:</label></td>
					<td><input type="text" name="title" id="title" value="<?php echo htmlspecialchars($link['name']); ?>" required></td> 
				</tr>
				<tr>
					<td width="80px"><label for="url">Адрес:</label></td>
					<td><input type="text" name="url" id="url" value="<?php echo htmlspecialchars($link['url']); ?>"></td> 
				</tr>
				<tr>
					<td><label for="body">Описание:</label></td> 
					<td><input type="text" name="body" id="body" value="<?php echo htmlspecialchars($link['description']); ?>"></td> 
				</tr>
				<tr>		
					<td><label for="author">Автор:</label></td>
					<td><input type="text" name="author" id="author" value="<?php echo ( (int) $link['author'] > 0 ? htmlspecialchars($user->get_user_login($link['author'])) : htmlspecialchars($link['author']) ); ?>"></td>
				</tr>
				<tr>		
					<td><label for="target">Открывать ссылку:</label></td>
					<td>
					<input type="radio" name="target" id="target" value="_blank" checked>
					В новой вкладке<br>
					<input type="radio" name="target" id="target" value="_top">
					В том же окне</td>	
				</tr>
				<tr>
					<td>Опубликовать</td>
					<td><input type="checkbox" value="1" id="publish" name="publish" <?php if($link['publish'] == 1) echo "checked"; ?>></td>
				</tr>
				<tr>
					<td></td>
					<td><input type="submit" name="submit" class="ucms-button-submit" onclick="makePublish()" value="Изменить"></td>
				</tr>
			</table>
		</form>
		<?php
		}else{
			header("Location: links.php");
		}
	}
}


function add_link($p){
	global $udb, $user;
	$title = $udb->parse_value($p['title']);
	$description = $udb->parse_value($p['body']);
	$author = $user->get_user_id($udb->parse_value($p['author']));
	if(!$author) $author = $udb->parse_value($p['author']);
	$publish = isset($p['publish']) ? 1 : 0;
	$target = $udb->parse_value($p['target']);
	$url = $udb->parse_value($p['url']);
	if(!empty($title) and !empty($url) and !empty($description) and $author){
		$add = $udb->query("INSERT INTO `".UC_PREFIX."links` VALUES (NULL, '$title', '$publish', '$url', '$description', '$author', '$target', NOW())");
		if($add){
			$_SESSION['success_add'] = true;
			header("Location: ".UCMS_DIR."/admin/links.php");
		}
	}else{
		echo "<div class=\"error\">Нужно заполнить все поля!</div><br>";
	}
	
}

function update_link($p){
	global $udb, $user;
	$id = (int) $p['id'];
	$title = $udb->parse_value($p['title']);
	$description = $udb->parse_value($p['body']);
	$author = $user->get_user_id($udb->parse_value($p['author']));
	if(!$author) $author = $udb->parse_value($p['author']);
	$publish = isset($p['publish']) ? 1 : 0;
	$target = $udb->parse_value($p['target']);
	$url = $udb->parse_value($p['url']);
	if(!empty($title) and !empty($url) and !empty($description) and $author){
		$upd = $udb->query("UPDATE `".UC_PREFIX."links` SET `name` = '$title', `publish` = '$publish', `url` = '$url', `description` = '$description', `author` = '$author', `target` = '$target' WHERE `id` = '$id'");
		if($upd){
			$_SESSION['success_upd'] = true;
			header("Location: ".UCMS_DIR."/admin/links.php");
		}
	}else{
		echo "<div class=\"error\">Нужно заполнить все поля!</div><br>";
	}
	
}

function delete_link($id){
	global $udb, $user;
	$id = (int) $id;
	if(!empty($id) and $id > 0){
		$del = $udb->query("DELETE FROM `".UC_PREFIX."links` WHERE `id` = '$id'");
		if($del){
			$_SESSION['success_del'] = true;
			header("Location: ".UCMS_DIR."/admin/links.php");
		}
	}else return false;
}
?>