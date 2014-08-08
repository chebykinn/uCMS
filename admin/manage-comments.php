<?php 
	function add_comment($p){
		global $user, $ucms, $udb;
		$domain2 = preg_replace("#(http://)#", '', SITE_DOMAIN);
		$comment = parse_comment($p['comment']);
		$guest = ($user->has_access(2, 2) and !$user->logged()) ? true : false;
		if($guest){
			$author = ($user->has_access(2, 2) and !$user->logged()) ? $udb->parse_value($p['guest-name']) : $user->get_user_id();
			$email = $udb->parse_value($p['guest-email']);
		}else{
			$author = $user->get_user_id();
		}

		
		$post = (int) $p['post'];
		if(!$comment){
			return 1;
		}else{
			if(!$user->has_access(2, 1)){
				return 2;
			}
			if ($user->has_access(2, 3)){
				$udb->query("INSERT INTO `".UC_PREFIX."comments` VALUES (null, '$post', '$comment','$author', 1, NOW())");
				$comments_nums = $udb->num_rows("SELECT `id` FROM `".UC_PREFIX."comments` WHERE `approved` = 1 and `post` = $post");
				$com_count = $udb->query("UPDATE `".UC_PREFIX."posts` SET `comments` = '$comments_nums' WHERE `id` = '$post'");
			}else{
				if(isset($p['code'])){
					$code = $udb->parse_value($p['code']);
					$passed = check_code($code);
					if(!$passed)
						return 3;
				}
				$udb->query("INSERT INTO `".UC_PREFIX."comments` VALUES (null, '$post', '$comment','$author', 0, NOW())");
				if(COMMENTS_EMAIL != ''){
					$headers = "Content-type:text/html; charset=utf-8\r\n";
					$subject = 'Добавлен комментарий';
					$headers .= 'From: '.'=?UTF-8?B?'.base64_encode(SITE_NAME).'?='.' <ucms@'.$domain2.'>'."\r\n";
					$user_link = NICE_LINKS ? "".UCMS_URL."/users/".$user->get_user_login($author) : "".UCMS_URL."/?action=profile&amp;id=".$author;
					if(!$guest)
						$message = "На Вашем сайте \"<b>".SITE_NAME."</b>\" был добавлен комментарий пользователем <a href='$user_link'>".$user->get_user_login($author)."</a>, ".$ucms->get_date().".<br><br><b>Текст:</b><br><hr>$comment<br><hr> <a href='".UCMS_URL."/admin/comments.php'>Удалить или одобрить</a>.";
					else
						$message = "На Вашем сайте \"<b>".SITE_NAME."</b>\" был добавлен комментарий гостем $author<br>Email: $email, ".$ucms->get_date().".<br><br><b>Текст:</b><br><hr>$comment<br><hr> <a href='".UCMS_URL."/admin/comments.php'>Удалить или одобрить</a>.";
					mail(COMMENTS_EMAIL, '=?UTF-8?B?'.base64_encode($subject).'?=', $message, $headers."Content-type:text/html; Charset=utf-8\r\n");
				}
			}
			$_SESSION['add-comment'] = true;
		}
	}		
	
	function alert_added(){
		global $user;
		if(isset($_SESSION['add-comment']) and $_SESSION['add-comment']){
			echo '<div class="success">';
			if(!$user->has_access(2, 3)){
				echo "Ваш комментарий был успешно добавлен и появится на сайте после проверки.";
			}else{
				echo "Ваш комментарий был успешно добавлен.";
			}
			echo '</div>';
			unset($_SESSION['add-comment']);
		}
	}

	function check_code($code){
		if(isset($_SESSION['captcha-code'])){
   	 		$code2 = $_SESSION['captcha-code'];
   	 		if($code === $code2){
   	 			unset($_SESSION['captcha-code']);
   	 			return true;
   	 		}else{
   	 			unset($_SESSION['captcha-code']);
   	 			return false;
   	 		}
   		}else return false;
	}

	function add_comment_form(){
		global $id, $user;
		if (isset($id)){
			if(!$user->has_access(2, 2)){
				if(!$user->logged() and ALLOW_REGISTRATION){
					$register_link = NICE_LINKS ? UCMS_DIR.'/registration' : UCMS_DIR.'/?action=registration';
					echo '<br><a href="'.$register_link.'" >Зарегистрируйтесь</a>, чтобы оставить комментарий.';
				}else{
					echo '<br><div style="width: 400px;">Вы не можете добавлять комментарии.</div>';
				}	
			}else{
				?>
				<div style="clear: both;">
					<?php alert_added(); ?>
					<form method="post" action="<?php UCMS_DIR ?>/new-comment.php">
						<input type="hidden" name="post" value="<?=$id ?>" />
						<table style="width:100%;">
							<tr>
								<td><b><label for="comment">Написать комментарий:</label></b></td> 
							</tr>
							<?php
								if(!$user->logged()){
									echo "
									<tr>
										<td><label for=\"guest-name\">Ваше имя: <span style=\"color:#ff0000;\">*</span></label></td> 
									</tr>
									<tr>
										<td><input name=\"guest-name\" type=\"text\" value=\"Гость\" required></td> 
									</tr>
									<tr>
										<td><label for=\"guest-email\">Ваш email: <span style=\"color:#ff0000;\">*</span></label></td> 
									</tr>
									<tr>
										<td><input name=\"guest-email\" type=\"email\" required></td> 
									</tr>";
								}
							?>
							
							<tr>
								<td><br><textarea name="comment" id="comment" cols="80" rows="10" tabindex="4"></textarea></td> 
							</tr>
							<?php 
								$captcha = false;
								if(USE_CAPTCHA === 2){
									if(!$user->logged()){
										$captcha = true;
									}
								}elseif(USE_CAPTCHA === 3){
									if(!$user->has_access(2, 3)){
										$captcha = true;
									}
								}
							if($captcha){ ?>
							<tr>
								<td><label><b>Введите код с картинки: </b><span style="color:#ff0000;">*</span></label></td> 
									
							</tr>
							<tr>
								<td><img src="<?php echo UCMS_DIR; ?>/sys/users/code/capcha-img.php" alt=""></td>	
							</tr>
							<tr>
								<td><input type="text" name="code" required></td>
							</tr>
							<?php } ?>
							<tr>
								<td><input type="submit" name="submit" value="Ответить" /></td>
							</tr>
						</table>	
					</form>
				</div>
				<?php
			}
		}
	}
	
	function update_comment_form($id){
		global $udb, $user, $months;
		$id = (int) $id;
		$row = $udb->get_row("SELECT * FROM `".UC_PREFIX."comments` WHERE `id` = '$id' LIMIT 1");
		if($user->get_user_id() == $row['author']){
			$accessLVL = 3;
		}elseif($user->get_user_group($row['author']) == 1){
			$accessLVL = 6;
		}else $accessLVL = 4;
		if($row and count($row) > 0){
			if($user->has_access(2, $accessLVL)){
			?>
			<form method="post" action="comments.php">
				<input type="hidden" name="update" value="true" >
				<input type="hidden" name="referer" value="<?php echo $user->get_back_url(); ?>" >
				<input type="hidden" name="id" value="<?=$row['id']?>" >
				<table class="forms">
					<tr>
						<td><label for="body">Текст:</label></td> 
						<td><textarea style="width: 500px; height: 250px;" name="comment" id="comment" ><?=$row['comment']?></textarea></td>
					</tr>
					<?php if($user->has_access(1, 4)){ ?>
						<tr>
							<td>Автор:</td>
							<td><input type="text" value="<?php 
								if((int) $row['author'] == 0)
									echo $row['author'];
								else
									echo htmlspecialchars($user->get_user_login($row['author'])); 
							?>" id="author" name="author"></td>
						</tr>
						<tr>
							<td>Дата:</td>
							<td>
								<select name="day" style="width:100px;">
									<?php
									$date = explode(" ", $row['date']);
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
					<td></td>
						<td><input type="submit" name="submit" class="ucms-button-submit" value="Изменить" ></td>
					</tr>
				</table>
			</form>
			<?php
			}
		}else{
			header("Location: comments.php");
		}
	}

	function update_comment($p){
		global $udb, $user;
		$id = (int) $p['id'];
		$author = $user->get_user_id($udb->parse_value($p['author']));
		if(!$author) $author = $udb->parse_value($p['author']);
		if(isset($p['day'])){
			$day = (int) $p['day'];
			$month = (int) $p['month'];
			$year = (int) $p['year'];
			$hour = (int) $p['hour'];
			$minute = (int) $p['minute'];
			$second = (int) $p['second'];
			$date = "$year-$month-$day $hour:$minute:$second";
		}else $date = date("Y-m-d H:i:s");
		$referer = $udb->parse_value($p['referer']);
		$comment = parse_comment($p['comment']);
			if(!$comment){
				echo '<p>Нужен текст!</p>';
				echo '<p><a href="/admin/update-comment.php?id='. $id . '">Снова</a></p>';
			}else if($user->has_access(2, 3)){
				$sql = "UPDATE `".UC_PREFIX."comments` SET `comment` = '$comment', `approved` = '1', `author` = '$author', `date` = '$date' WHERE id = '$id'";
				$upd = $udb->query($sql);
				if($upd){
					if(preg_match("#(/admin/)#", $referer)){
					header("Location: ".UCMS_DIR."/admin/comments.php");			
					$_SESSION['success_upd'] = true;
				}else
					header("Location: ".$referer);
				}
				
			}
	}
	
	function delete_comment($id){
		global $ucms, $udb, $user;
		if(!$id){
			return false;
		}else{ 
			$id = (int) $id;
			$comment = $udb->get_row("SELECT `author` FROM `".UC_PREFIX."comments` WHERE `id` = '$id' LIMIT 1");
			$userd = $udb->get_row("SELECT `id`, `group` FROM `".UC_PREFIX."users` WHERE `id` = '$comment[author]' LIMIT 1");
			if($comment){
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
				header("Location: ".UCMS_DIR."/admin/comments.php");
				return false;
			}

			if($user->has_access(2, $accessLVL)){
				$sql = "SELECT * FROM `".UC_PREFIX."comments` WHERE `id` = '$id' LIMIT 1";
				$comment = $udb->get_row($sql);
				$post = $comment['post'];
				$sqc = "DELETE FROM `".UC_PREFIX."comments` WHERE `id` = '$id'";
				$del2 = $udb->query($sqc);
				$comments_nums = $udb->num_rows("SELECT `id` FROM `".UC_PREFIX."comments` WHERE `approved` = 1 and `post` = '$post'");
				$del1 = $udb->query("UPDATE `".UC_PREFIX."posts` SET `comments` = '$comments_nums' WHERE `id` = '$post'");
				if($del1 and $del2){
					if(preg_match("#(/admin/)#", $user->get_back_url())){
						header("Location: ".UCMS_DIR."/admin/comments.php");		
						$_SESSION['success_del'] = true;
					}else
						header("Location: ".$user->get_back_url());
				}
			}else
				header("Location: ".UCMS_DIR."/admin/comments.php");
		}
	}
			
	function approve_comment($id){
		global $udb, $user;
		if(!$id){
			return false;
		}
		else if($user->has_access(2, 4)){
			$id = (int) $id;
			$sql = "SELECT `post` FROM `".UC_PREFIX."comments` WHERE id='$id'";
			$comment = $udb->get_row($sql);
			$post = $comment['post'];
			$sqa = "UPDATE `".UC_PREFIX."comments` SET `approved` = '1' WHERE id = '$id'";
			$upd = $udb->query($sqa);
			$comments_nums = $udb->num_rows("SELECT `id` FROM `".UC_PREFIX."comments` WHERE `approved` = 1 and `post` = '$post'");
			$add = $udb->query("UPDATE `".UC_PREFIX."posts` SET `comments` = '$comments_nums' WHERE `id` = '$post'");
			if($add and $upd){
				if(preg_match("#(/admin/)#", $user->get_back_url())){
					header("Location: ".UCMS_DIR."/admin/comments.php");			
					$_SESSION['success_add'] = true;
				}else
					header("Location: ".$user->get_back_url());	
			}
			
		}
	}

	function manage_comments(){
		global $ucms, $user, $udb;
		if (isset($_POST['item']) and isset($_POST['actions'])){
			$items = array();
			$action = (int) $_POST['actions'];
			foreach ($_POST['item'] as $id) {
				$id = (int) $id;
				$comment = $udb->get_row("SELECT `author` FROM `".UC_PREFIX."comments` WHERE `id` = '$id' LIMIT 1");
				if($comment){
					if(!empty($comment['author'])){
						if($user->get_user_id() == $comment['author']){
							$accessLVL = 2;
						}elseif($user->get_user_group($comment['author']) == 1){
							$accessLVL = 6;
						}else{
							$accessLVL = 4;
						}
					}
				}
				if($action == 3) $accessLVL++;
				if($user->has_access(2, $accessLVL)){
					$items[] = $id;
				}
			}
			$ids = implode(',', $items);
			if (count($items) > 0) {
				switch ($action) {
					case 1:
						$upd = $udb->query("UPDATE `".UC_PREFIX."comments` SET `approved` = '1' WHERE `id` IN ($ids)");
						if (count($items) > 1) {
							$_SESSION['success_addm'] = true;
						}else 
							$_SESSION['success_add'] = true;
 						header("Location: ".UCMS_DIR."/admin/comments.php");
					break;
	
					case 2:
						$upd = $udb->query("UPDATE `".UC_PREFIX."comments` SET `approved` = '0' WHERE `id` IN ($ids)");
						if (count($items) > 1) {
							$_SESSION['success_updm'] = true;
						}else 
							$_SESSION['success_upd'] = true;
 						header("Location: ".UCMS_DIR."/admin/comments.php");
					break;
	
					case 3:
						$del = $udb->query("DELETE FROM `".UC_PREFIX."comments` WHERE `id` IN ($ids)");
						if (count($items) > 1) {
							$_SESSION['success_delm'] = true;
						}else 
							$_SESSION['success_del'] = true;
 						header("Location: ".UCMS_DIR."/admin/comments.php");
					break;
					
				}
			}
		}
		$call = $udb->num_rows("SELECT * FROM `".UC_PREFIX."comments`");
		$capproved = $udb->num_rows("SELECT * FROM `".UC_PREFIX."comments` WHERE `approved` > 0");
		$cunapproved = $udb->num_rows("SELECT * FROM `".UC_PREFIX."comments` WHERE `approved` = 0");
		$user_id = $user->get_user_id();
		$perpage = 25;
		$columns = array('author', 'date');
		$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
		$orderby = isset($_GET['orderby']) ? in_array($_GET['orderby'], $columns) ? $_GET['orderby'] : 'id' : 'id';
		$order = (isset($_GET['order']) and $_GET['order'] == 'DESC') ? 'ASC' : 'DESC';
		$status = isset($_GET['status']) ? $_GET['status'] : "";
		switch ($status) {
			case 'approved':
				$swhere = "WHERE `approved` = 1";
				break;

			case 'unapproved':
				$swhere = "WHERE `approved` = 0";
				break;

			default:
				$swhere = "";
			break;
		}
		if($page <= 0) $page = 1;
		if($user->has_access(2, 4))
			$count = $udb->num_rows("SELECT * FROM `".UC_PREFIX."comments` $swhere ORDER BY `$orderby` $order");
		else $count = $udb->num_rows("SELECT * FROM `".UC_PREFIX."comments` WHERE `author` = '$user_id' ORDER BY `$orderby` $order");
		$pages_count = 0;
		if($count != 0){ 
			$pages_count = ceil($count / $perpage); 
			if ($page > $pages_count):
				$page = $pages_count;
			endif; 
			$start_pos = ($page - 1) * $perpage;
			if($user->has_access(2, 4))
				$sql = "SELECT * FROM `".UC_PREFIX."comments` $swhere ORDER BY `$orderby` $order LIMIT $start_pos, $perpage";
			else $sql = "SELECT * FROM `".UC_PREFIX."comments` WHERE `author` = '$user_id' ORDER BY `$orderby` $order LIMIT $start_pos, $perpage";
		}else $sql  = "SELECT * FROM `".UC_PREFIX."comments` WHERE `id` = '0'";

		$lall = $status != '' ? "<a href=\"".UCMS_DIR."/admin/comments.php".(isset($_GET['orderby']) ? "?orderby=".$_GET['orderby'] : "").(isset($_GET['order']) ? "&amp;order=".$_GET['order'] : "").(isset($_GET['page']) ? ((isset($_GET['orderby']) and isset($_GET['order'])) ? "&amp;" : "?")."page=".$_GET['page'] : "")."\">Все</a>" : "<b>Все</b>"; 
		$lapproved = $status != 'approved' ? "<a href=\"".UCMS_DIR."/admin/comments.php?status=approved".(isset($_GET['orderby']) ? "&amp;orderby=".$_GET['orderby'] : "").(isset($_GET['order']) ? "&amp;order=".$_GET['order'] : "").(isset($_GET['page']) ? "&amp;page=".$_GET['page'] : "")."\">Одобренные</a>" : "<b>Одобренные</b>"; 
		$lunapproved =  $status != 'unapproved' ? "<a href=\"".UCMS_DIR."/admin/comments.php?status=unapproved".(isset($_GET['orderby']) ? "&amp;orderby=".$_GET['orderby'] : "").(isset($_GET['order']) ? "&amp;order=".$_GET['order'] : "").(isset($_GET['page']) ? "&amp;page=".$_GET['page'] : "")."\">Неодобренные</a>" : "<b>Неодобренные</b>"; 
		?>
		<?php if($user->has_access(2, 4)){ ?>
		<br>
		Показывать: <?php echo $lall." ($call)"; ?> | <?php echo $lapproved." ($capproved)"; ?> | <?php echo $lunapproved." ($cunapproved)"; ?>
		<br><br>
		<form action="comments.php" method="post">
		<select name="actions" style="width: 250px;">
			<option>Отмеченные</option>
			<option value="1">Одобрить</option>
			<option value="2">Скрыть</option>
			<option value="3">Удалить</option>
		</select>
		<input type="submit" value="Применить" class="ucms-button-submit">
		<br>
		<?php } ?>
		<?php
		if($pages_count > 1){
			echo "<br>";
			pages($page, $count, $pages_count, 15, false);
			echo '<br>';
		}?><br>
		<table class="manage">
		<?php
		$comments = $udb->get_rows($sql);
		$link1 = UCMS_DIR."/admin/comments.php?orderby=author&amp;order=".$order.(isset($_GET['page']) ? "&amp;page=".$_GET['page'] : "");
		$link2 = UCMS_DIR."/admin/comments.php?orderby=date&amp;order=".$order.(isset($_GET['page']) ? "&amp;page=".$_GET['page'] : "");
		$mark = $order == "ASC" ? '↑' : '↓';
		echo '<table class="manage">';
		echo '<tr>
				<th style="width: 15px;"><input type="checkbox" name="select-all" value="1"></th>
				<th><a href="'.$link1.'">Автор '.$mark.'</a></th>
				<th>Текст комментария</th>
				<th>Пост</th>
				<th><a href="'.$link2.'">Дата '.$mark.'</a></th>
				<th>Статус</th>
				<th style="width: 180px;">Управление</th>
			</tr>';
		if($comments){ 
			for ($i = 0; $i < count($comments); $i++) { 
				$authors[] = $udb->parse_value($comments[$i]['author']);
				$posts[] = $udb->parse_value($comments[$i]['post']);
			}
			$posts = implode("','", $posts);
			$posts = "'".$posts."'";

			$posts_meta = $udb->get_rows("SELECT * FROM `".UC_PREFIX."posts` WHERE `id` in ($posts) ");

			$authors = implode("','", $authors);
			$authors = "'".$authors."'";

			$authors_meta = $udb->get_rows("SELECT `id`, `login`, `group` FROM `".UC_PREFIX."users` WHERE `id` in ($authors)");
	
			for($i = 0; $i < count($comments); $i++) {
				for($j = 0; $j < count($authors_meta); $j++){
					if(isset($comment_author_id) and ($comments[$i]['author'] === $comment_author_id)) break;
					if($comments[$i]['author'] === $authors_meta[$j]['id']){
						$comment_author_id = $authors_meta[$j]['id'];
						$comment_author_login = $authors_meta[$j]['login'];
						$comment_author_group = $authors_meta[$j]['group'];
						break;
					}else{
						unset($comment_author_id);
						$comment_author_login = $comments[$i]['author'];
						$comment_author_group = 6;
					}
				}

				for($j = 0; $j < count($posts_meta); $j++){
					if(isset($post_id) and ($comments[$i]['post'] === $post_id)) break;
					if($comments[$i]['post'] === $posts_meta[$j]['id']){
						$post_id = $posts_meta[$j]['id'];
						$post = $j;
						break;
					}else $post = 0;
				}
				$link = NICE_LINKS ? "<a href=\"".post_sef_links($posts_meta[$post])."#comment-".$comments[$i]['id']."\">".$posts_meta[$post]['title']."</a>" : "<a href=\"".UCMS_DIR."/?id=".$comments[$i]['post']."#comment-".$comments[$i]['id']."\">".$posts_meta[$post]['title']."</a>";
				?>
				<tr>
				<td><input type="checkbox" name="item[]" value="<?php echo $comments[$i]['id']; ?>"></td>
				<td><b><?php if((int) ($comments[$i]['author']) > 0) echo $comment_author_login; else echo $comments[$i]['author'];  ?></b></td>
				<td>
				<?php
				$limit = 100;
				$comment = $comments[$i]['comment'];
				if(mb_strlen($comment, 'UTF-8') > $limit){
					echo htmlspecialchars(mb_substr($comment, 0, $limit, 'UTF-8')).'...';
				}else{
					echo htmlspecialchars($comment);
				}
				?>
				</td>
				<td><?php echo $link; ?></td>
				<td><?php echo $ucms->format_date($comments[$i]['date'])?></td>
				<td><?php echo ($comments[$i]['approved'] == 1 ? "Одобрен" : "Неодобрен"); ?></td>
				<td><span class="actions">
				<?php
				if ($comments[$i]['approved'] == 0 and $user->has_access(2, 4)):
					echo '<a href="'.UCMS_DIR.'/admin/comments.php?action=approve&amp;id='.$comments[$i]['id'].'">Одобрить</a> | '; 
				endif;
				if($user_id == $comments[$i]['author'])
					$accessLVL = 3;
				elseif($comment_author_group == 1){
					$accessLVL = 6;
				}else $accessLVL = 4;
				if($user->has_access(2, $accessLVL)) echo '<a href="'.UCMS_DIR.'/admin/comments.php?action=update&amp;id='.$comments[$i]['id'].'" >Изменить</a> | <a href="comments.php?action=delete&amp;id='.$comments[$i]['id'].'" >Удалить</a>';
				echo '</span></td>';
				echo '</tr>';

			}
			echo '</table></form>';
		}else{
			?>
			<tr>
				<td colspan="7" style="text-align:center;">Комментариев пока нет.</td>
			</tr>
			</table>
			<?php
		}
	}


	function parse_comment($text){
		global $udb, $user;
		$text = $udb->parse_value($text);
		if($user->has_access(2, 3)){
			$replacement = NICE_LINKS ?  UCMS_URL."/redirect/http://" : UCMS_URL."/?action=redirect&amp;url=http://";
			if(!preg_match("#($replacement)#", $text))
				$text = preg_replace("#(http://)#", $replacement, $text);
			$text = strip_tags($text, '<p><a><pre><img><br><b><em><i><strike>');	
		}else{
			$text = strip_tags($text);
		}
		return $text;	
		
	}
?>
