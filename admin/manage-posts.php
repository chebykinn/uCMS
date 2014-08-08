<?php
	function add_post_form(){
		global $user, $udb, $months;
		?>
		<form method="post" action="posts.php">
			<input type="hidden" name="add" value="true">
			<input type="hidden" name="author" value="<?=$user->get_user_id() ?>">	
			<input type="hidden" id="body" name="body">	
			<table class="forms" style="width:100%">
				<tr>
					<td width="80px"><label for="title">Заголовок:</label></td>
					<td><input type="text" name="title" id="title" required></td> 
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
					<td><iframe scrolling="yes" frameborder="no" style="border: 1px solid #aaadaa" src="#" id="editor" name="editor" width="100%" height="600"></iframe><textarea style="display: none;" name="html-code" id="html-code" Rows="30" cols="500"></textarea></td> 
					<tr>		
						<td><label for="keywords">Теги:</label></td>
						<td><input type="text" name="keywords" id="keywords"></td>
					</tr>
				</tr>
				<tr>
					<td>Категория:</td>
					<td><select name="category" size="1">
					<?php
					$catlist = "SELECT * FROM `".UC_PREFIX."categories`";
					$l_categories = $udb->get_rows($catlist);
					for($i = 0; $i < count($l_categories); $i++){
						echo '<option value="'.$l_categories[$i]['id'].'">'.$l_categories[$i]['name'].'</option>';
					}
					?>
					</select></td>
		
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
				<tr>
					<td>Закрепить</td>
					<td><input type="checkbox" value="2" id="pin" name="pin"></td>
				</tr>
				<?php } ?>
				<tr>
					<td>Опубликовать</td>
					<td><input type="checkbox" value="1" id="publish" name="publish" checked></td>
				</tr>
				<tr>
					<td>Отключить комментирование</td>
					<td><input type="checkbox" value="-1" id="comment" name="comment"></td>
				</tr>
				<tr>
					<td></td>
					<td><input type="submit" name="submit" class="ucms-button-submit" onclick="makePublish()" value="Добавить" /></td>
				</tr>
			</table>
		</form>
		<?php
	}

	function add_post($p){
		global $udb, $ucms, $user;
		$title = $udb->parse_value($p['title']);
		$body = $udb->parse_value($p['body']);
		$keywords = $udb->parse_value($p['keywords']);
		$publish = (int) $p['publish'];
		if(isset($p['pin'])) $publish = 2;
		$comment = isset($p['comment']) ? (int) $p['comment'] : 0;
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
			$author = $udb->parse_value($p['author']);
		if((int) $author == 0 and !$user->logged()){
			$author = isset($_SESSION['guest_login']) ? $_SESSION['guest_login'] : "Гость";
		}
		$category = (int) $p['category'];
		if(empty($title) or empty($body)){
			echo '<br><div class="error">Нужно обязательно заполнить поля "Заголовок" и "Текст".</div>';
		}	
		else if($user->has_access(1, 2)){
			if(empty($alias)){
				$alias = $ucms->transliterate($title);
				$alias = preg_replace('/\s/', "_", $alias);
			}
			if($publish)
				$udb->query("UPDATE `".UC_PREFIX."categories` SET `posts` = `posts` + 1 WHERE `id` = '$category'");
			$sql = "INSERT INTO `".UC_PREFIX."posts` VALUES (null, '$title','$body', '$keywords', '$publish', '$alias', '$author', '$category', '$comment', '$date')";
			$add = $udb->query($sql);
			if($add){
				header("Location: ".UCMS_DIR."/admin/posts.php");
				$_SESSION['success_add'] = true;
			}else echo '<div class="error">Произошла ошибка при добавлении поста.</div>';
			
		}
	}
		
		function manage_posts(){
			global $user, $udb, $ucms;
			if (isset($_POST['item']) and isset($_POST['actions'])){
				$items = array();
				$action = (int) $_POST['actions'];
				foreach ($_POST['item'] as $id) {
					$id = (int) $id;
					$post = $udb->get_row("SELECT `author` FROM `".UC_PREFIX."posts` WHERE `id` = '$id' LIMIT 1");
					if($post){
						if(!empty($post['author'])){
							if($user->get_user_id() == $post['author']){
								$accessLVL = 2;
							}elseif($user->get_user_group($post['author']) == 1){
								$accessLVL = 6;
							}else{
								$accessLVL = 4;
							}
						}
					}
					if($action == 4) $accessLVL++;
					if($user->has_access(1, $accessLVL)){
						$items[] = $id;
					}
				}
				$ids = implode(',', $items);
				if (count($items) > 0) {
					switch ($action) {
						case 1:
							$upd = $udb->query("UPDATE `".UC_PREFIX."posts` SET `publish` = '1' WHERE `id` IN ($ids)");
							if (count($items) > 1) {
								$_SESSION['success_updm'] = true;
							}else 
								$_SESSION['success_upd'] = true;
 							header("Location: ".UCMS_DIR."/admin/posts.php");
						break;
						
						case 2:
							$upd = $udb->query("UPDATE `".UC_PREFIX."posts` SET `publish` = '2' WHERE `id` IN ($ids)");
							if (count($items) > 1) {
								$_SESSION['success_updm'] = true;
							}else 
								$_SESSION['success_upd'] = true;
 							header("Location: ".UCMS_DIR."/admin/posts.php");
						break;

						case 3:
							$upd = $udb->query("UPDATE `".UC_PREFIX."posts` SET `publish` = '0' WHERE `id` IN ($ids)");
							if (count($items) > 1) {
								$_SESSION['success_updm'] = true;
							}else 
								$_SESSION['success_upd'] = true;
 							header("Location: ".UCMS_DIR."/admin/posts.php");
						break;
		
						case 4:
							$del = $udb->query("DELETE FROM `".UC_PREFIX."posts` WHERE `id` IN ($ids)");
							if (count($items) > 1) {
								$_SESSION['success_delm'] = true;
							}else 
								$_SESSION['success_del'] = true;
 							header("Location: ".UCMS_DIR."/admin/posts.php");
						break;
						
					}
				}
			}
			$call = $udb->num_rows("SELECT * FROM `".UC_PREFIX."posts`");
			$cpublished = $udb->num_rows("SELECT * FROM `".UC_PREFIX."posts` WHERE `publish` = 1");
			$cdraft = $udb->num_rows("SELECT * FROM `".UC_PREFIX."posts` WHERE `publish` = 0");
			$cpinned = $udb->num_rows("SELECT * FROM `".UC_PREFIX."posts` WHERE `publish` = 2");
			$perpage = 25;
			$user_id = $user->get_user_id();
			$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
			$columns = array('title','author', 'comments', 'date');
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
				
				case 'pinned':
					$swhere = "WHERE `publish` = 2";
				break;

				default:
					$swhere = "";
				break;
			}
			if($page <= 0) $page = 1;
			if($user->has_access(1, 4))
				$count = $udb->num_rows("SELECT * FROM `".UC_PREFIX."posts` $swhere ORDER BY `$orderby` $order");
			else
				$count = $udb->num_rows("SELECT * FROM `".UC_PREFIX."posts` WHERE `author` = '$user_id' ORDER BY `$orderby` $order");
			$pages_count = 0;
			if($count != 0){ 
				$pages_count = ceil($count / $perpage); 
				if ($page > $pages_count):
					$page = $pages_count;
				endif; 
				$start_pos = ($page - 1) * $perpage;
				if($user->has_access(1, 4))
					$sql = "SELECT * FROM `".UC_PREFIX."posts` $swhere ORDER BY `$orderby` $order LIMIT $start_pos, $perpage";
				else
					$sql = "SELECT * FROM `".UC_PREFIX."posts` WHERE `author` = '$user_id' ORDER BY `$orderby` $order LIMIT $start_pos, $perpage";
			}else{
				$sql = "SELECT * FROM `".UC_PREFIX."posts` WHERE `id` = '0'";
			}

			$lall = $status != '' ? "<a href=\"".UCMS_DIR."/admin/posts.php".(isset($_GET['orderby']) ? "?orderby=".$_GET['orderby'] : "").(isset($_GET['order']) ? "&amp;order=".$_GET['order'] : "").(isset($_GET['page']) ? ((isset($_GET['orderby']) and isset($_GET['order'])) ? "&amp;" : "?")."page=".$_GET['page'] : "")."\">Все</a>" : "<b>Все</b>"; 
			$lpublished = $status != 'published' ? "<a href=\"".UCMS_DIR."/admin/posts.php?status=published".(isset($_GET['orderby']) ? "&amp;orderby=".$_GET['orderby'] : "").(isset($_GET['order']) ? "&amp;order=".$_GET['order'] : "").(isset($_GET['page']) ? "&amp;page=".$_GET['page'] : "")."\">Опубликованные</a>" : "<b>Опубликованные</b>"; 
			$ldraft =  $status != 'draft' ? "<a href=\"".UCMS_DIR."/admin/posts.php?status=draft".(isset($_GET['orderby']) ? "&amp;orderby=".$_GET['orderby'] : "").(isset($_GET['order']) ? "&amp;order=".$_GET['order'] : "").(isset($_GET['page']) ? "&amp;page=".$_GET['page'] : "")."\">Черновики</a>" : "<b>Черновики</b>"; 
			$lpinned =  $status != 'pinned' ? "<a href=\"".UCMS_DIR."/admin/posts.php?status=pinned".(isset($_GET['orderby']) ? "&amp;orderby=".$_GET['orderby'] : "").(isset($_GET['order']) ? "&amp;order=".$_GET['order'] : "").(isset($_GET['page']) ? "&amp;page=".$_GET['page'] : "")."\">Закрепленные</a>" : "<b>Закрепленные</b>"; 
			
			?>
			<br>
			Показывать: <?php echo $lall." ($call)"; ?> | <?php echo $lpinned." ($cpinned)"; ?> | <?php echo $lpublished." ($cpublished)"; ?> | <?php echo $ldraft." ($cdraft)"; ?>
			<br><br>
			<form action="posts.php" method="post">
			<?php if($user->has_access(1, 2)){ ?>
			<select name="actions" style="width: 250px;">
				<option>Отмеченные</option>
				<option value="1">Опубликовать</option>
				<option value="2">Закрепить</option>
				<option value="3">Сделать черновиками</option>
				<option value="4">Удалить</option>
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
			$link1 = UCMS_DIR."/admin/posts.php?orderby=title&amp;order=".$order.(isset($_GET['page']) ? "&amp;page=".$_GET['page'] : "");
			$link2 = UCMS_DIR."/admin/posts.php?orderby=author&amp;order=".$order.(isset($_GET['page']) ? "&amp;page=".$_GET['page'] : "");
			$link3 = UCMS_DIR."/admin/posts.php?orderby=comments&amp;order=".$order.(isset($_GET['page']) ? "&amp;page=".$_GET['page'] : "");
			$link4 = UCMS_DIR."/admin/posts.php?orderby=date&amp;order=".$order.(isset($_GET['page']) ? "&amp;page=".$_GET['page'] : "");
			$mark = $order == "ASC" ? '↑' : '↓';
			?>
			<tr>
				<th style="width: 15px;"><input type="checkbox" name="select-all" value="1"></th>
				<th><a href="<?php echo $link1; ?>">Название <?php echo $mark; ?></a></th>
				<th>Категория</th>
				<th><a href="<?php echo $link2; ?>">Автор <?php echo $mark; ?></a></th>
				<th><a href="<?php echo $link3; ?>">Комментарии <?php echo $mark; ?></a></th>
				<th>Теги</th>
				<th>Статус</th>
				<th><a href="<?php echo $link4; ?>">Дата <?php echo $mark; ?></a></th>
				<th style="width: 115px;">Управление</th>
			</tr>
			<?php
			$m_posts = $udb->get_rows($sql);
			if($m_posts){
				$p_count = count($m_posts);
			}else $p_count = 0;
			if($p_count != 0){
				for ($i = 0; $i < count($m_posts); $i++) { 
					$authors[] = $udb->parse_value($m_posts[$i]['author']);
					$categories[] = $udb->parse_value($m_posts[$i]['category']);
				}
	
				$authors = implode("','", $authors);
				$authors = "'".$authors."'";

				$categories = implode("','", $categories);
				$categories = "'".$categories."'";

				$authors_meta = $udb->get_rows("SELECT `id`, `login`, `group` FROM `".UC_PREFIX."users` WHERE `id` in ($authors)");
				$categories_meta = $udb->get_rows("SELECT `id`, `name` FROM `".UC_PREFIX."categories` WHERE `id` in ($categories)");
				
				for($i = 0; $i < $p_count; $i++){
					for($j = 0; $j < count($authors_meta); $j++){
						if(isset($post_author_id) and ($m_posts[$i]['author'] === $post_author_id)) break;
						if($m_posts[$i]['author'] === $authors_meta[$j]['id']){
							$post_author_id = $authors_meta[$j]['id'];
							$post_author_login = $authors_meta[$j]['login'];
							$post_author_group = $authors_meta[$j]['group'];
							break;
						}else{
							unset($post_author_id);
							$post_author_login = $m_posts[$i]['author'];
							$post_author_group = 6;
						}
					}

					for($j = 0; $j < count($categories_meta); $j++){
						if(isset($post_category_id) and ($m_posts[$i]['category'] === $post_category_id)) break;
						if($m_posts[$i]['category'] === $categories_meta[$j]['id']){
							$post_category_id = $categories_meta[$j]['id'];
							$post_category_name = $categories_meta[$j]['name'];
							break;
						}
					}
					switch ($m_posts[$i]['publish']) {
						case 0:
							$status = 'Черновик';
						break;

						case 1:
							$status = 'Опубликован';
						break;

						case 2:
							$status = 'Закреплен';
						break;

						default:
							$status = 'Опубликован';
						break;
					}
					$cstatus = $m_posts[$i]['comments'] < 0 ? ' | Комментарии отключены' : '';
					$link = NICE_LINKS ? post_sef_links($m_posts[$i]) : UCMS_DIR.'/?id='.$m_posts[$i]['id'];
					if($user_id == $m_posts[$i]['author'])
						$accessLVL = 2;
					elseif($post_author_group == 1){
						$accessLVL = 6;
					}else
						$accessLVL = 4;
					?>
					<tr>
						<td><input type="checkbox" name="item[]" value="<?php echo $m_posts[$i]['id']; ?>"></td>
						<td style="width: 25%"><a target="_blank" href="<?php echo $link; ?>"><?php echo $m_posts[$i]['title']; ?></a></td>
						<td><?php echo $post_category_name; ?></td>
						<td><b><?php
							if((int) $m_posts[$i]['author'] == 0){
								echo $m_posts[$i]['author'];
							}else
						 		echo $post_author_login; 
						 ?></b></td>
						<td><?php if($m_posts[$i]['comments'] < 0) echo "Комментарии отключены"; else echo $m_posts[$i]['comments']; ?></td>
						<td><?php echo $m_posts[$i]['keywords']; ?></td>						
						<td><?php echo $status; ?></td>
						<td><?php echo $ucms->format_date($m_posts[$i]['date']); ?></td>
						<td><span class="actions"><?php if($user->has_access(1, $accessLVL)){ ?><a href="posts.php?action=update&amp;id=<?php echo $m_posts[$i]['id']?>">Изменить</a><?php } ?><?php if($user->has_access(1, $accessLVL+1)){ ?> | <a href="?action=delete&amp;id=<?php echo $m_posts[$i]['id'];?>">Удалить</a><?php } ?></span></td>
					</tr>
					<?php	
				};
			}else{
			?>
				<tr>
					<td colspan="9" style="text-align:center;">Постов пока нет.</td>
				</tr>
			<?php
			}
			echo '</table></form>';
		}
			
		function delete_post($id){
			global $user, $udb;	
			if(!$id)
				return false;
			else{
				$id = (int) $id;
				$post = $udb->get_row("SELECT `author` FROM `".UC_PREFIX."posts` WHERE `id` = '$id' LIMIT 1");
				$userd = $udb->get_row("SELECT `id`, `group` FROM `".UC_PREFIX."users` WHERE `id` = '$post[author]' LIMIT 1");
				if($post){
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
					header("Location: ".UCMS_DIR."/admin/posts.php");
					return false;
				}
				if($user->has_access(1, $accessLVL)){
					$del = $udb->query("DELETE FROM `".UC_PREFIX."posts` WHERE `id` = '$id'");
					$udb->query("DELETE FROM `".UC_PREFIX."comments` WHERE `post` = '$id'");
					if($del){
						header("Location: ".UCMS_DIR."/admin/posts.php");
						$_SESSION['success_del'] = true;
					}else echo '<div class="error">Произошла ошибка при удалении поста.</div>';
				}else{
					header("Location: ".UCMS_DIR."/admin/posts.php");
					return false;
				}
			}
		}
			
		function update_post_form($id){
			global $user, $udb, $months;
			$id = $udb->parse_value($id);
			$sql = "SELECT * FROM `".UC_PREFIX."posts` WHERE `id` = '$id' LIMIT 1";
			$post = $udb->get_row($sql);
			if($post and count($post) > 0){
				$cat = "SELECT * FROM `".UC_PREFIX."categories` WHERE `id` = '$post[category]'";
				$category = $udb->get_row($cat);
				if($user->get_user_id() == $post['author']){
					$accessLVL = 2;
				}elseif($user->get_user_group($post['author']) == 1){
					$accessLVL = 6;
				}else $accessLVL = 4;
				if($user->has_access(1, $accessLVL)){
				?>
				<form method="post" action="posts.php">
					<input type="hidden" name="update" value="true" >
					<input type="hidden" name="id" value="<?=$post['id']?>" >
					<input type="hidden" name="author" value="<?=$post['author']?>" >
					<input type="hidden" id="body" name="body" value="<?php echo htmlspecialchars($post['body'])?>">	
					<table class="forms" style="width:100%">
						<tr>
							<td width="80px"><label for="title">Заголовок:</label></td>
							<td><input type="text" name="title" id="title" value="<?=$post['title']?>"></td> 
						</tr>
						<tr>
							<td width="80px"><label for="title">Ссылка:</label></td>
							<td><input type="text" name="alias" id="alias" value="<?=$post['alias']?>"></td> 
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
							<td><iframe scrolling="auto" frameborder="no" style="border: 1px solid #aaadaa" src="#" id="editor" name="editor" width="98%" height="600"></iframe><textarea style="display: none;" name="html-code" id="html-code" rows="30" cols="500" ></textarea></td>
						<tr>		
							<td><label for="keywords">Теги:</label></td>
							<td><input type="text" name="keywords" id="keywords" value="<?=$post['keywords']?>"></td>
						</tr>
						<tr>
							<td>Категория:</td>
							<td><select name="category" size="1">
							<?php
								echo '<option selected value="'.$post['category'].'">'.$category['name'].'</option>';
								$catlist = "SELECT * FROM `".UC_PREFIX."categories`";
								$cats = $udb->get_rows($catlist);
								for($i = 0; $i < count($cats); $i++){
									echo '<option value="'.$cats[$i]['id'].'">'.$cats[$i]['name'].'</option>';
								}
								?>
							</select>
							</td>
						</tr>
						<?php if($user->has_access(1, 4)){ ?>
						<tr>
							<td>Автор:</td>
							<td><input type="text" value="<?php 
								if((int) $post['author'] == 0)
									echo $post['author'];
								else{
									echo $user->get_user_login($post['author']);
								} 
							?>" id="author" name="author"></td>
						</tr>
						<tr>
							<td>Дата:</td>
							<td>
								<select name="day" style="width:100px;">
									<?php
									$date = explode(" ", $post['date']);
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
						<tr>
							<td>Закрепить</td>
							<td><input type="checkbox" value="2" id="pin" name="pin" <?php if($post['publish'] == 2) echo "checked"; ?>></td>
						</tr>
						<?php } ?>
						<tr>
							<td>Опубликовать</td>
							<td><input type="checkbox" value="1" id="publish" name="publish" <?php if($post['publish'] > 0) echo "checked"; ?>></td>
						</tr>
						<tr>
							<td>Отключить комментирование</td>
							<td><input type="checkbox" value="-1" id="comment" name="comment" <?php if($post['comments'] < 0) echo "checked"; ?>></td>
						</tr>
						<tr>
							<td></td>
							<td><input type="submit" name="submit" class="ucms-button-submit" onclick="makePublish()" value="Изменить" /></td>
						</tr>
					</table>
				</form>
				<?php
				}
			}else{
				header("Location: posts.php");
			}
		}
		function update_post($p){
			global $udb, $ucms, $user;
			$id = (int) $p['id'];
			$title = $udb->parse_value($p['title']);
			$body = $udb->parse_value($p['body']);
			$keywords = $udb->parse_value($p['keywords']);
			$publish = (int) $p['publish'];
			if(isset($p['pin'])) $publish = 2;
			$comment = isset($p['comment']) ? (int) $p['comment'] : 0;
			$alias = $udb->parse_value($p['alias']);
			$author = isset($p['author']) ? $user->get_user_id($udb->parse_value($p['author'])) : $user->get_user_id();
			$category = (int) $p['category'];
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
			if($author == $user->get_user_id()){
				$accessLVL = 2;
			}elseif($user->get_user_group($author) == 1){
				$accessLVL = 6;
			}else $accessLVL = 4;
			if(empty($title) or empty($body)){
				echo '<br><div class="error">Нужно обязательно заполнить поля "Заголовок" и "Текст".</div>';
			}	
			else if($user->has_access(1, $accessLVL)){
				if(empty($alias)){
					$alias = $ucms->transliterate($title);
					$alias = preg_replace('/\s/', "_", $alias);
				}
				$test = $udb->get_row("SELECT `category`, `publish` FROM `".UC_PREFIX."posts` WHERE `id` = '$id'");
				if($test['publish'] == 0 and $publish >= 1) $udb->query("UPDATE `".UC_PREFIX."categories` SET `posts` = `posts` + 1 WHERE `id` = '$category'");
				else if($test['publish'] >= 1 and $publish == 0) $udb->query("UPDATE `".UC_PREFIX."categories` SET `posts` = `posts` - 1 WHERE `id` = '$category'");
				if($test['category'] != $category and $test['publish'] == 1){
					$udb->query("UPDATE `".UC_PREFIX."categories` SET `posts` = `posts` + 1 WHERE `id` = '$category'");
					$udb->query("UPDATE `".UC_PREFIX."categories` SET `posts` = `posts` - 1 WHERE `id` = '$test[category]'");
				}
				if($comment > -1){
					$comment = $udb->num_rows("SELECT `id` FROM `".UC_PREFIX."comments` WHERE `post` = '$id'");
				}else
					$udb->query("DELETE FROM `".UC_PREFIX."comments` WHERE `post` = '$id'");
				$sql = "UPDATE `".UC_PREFIX."posts` SET `title` = '$title', `body` = '$body', `keywords` = '$keywords', `publish` = '$publish', `alias` = '$alias', `category` = '$category', `comments` = '$comment', `author` = '$author', `date` = '$date' WHERE `id` = '$id'";
				$update = $udb->query($sql);
				if($update){
					header("Location: ".UCMS_DIR."/admin/posts.php");
					$_SESSION['success_upd'] = true;
				}else echo '<div class="error">Произошла ошибка при обновлении поста.</div>';
				
			}
		}

		function manage_categories(){
			global $user, $ucms, $udb;
			if (isset($_POST['item']) and isset($_POST['actions'])){
			$items = array();
			$action = (int) $_POST['actions'];
			foreach ($_POST['item'] as $id) {
				$id = (int) $id;
				$items[] = $id;
			}
			$ids = implode(',', $items);
			if (count($items) > 0) {
				switch ($action) {
					case 2:
					foreach ($items as $item) {
						$posts = $udb->num_rows("SELECT `id` FROM `".UC_PREFIX."posts` WHERE `category` = '$item'");
						$upd = $udb->query("UPDATE `".UC_PREFIX."categories` SET `posts` = '$posts' WHERE `id` = '$item'");
					}
						
						if (count($items) > 1) {
							$_SESSION['success_updm'] = true;
						}else 
							$_SESSION['success_upd'] = true;
 						header("Location: ".UCMS_DIR."/admin/categories.php");
					break;
	
					case 3:
						$del = $udb->query("DELETE FROM `".UC_PREFIX."categories` WHERE `id` IN ($ids)");
						$udb->query("UPDATE `".UC_PREFIX."posts` SET `category` = '1' WHERE `category` IN ($ids)");
						if (count($items) > 1) {
							$_SESSION['success_delm'] = true;
						}else 
							$_SESSION['success_del'] = true;
 						header("Location: ".UCMS_DIR."/admin/categories.php");
					break;
					
				}
			}
		}
		$call = $udb->num_rows("SELECT * FROM `".UC_PREFIX."categories`");
		$perpage = 25;
		$user_id = $user->get_user_id();
		$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
		$columns = array('name','posts');
		$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
		$orderby = isset($_GET['orderby']) ? in_array($_GET['orderby'], $columns) ? $_GET['orderby'] : 'id' : 'id';
		$order = (isset($_GET['order']) and $_GET['order'] == 'DESC') ? 'ASC' : 'DESC';
		if($page <= 0) $page = 1;
		$count = $udb->num_rows("SELECT * FROM `".UC_PREFIX."categories` ORDER BY `$orderby` $order");
		$pages_count = 0;
		if($count != 0){ 
			$pages_count = ceil($count / $perpage); 
			if ($page > $pages_count):
				$page = $pages_count;
			endif; 
			$start_pos = ($page - 1) * $perpage;
			$sql = "SELECT * FROM `".UC_PREFIX."categories` ORDER BY `$orderby` $order LIMIT $start_pos, $perpage";
			
		}
		$link1 = UCMS_DIR."/admin/categories.php?orderby=name&amp;order=".$order.(isset($_GET['page']) ? "&amp;page=".$_GET['page'] : "");
		$link2 = UCMS_DIR."/admin/categories.php?orderby=posts&amp;order=".$order.(isset($_GET['page']) ? "&amp;page=".$_GET['page'] : "");
		$mark = $order == "ASC" ? '↑' : '↓';
		?>
		<br>
		<b>Всего категорий:</b> <?php echo $call; ?>
		<br><br>
		<form action="categories.php" method="post">
		<?php if($user->has_access(1, 7)){ ?>
		<select name="actions" style="width: 250px;">
			<option>Отмеченные</option>
			<option value="2">Пересчитать посты</option>
			<option value="3">Удалить</option>
		</select>
		<?php } ?>
		<input type="submit" value="Применить" class="ucms-button-submit">
		<br><br>
		<?php
		if($pages_count > 1){
			pages($page, $count, $pages_count, 15, false);
			echo '<br>';
			echo '<br>';
		}
		echo '<table class="manage">';
		?>
		<tr>
			<th style="width: 15px;"><input type="checkbox" name="select-all" value="1"></th>
			<th><a href="<?php echo $link1; ?>">Название <?php echo $mark; ?></a></th>
			<th>Ссылка</th>
			<th style="width: 50px;"><a href="<?php echo $link2; ?>">Посты <?php echo $mark; ?></a></th>
			<th style="width: 115px;">Управление</th>
		</tr>
		<?php
		
		if($count != 0){
			$category = $udb->get_rows($sql);
			for($i = 0; $i < count($category); $i++){
				?>
				<tr>
					<td><input type="checkbox" name="item[]" value="<?php echo $category[$i]['id']; ?>"></td>
					<td style="width: 300px;"><?php echo $category[$i]['name']; ?></td>
					<td><?php echo $category[$i]['alias']; ?></td>
					<td><?php echo $category[$i]['posts']; ?></td>
					<td><span class="actions"><a href="categories.php?update=<?php echo $category[$i]['id'];?>">Изменить</a> | <a href="categories.php?delete=<?php echo $category[$i]['id'];?>">Удалить</a></span></td>
				</tr>
				<?php	
			}
		}else{
		?>
			<tr>
				<td colspan="5" style="text-align:center;">Категории пока нет.</td>
			</tr>
		<?php
		}
		echo '</table></form>';
	}
		

	function add_category($p){
		global $udb;
		$name = $udb->parse_value($p['name']);
		$alias = $udb->parse_value($p['alias']);
		if($name != '' and $alias != ''){
			$add = $udb->query("INSERT IGNORE INTO `".UC_PREFIX."categories` VALUES(null, '$name', '$alias', '0')");
			if($add) {
				header("Location: ".UCMS_DIR."/admin/categories.php");
				$_SESSION['success_add'] = true;
			}else echo '<div class="error">Произошла ошибка при добавлении категории.</div>';
			
		}else echo '<div class="error">Вы заполнили не все поля.</div>';
	}

	function update_category($p){
		global $udb;
		$id = $p['id'] != '' ? (int) $p['id'] : 0;
		$name = $udb->parse_value($p['name']);
		$alias = $udb->parse_value($p['alias']);
		if($name != ''){
			$update = $udb->query("UPDATE `".UC_PREFIX."categories` SET `name` = '$name' WHERE `id` = '$id'");
		}
		if($alias != ''){
			$update = $udb->query("UPDATE `".UC_PREFIX."categories` SET `alias` = '$alias' WHERE `id` = '$id'");
		}
		if($update) {
			header("Location: ".UCMS_DIR."/admin/categories.php");
			$_SESSION['success_upd'] = true;
		}else echo '<div class="error">Произошла ошибка при обновлении категории.</div>';
	}

	function delete_category($id){
		global $udb, $user;
		$id = (int) $id;
		if($id > 1 and $user->has_access(1, 7)){
			$delete = $udb->query("DELETE FROM `".UC_PREFIX."categories` WHERE `id` = '$id' LIMIT 1");
			if($delete) {
				header("Location: ".UCMS_DIR."/admin/categories.php");
				$_SESSION['success_del'] = true;
			}else echo '<div class="error">Произошла ошибка при удалении категории.</div>';
		}
	}
?>