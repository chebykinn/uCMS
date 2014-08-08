<?php
	function add_page_form(){
		global $user, $months;
		?>
		<form method="post" action="pages.php">
			<input type="hidden" name="add" value="true">
			<input type="hidden" id="body" name="body">
			<table class="forms" style="width:100%">	
				<tr>
					<td width="80px"><label for="title">Заголовок:</label></td>
					<td><input type="text" name="title" id="title"></td> 
				</tr>
				<tr>
					<td width="80px"><label for="title">Ссылка:</label></td>
					<td><input type="text" name="alias" id="alias"></td> 
				</tr>
				<tr><td></td><td>
					<nobr>
					<input class="editb" type="button" value="Ж" style="font-weight: bold;" onclick='setBold()'>
					<input class="editb" type="button" value="К" style="font-style: italic;" onclick="setItal()">
					<input class="editb" type="button" value="П" style="text-decoration: underline;" onclick="setUnderline()">
					<input class="editb" type="button" value="Ссылка" onclick="setLink()">
					<input class="editb" type="button" value="Изображение" onclick="setImage()">
					<input class="editb" type="button" value="Слева" onclick="setLeft()">
					<input class="editb" type="button" value="По центру" onclick="setCenter()">
					<input class="editb" type="button" value="Справа" onclick="setRight()">
					<input class="editb" type="button" style="width: 200px" value="Очистить форматирование" onclick="RemoveFormat()">
					<input id="htmlb" class="editb" type="button" value="HTML-код" onclick="ShowHTML()"></nobr>
					<input id="wysiwygb" style="display:none; width: 200px" class="editb" type="button" value="Визуальный редактор" onclick="ShowWYSIWSYG()">
					</nobr>
					</td></tr>
				<tr>
					<td><label for="editor">Текст:</label></td> 
					<td><iframe scrolling="yes" frameborder="no" style="border: 1px solid #aaadaa" src="#" id="editor" name="editor" width="100%" height="600"></iframe><textarea style="display: none;" name="html-code" id="html-code" Rows="30" cols="500" ></textarea></td> 
				</tr>
				<?php if($user->has_access(1, 4)){ ?>
				<tr>
					<td>Автор:</td>
					<td><input type="text" value="<?php echo $user->get_user_login(); ?>" id="author" name="author"></td>
				</tr>
				<tr>
					<td>Дата:</td>
					<td>
						<select name="day" style="width:100px;">
							<?php
							echo "<option value=".date('d').">".date('d')."</option>";
							for ($i = 1; $i <= 31; $i++) {
								$d = $i < 10 ? "0$i" : $i;
								echo "<option value=\"$d\">$d</option>";
							}
							?>
						</select>
						<select name="month" style="width:100px;">
							<?php
							
							echo "<option value=".date('m').">".$months[date('m')]."</option>";
							for ($i = 1; $i <= 12; $i++) {
								$m = $i < 10 ? "0$i" : $i;
								echo "<option value=\"$m\">$months[$m]</option>";
							}
							?>
						</select>
						<select name="year" style="width:100px;">
							<?php
							echo "<option value=".date('Y').">".date('Y')."</option>";
							for ($i = date('Y'); $i >= 1900; $i--) {
								echo "<option value=\"$i\">$i</option>";
							}
							?>
						</select>
						в
						<input type="text" name="hour" style="width: 15px; height: 15px;" value="<?php echo date("H"); ?>"> :
						<input type="text" name="minute" style="width: 15px; height: 15px;" value="<?php echo date("i"); ?>"> :
						<input type="text" name="second" style="width: 15px; height: 15px;" value="<?php echo date("s"); ?>">
					</td>
				</tr>
				<?php } ?>
				<tr>
					<td>Опубликовать</td>
					<td><input type="checkbox" value="1" id="publish" name="publish" checked></td>
				</tr>
				<tr>
					<td></td>
					<td><input type="submit" name="submit" class="ucms-button-submit" onclick='makePublish()' value="Добавить"></td>
				</tr>
			</table>
		</form>
		<?php
	}

	function add_page($p){
		global $udb, $user;
		$title = $udb->parse_value($p['title']);
		$body = $udb->parse_value($p['body']);
		$publish = (int) $p['publish'];
		$alias = $udb->parse_value($p['alias']);
		$author = isset($p['author']) ? $user->get_user_id($udb->parse_value($p['author'])) : $user->get_user_id();
		if(isset($p['day'])){
			$day = (int) $p['day'];
			$month = (int) $p['month'];
			$year = (int) $p['year'];
			$hour = (int) $p['hour'];
			$minute = (int) $p['minute'];
			$second = (int) $p['second'];
			$date = "$year-$month-$day $hour:$minute:$second";
		}else $date = date("Y-m-d H:i:s");
		if(!$author)
			$author = $user->get_user_id($author);
		if((int) $author == 0 and !$user->logged()){
			$author = isset($_SESSION['guest_login']) ? $_SESSION['guest_login'] : "Гость";
		}
		if(empty($title) or empty($body)){
			echo '<br><div class="error">Нужно обязательно заполнить поля "Заголовок" и "Текст".</div>';
		}	
		else{
			$sql= "INSERT INTO `".UC_PREFIX."pages` VALUES (null, '$title','$alias', '$author', '$body', '$publish', '$date')";
			$add = $udb->query($sql);
			if($add){
				header("Location: ".UCMS_DIR."/admin/pages.php");
				$_SESSION['success_add'] = true;
			}
			
		}
	}
		
	function manage_pages(){
		global $udb, $user, $ucms;
		if (isset($_POST['item']) and isset($_POST['actions'])){
			$items = array();
			$action = (int) $_POST['actions'];
			foreach ($_POST['item'] as $id) {
				$id = (int) $id;
				$page = $udb->get_row("SELECT `author` FROM `".UC_PREFIX."pages` WHERE `id` = '$id' LIMIT 1");
				if($page){
					if(!empty($page['author'])){
						if($user->get_user_id() == $page['author']){
							$accessLVL = 2;
						}elseif($user->get_user_group($page['author']) == 1){
							$accessLVL = 6;
						}else{
							$accessLVL = 4;
						}
					}
				}
				if($action == 3) $accessLVL++;
				if($user->has_access(3, $accessLVL)){
					$items[] = $id;
				}
			}
			$ids = implode(',', $items);
			if (count($items) > 0) {
				switch ($action) {
					case 1:
						$upd = $udb->query("UPDATE `".UC_PREFIX."pages` SET `publish` = '1' WHERE `id` IN ($ids)");
						if (count($items) > 1) {
							$_SESSION['success_updm'] = true;
						}else 
							$_SESSION['success_upd'] = true;
 						header("Location: ".UCMS_DIR."/admin/pages.php");
					break;
	
					case 2:
						$upd = $udb->query("UPDATE `".UC_PREFIX."pages` SET `publish` = '0' WHERE `id` IN ($ids)");
						if (count($items) > 1) {
							$_SESSION['success_updm'] = true;
						}else 
							$_SESSION['success_upd'] = true;
 						header("Location: ".UCMS_DIR."/admin/pages.php");
					break;
	
					case 3:
						$del = $udb->query("DELETE FROM `".UC_PREFIX."pages` WHERE `id` IN ($ids)");
						if (count($items) > 1) {
							$_SESSION['success_delm'] = true;
						}else 
							$_SESSION['success_del'] = true;
 						header("Location: ".UCMS_DIR."/admin/pages.php");
					break;
					
				}
			}
		}
		$call = $udb->num_rows("SELECT * FROM `".UC_PREFIX."pages`");
		$cpublished = $udb->num_rows("SELECT * FROM `".UC_PREFIX."pages` WHERE `publish` > 0");
		$cdraft = $udb->num_rows("SELECT * FROM `".UC_PREFIX."pages` WHERE `publish` = 0");
		$user_id = $user->get_user_id();
		$perpage = 25;
		$columns = array('title','author', 'date');
		$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
		$orderby = isset($_GET['orderby']) ? in_array($_GET['orderby'], $columns) ? $_GET['orderby'] : 'id' : 'id';
		$order = (isset($_GET['order']) and $_GET['order'] == 'DESC') ? 'ASC' : 'DESC';
		$status = isset($_GET['status']) ? $_GET['status'] : "";
		switch ($status) {
			case 'published':
				$swhere = "WHERE `publish` = 1";
				break;

			case 'draft':
				$swhere = "WHERE `publish` = 0";
				break;

			default:
				$swhere = "";
			break;
		}
		if($page <= 0) $page = 1;
		if($user->has_access(3, 4))
			$count = $udb->num_rows("SELECT * FROM `".UC_PREFIX."pages` $swhere ORDER BY `$orderby` $order");
		else $count = $udb->num_rows("SELECT * FROM `".UC_PREFIX."pages` WHERE `author` = '$user_id' ORDER BY `$orderby` $order");
		$pages_count = 0;
		if($count != 0){ 
			$pages_count = ceil($count / $perpage); 
			if ($page > $pages_count):
				$page = $pages_count;
			endif; 
			$start_pos = ($page - 1) * $perpage;
			if($user->has_access(3, 4))
				$sql = "SELECT * FROM `".UC_PREFIX."pages` $swhere ORDER BY `$orderby` $order LIMIT $start_pos, $perpage";
			else $sql = "SELECT * FROM `".UC_PREFIX."pages` WHERE `author` = '$user_id' ORDER BY `$orderby` $order LIMIT $start_pos, $perpage";

			
		}else $sql  = "SELECT * FROM `".UC_PREFIX."pages` WHERE `id` = '0'";

		$lall = $status != '' ? "<a href=\"".UCMS_DIR."/admin/pages.php".(isset($_GET['orderby']) ? "?orderby=".$_GET['orderby'] : "").(isset($_GET['order']) ? "&amp;order=".$_GET['order'] : "").(isset($_GET['page']) ? ((isset($_GET['orderby']) and isset($_GET['order'])) ? "&amp;" : "?")."page=".$_GET['page'] : "")."\">Все</a>" : "<b>Все</b>"; 
		$lpublished = $status != 'published' ? "<a href=\"".UCMS_DIR."/admin/pages.php?status=published".(isset($_GET['orderby']) ? "&amp;orderby=".$_GET['orderby'] : "").(isset($_GET['order']) ? "&amp;order=".$_GET['order'] : "").(isset($_GET['page']) ? "&amp;page=".$_GET['page'] : "")."\">Опубликованные</a>" : "<b>Опубликованные</b>"; 
		$ldraft =  $status != 'draft' ? "<a href=\"".UCMS_DIR."/admin/pages.php?status=draft".(isset($_GET['orderby']) ? "&amp;orderby=".$_GET['orderby'] : "").(isset($_GET['order']) ? "&amp;order=".$_GET['order'] : "").(isset($_GET['page']) ? "&amp;page=".$_GET['page'] : "")."\">Черновики</a>" : "<b>Черновики</b>"; 
		?>
		<br>
		Показывать: <?php echo $lall." ($call)"; ?> | <?php echo $lpublished." ($cpublished)"; ?> | <?php echo $ldraft." ($cdraft)"; ?>
		<br><br>
		<form action="pages.php" method="post">
		<?php if($user->has_access(3, 2)){ ?>
		<select name="actions" style="width: 250px;">
			<option>Отмеченные</option>
			<option value="1">Опубликовать</option>
			<option value="2">Сделать черновиками</option>
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
		}?><br>
		<table class="manage">
		<?php
		$link1 = UCMS_DIR."/admin/pages.php".(isset($_GET['status']) ? "?status=".$_GET['status']."&amp;" : "?")."orderby=title&amp;order=".$order.(isset($_GET['page']) ? "&amp;page=".$_GET['page'] : "");
		$link2 = UCMS_DIR."/admin/pages.php".(isset($_GET['status']) ? "?status=".$_GET['status']."&amp;" : "?")."orderby=author&amp;order=".$order.(isset($_GET['page']) ? "&amp;page=".$_GET['page'] : "");
		$link3 = UCMS_DIR."/admin/pages.php".(isset($_GET['status']) ? "?status=".$_GET['status']."&amp;" : "?")."orderby=date&amp;order=".$order.(isset($_GET['page']) ? "&amp;page=".$_GET['page'] : "");
		$mark = $order == "ASC" ? '↑' : '↓';
		?>
		<tr>
			<th style="width: 15px;"><input type="checkbox" name="select-all" value="1"></th>
			<th><a href="<?php echo $link1; ?>">Название <?php echo $mark; ?></a></th>
			<th><a href="<?php echo $link2; ?>">Автор <?php echo $mark; ?></a></th>
			<th style="width: 200px;">Статус</th>
			<th><a href="<?php echo $link3; ?>">Дата <?php echo $mark; ?></a></th>
			<th style="width: 115px;">Управление</th>
		</tr>
		<?php
		
		$pages = $udb->get_rows($sql);
		if($pages){
			for ($i = 0; $i < count($pages); $i++) { 
				$authors[] = $udb->parse_value($pages[$i]['author']);
			}

			$authors = implode("','", $authors);
			$authors = "'".$authors."'";
			$authors_meta = $udb->get_rows("SELECT `id`, `login`, `group` FROM `".UC_PREFIX."users` WHERE `id` in ($authors)");

			for($i = 0; $i < count($pages); $i++){
				for($j = 0; $j < count($authors_meta); $j++){
					if(isset($page_author_id) and ($pages[$i]['author'] === $page_author_id)) break;
					if($pages[$i]['author'] === $authors_meta[$j]['id']){
						$page_author_id = $authors_meta[$j]['id'];
						$page_author_login = $authors_meta[$j]['login'];
						$page_author_group = $authors_meta[$j]['group'];
						break;
					}else{
						unset($page_author_id);
						$page_author_login = $pages[$i]['author'];
						$page_author_group = 6;
					}
				}
				$status = $pages[$i]['publish'] == 1 ? "Опубликована" : "Черновик";
				$link = NICE_LINKS ? page_sef_links($pages[$i]) : UCMS_DIR.'/?p='.$pages[$i]['id'];
				if($user_id == $pages[$i]['author'])
					$accessLVL = 3;
				elseif($page_author_group == 1){
					$accessLVL = 6;
				}else $accessLVL = 4;
				?>
				<tr>
					<td><input type="checkbox" name="item[]" value="<?php echo $pages[$i]['id']; ?>"></td>
					<td><a target="_blank" href="<?php echo $link; ?>"><?php echo $pages[$i]['title']; ?></a></td>
					<td><b><?php 
					if((int) $pages[$i]['author'] == 0){
						echo $pages[$i]['author'];
					}else{
						echo $page_author_login; 
					}
					?></b></td>
					<td><?php echo $status; ?></td>
						<td><?php echo $ucms->format_date($pages[$i]['date']); ?></td>
					<td><span class="actions"><?php if($user->has_access(3, $accessLVL)){ ?><a href="<?php echo UCMS_DIR ?>/admin/pages.php?action=update&amp;id=<?php echo $pages[$i]['id']?>">Изменить</a><?php } ?><?php if($user->has_access(3, $accessLVL+1)){ ?> | <a href="<?php echo UCMS_DIR ?>/admin/pages.php?action=delete&amp;id=<?php echo $pages[$i]['id'];?>">Удалить</a><?php } ?></span></td>
				</tr>
				<?php	
			}
		}else{
			?>
			<tr>
				<td colspan="6" style="text-align:center;">Страниц пока нет.</td>
			</tr>
			<?php
		}
			echo '</table></form>';
	}
			
	function delete_page($id){
		global $user, $udb;
		if(!$id){
			return false;
		}
		else{
			$id = (int) $id;
			$page = $udb->get_row("SELECT `author` FROM `".UC_PREFIX."pages` WHERE `id` = '$id' LIMIT 1");
			$userd = $udb->get_row("SELECT `id`, `group` FROM `".UC_PREFIX."users` WHERE `id` = '$page[author]' LIMIT 1");
			if($page){
				if($userd){
					if($userd['id'] == $user->get_user_id()){
						$accessLVL = 2;
					}elseif($userd['group'] == 1){
						$accessLVL = 6;
					}else{
						$accessLVL = 4;
					}
				}else{
					$accessLVL = 4;
				}
			}else{
				header("Location: ".UCMS_DIR."/admin/pages.php");
				return false;
			}
			if($user->has_access(3, $accessLVL)){
				$del = $udb->query("DELETE FROM `".UC_PREFIX."pages` WHERE `id` = '$id'");
				if($del){
					header("Location: ".UCMS_DIR."/admin/pages.php");
					$_SESSION['success_del'] = true;
				}
			}else{
				header("Location: ".UCMS_DIR."/admin/pages.php");
				return false;
			}
		}
	}
			
	function update_page_form($id){
		global $udb, $months, $user;
		$id = $udb->parse_value($id);
		$sql = "SELECT * FROM `".UC_PREFIX."pages` WHERE `id` = '$id'";
		$page = $udb->get_row($sql);
		if($page and count($page) > 0){
			?>
			<form method="post" action="pages.php">
				<input type="hidden" name="update" value="true" >
				<input type="hidden" name="id" value="<?=$page['id']?>" >
				<input type="hidden" id="body" name="body" value="<?php echo htmlspecialchars($page['body'])?>">
				<table class="forms" style="width:100%">
					<tr>
						<td width="80px"><label for="title">Заголовок:</label></td>
						<td><input type="text" name="title" id="title" value="<?=$page['title']?>"></td> 
					</tr>
					<tr>
						<td width="80px"><label for="title">Ссылка:</label></td>
						<td><input type="text" name="alias" id="alias" value="<?=$page['alias']?>"></td> 
					</tr>
					<tr><td></td><td>
					<nobr>
					<input class="editb" type="button" value="Ж" style="font-weight: bold;" onclick='setBold()'>
					<input class="editb" type="button" value="К" style="font-style: italic;" onclick="setItal()">
					<input class="editb" type="button" value="П" style="text-decoration: underline;" onclick="setUnderline()">
					<input class="editb" type="button" value="Ссылка" onclick="setLink()">
					<input class="editb" type="button" value="Изображение" onclick="setImage()">
					<input class="editb" type="button" value="Слева" onclick="setLeft()">
					<input class="editb" type="button" value="По центру" onclick="setCenter()">
					<input class="editb" type="button" value="Справа" onclick="setRight()">
					<input class="editb" type="button" style="width: 200px" value="Очистить форматирование" onclick="RemoveFormat()">
					<input id="htmlb" class="editb" type="button" value="HTML-код" onclick="ShowHTML()"></nobr>
					<input id="wysiwygb" style="display:none; width: 200px" class="editb" type="button" value="Визуальный редактор" onclick="ShowWYSIWSYG()">
					</nobr>
					</td></tr>
					<tr>
						<td><label for="editor">Текст:</label></td> 
						<td><iframe scrolling="yes" frameborder="no" style="border: 1px solid #aaadaa" src="#" id="editor" name="editor" width="100%" height="600"></iframe><textarea style="display: none;" name="html-code" id="html-code" Rows="30" cols="500" ></textarea></td>
					</tr>
					<?php if($user->has_access(1, 4)){ ?>
					<tr>
						<td>Автор:</td>
						<td><input type="text" value="<?php 
							if((int) $page['author'] == 0)
								echo $page['author'];
							else
								echo $user->get_user_login($page['author']); 
						?>" id="author" name="author"></td>
					</tr>
					<tr>
						<td>Дата:</td>
						<td>
							<select name="day" style="width:100px;">
								<?php
								$date = explode(" ", $page['date']);
								$time = explode(":", $date[1]);
								$date = explode("-", $date[0]);
								echo "<option value=".$date[2].">".$date[2]."</option>";
								echo "<option value=".date('d').">".date('d')."</option>";
								for ($i = 1; $i <= 31; $i++) {
									$d = $i < 10 ? "0$i" : $i;
									echo "<option value=\"$d\">$d</option>";
								}
								?>
							</select>
							<select name="month" style="width:100px;">
								<?php
								echo "<option value=".$date[1].">".$months[$date[1]]."</option>";
								echo "<option value=".date('m').">".$months[date('m')]."</option>";
								for ($i = 1; $i <= 12; $i++) {
									$m = $i < 10 ? "0$i" : $i;
									echo "<option value=\"$m\">$months[$m]</option>";
								}
								?>
							</select>
							<select name="year" style="width:100px;">
								<?php
								echo "<option value=".$date[0].">".$date[0]."</option>";
								echo "<option value=".date('Y').">".date('Y')."</option>";
								for ($i = date('Y'); $i >= 1900; $i--) {
									echo "<option value=\"$i\">$i</option>";
								}
								?>
							</select>
							в
							<input type="text" name="hour" style="width: 15px; height: 15px;" value="<?php echo $time[0]; ?>"> :
							<input type="text" name="minute" style="width: 15px; height: 15px;" value="<?php echo $time[1]; ?>"> :
							<input type="text" name="second" style="width: 15px; height: 15px;" value="<?php echo $time[2]; ?>">
						</td>
					</tr>
					<?php } ?>
					<tr>
						<td>Опубликовать</td>
						<td><input type="checkbox" value="1" id="publish" name="publish" <?php if($page['publish'] == 1) echo "checked"; ?>></td>
					</tr>
					<tr>
						<td></td>
						<td><input type="submit" name="submit" class="ucms-button-submit" onclick='makePublish()' value="Изменить" /></td>
					</tr>
				</table>
			</form>
			<?php
			}else{
				header("Location: pages.php");
			}
		}
	function update_page($p){
		global $udb, $user;
		$id = (int) $p['id'];
		$alias = $udb->parse_value($p['alias']);
		$title = $udb->parse_value($p['title']);
		$body = $udb->parse_value($p['body']);
		$publish = (int) $p['publish'];
		$author = isset($p['author']) ? $user->get_user_id($udb->parse_value($p['author'])) : $user->get_user_id();
		if(isset($p['day'])){
			$day = (int) $p['day'];
			$month = (int) $p['month'];
			$year = (int) $p['year'];
			$hour = (int) $p['hour'];
			$minute = (int) $p['minute'];
			$second = (int) $p['second'];
			$date = "$year-$month-$day $hour:$minute:$second";
		}else $date = date("Y-m-d H:i:s");
		if(!$author)
			$author = $udb->parse_value($p['author']);
		if(empty($title) or empty($body)){
			echo '<br><div class="error">Нужно обязательно заполнить поля "Заголовок" и "Текст".</div>';
		}	
		else{
			$sql= "UPDATE `".UC_PREFIX."pages` SET `title` = '$title', `body` = '$body', `publish` = '$publish', `alias` = '$alias', `author` = '$author', `date` = '$date' WHERE `id` = '$id'";
			$upd = $udb->query($sql);
			if($upd){
				header("Location: ".UCMS_DIR."/admin/pages.php");
				$_SESSION['success_upd'] = true;
			}	
		}
	}
		
?>
