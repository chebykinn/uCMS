<?php 
	include 'head.php';
	include 'sidebar.php';
	$posts_count = $udb->num_rows("SELECT * FROM `".UC_PREFIX."posts`");
	$pages_count = $udb->num_rows("SELECT * FROM `".UC_PREFIX."pages`");
	$comments_count = $udb->num_rows("SELECT * FROM `".UC_PREFIX."comments`");
	$users_count = $udb->num_rows("SELECT * FROM `".UC_PREFIX."users`");
	$categories_count = $udb->num_rows("SELECT * FROM `".UC_PREFIX."categories`");
	$groups_count = $udb->num_rows("SELECT * FROM `".UC_PREFIX."groups`");
	$messages_count = $udb->num_rows("SELECT * FROM `".UC_PREFIX."messages`");
	$links_count = $udb->num_rows("SELECT * FROM `".UC_PREFIX."links`");
	$themes_count = $udb->num_rows("SELECT * FROM `".UC_PREFIX."themes`");
	$widgets_count = $udb->num_rows("SELECT * FROM `".UC_PREFIX."widgets`");
	?> 
	<div id="content">
		<?php if(UCMS_MAINTENANCE){
			echo "<div class=\"warning\">Внимание, на сайте включён режим техобслуживания.</div><br>";
		}?>
			<h2>uCMS <?php echo UCMS_VERSION; ?></h2><br>
		<table style="width: 100%;">
			<tr>
				<td style="vertical-align: top; width: 350px;">
				<table class="info">
			<tr>
				<td><b>Название сайта:</b></td>
				<td><?php site_info("name"); ?></td>
			</tr>
			<tr>
				<td><b>Описание сайта:</b></td>
				<td><?php site_info("description"); ?></td>
			</tr>
			<tr>
				<td><b>Заголовок сайта:</b></td>
				<td><?php site_info("title"); ?></td>
			</tr>
			<tr>
				<td><b>Домен сайта:</b></td>
				<td><?php site_info("domain"); ?></td>
			</tr>
			<tr>
				<td><b>Автор сайта:</b></td>
				<td><?php site_info("author"); ?></td>
			</tr>
			<tr>
				<td><b>Ваш логин:</b></td>
				<td><?php echo $user->get_user_login(); ?></td>
			</tr>
			<tr>
				<td><b>Тема сайта:</b></td>
				<td><?php echo THEMENAME; ?></td>
			</tr>
			<tr>
				<td><b>Время:</b></td>
				<td><?php echo $ucms->get_date(true, true, true, false, '-', ", "); ?></td>
			</tr>
			<?php if($user->has_access(0, 4)){ ?><tr>
				<td><b>Каталог uCMS:</b></td>
				<td><?php if(!UCMS_DIR) echo '/'; else echo UCMS_DIR; ?></td>
			</tr>
			<tr>
				<td><b>Префикс таблиц:</b></td>
				<td><?php echo UC_PREFIX; ?></td>
			</tr>
			<tr>
				<td><b>Размер аватаров:</b></td>
				<td><?php echo AVATAR_WIDTH."x".AVATAR_HEIGHT; ?></td>
			</tr>
			<tr>
				<td><b>Всего страниц:</b></td>
				<td><?php echo $pages_count; ?></td>
			</tr>
			<tr>
				<td><b>Всего постов:</b></td>
				<td><?php echo $posts_count; ?></td>
			</tr>
			<tr>
				<td><b>Всего категорий:</b></td>
				<td><?php echo $categories_count; ?></td>
			</tr>
			<tr>
				<td><b>Всего комментариев:</b></td>
				<td><?php echo $comments_count; ?></td>
			</tr>
			<tr>
				<td><b>Всего пользователей:</b></td>
				<td><?php echo $users_count; ?></td>
			</tr>
			<tr>
				<td><b>Всего групп:</b></td>
				<td><?php echo $groups_count; ?></td>
			</tr>
			<tr>
				<td><b>Всего сообщений:</b></td>
				<td><?php echo $messages_count; ?></td>
			</tr>
			<tr>
				<td><b>Всего ссылок:</b></td>
				<td><?php echo $links_count; ?></td>
			</tr>
			<tr>
				<td><b>Всего тем:</b></td>
				<td><?php echo $themes_count; ?></td>
			</tr>
			<tr>
				<td><b>Всего виджетов:</b></td>
				<td><?php echo $widgets_count; ?></td>
			</tr>
			<?php } ?>
			</table>
		</td>
	<td style="vertical-align: top;">
		<table class="info" style="width:100%">
			<tr>
				<th>Новые посты</th>
				<th>Новые комментарии</th>
				<th>Новые страницы</th>
				<th>Новые пользователи</th>
			</tr>
			<?php 
			$lim = 10;
			if(!$user->has_access(0, 4)){ 
				$publish = "WHERE `publish` > 0";
				$approved = "WHERE `approved` > 0";
				$activation = "WHERE `activation` > 0";
			}else{
				$publish = "";
				$approved = "";
				$activation = "";
			}
			$posts = $udb->get_rows("SELECT * FROM `".UC_PREFIX."posts` $publish ORDER BY `id` DESC LIMIT $lim");
			$comments = $udb->get_rows("SELECT * FROM `".UC_PREFIX."comments` $approved ORDER BY `id` DESC LIMIT $lim");
			$pages = $udb->get_rows("SELECT * FROM `".UC_PREFIX."pages` $publish ORDER BY `id` DESC LIMIT $lim");
			$users = $udb->get_rows("SELECT * FROM `".UC_PREFIX."users` $activation ORDER BY `id` DESC LIMIT $lim");
			//prepare
			if($posts and count($posts) > 0){
				for($i = 0; $i < count($posts); $i++){
					$p_authors[] = $udb->parse_value($posts[$i]['author']);
					$p_categories[] = $udb->parse_value($posts[$i]['category']);
				}
	
				$p_authors2 = implode("','", $p_authors);
				$p_authors2 = "'".$p_authors2."'";
	
				$p_categories2 = implode("','", $p_categories);
				$p_categories2 = "'".$p_categories2."'";
	
				$p_logins = $udb->get_rows("SELECT `id`, `login`, `group` FROM `".UC_PREFIX."users` WHERE `id` in ($p_authors2) ");
				$p_cats_meta = $udb->get_rows("SELECT `id`, `name` FROM `".UC_PREFIX."categories` WHERE `id` in ($p_categories2) ");
			}
			if($comments and count($comments) > 0){
				for($i = 0; $i < count($comments); $i++){
					$c_authors[] = $udb->parse_value($comments[$i]['author']);
					$c_posts[] = $udb->parse_value($comments[$i]['post']);
				}
	
				$c_authors2 = implode("','", $c_authors);
				$c_authors2 = "'".$c_authors2."'";
	
				$c_posts2 = implode("','", $c_posts);
				$c_posts2 = "'".$c_posts2."'";
	
				$c_posts_meta = $udb->get_rows("SELECT * FROM `".UC_PREFIX."posts` WHERE `id` in ($c_posts2) ");
				$c_logins = $udb->get_rows("SELECT `id`, `login`, `group` FROM `".UC_PREFIX."users` WHERE `id` in ($c_authors2) ");
			}
			if($pages and count($pages) > 0){
				for($i = 0; $i < count($pages); $i++){
					$pa_authors[] = $udb->parse_value($pages[$i]['author']);
				}
	
				$pa_authors2 = implode("','", $pa_authors);
				$pa_authors2 = "'".$pa_authors2."'";
	
				$pa_logins = $udb->get_rows("SELECT `id`, `login`, `group` FROM `".UC_PREFIX."users` WHERE `id` in ($pa_authors2) ");
			}
			//end-prepare

			if($posts or $comments or $pages or $users){
				$arr = array(count($posts), count($comments), count($pages), count($users));
				sort($arr);
				//***
				for ($i = 0; $i < $arr[3]; $i++) { 
					echo "<tr>";
					if(isset($posts[$i]['id'])){ //posts

						for($j = 0; $j < count($p_logins); $j++){
							if(isset($p_author_id) and ($posts[$i]['author'] === $p_author_id)) break;
							if($posts[$i]['author'] === $p_logins[$j]['id']){
								$p_author_id = $p_logins[$j]['id'];
								$p_author_login = $p_logins[$j]['login'];
								$p_author_group = $p_logins[$j]['group'];
								break;
							}
						}
	
						for($j = 0; $j < count($p_cats_meta); $j++){
							if(isset($p_category_id) and ($posts[$i]['category'] === $p_category_id)) break;
							if($posts[$i]['category'] === $p_cats_meta[$j]['id']){
								$p_category_id = $p_cats_meta[$j]['id'];
								$p_category_name = $p_cats_meta[$j]['name'];
								break;
							}
						}
						
						if(!isset($p_author_group)) $p_author_group = $user->get_user_group($posts[$i]['author']);
						if($user->get_user_id() == $posts[$i]['author']){
							$accessLVL = 2;
						}else if($p_author_group == 1){
							$accessLVL = 6;
						}else 
							$accessLVL = 4;
					
						if(NICE_LINKS){
							$slink = post_sef_links($posts[$i], $p_category_name);
						}else $slink = UCMS_DIR.'/?id='.$posts[$i]['id'];

						$link = "<a href=\"$slink\" target=\"_blank\">".$posts[$i]['title']."</a>";

						echo "<td><b>".$p_category_name.":</b> <div class=\"title\">$link</div> (";
						if ((int) $posts[$i]['author'] == 0)
							echo $posts[$i]['author'].")";
						else 
							echo $p_author_login.")";
						echo "<span class=\"actions\"> ";
						if($user->has_access(1, $accessLVL)){
							echo "<a href=\"posts.php?action=update&amp;id=".$posts[$i]['id']."\">Изменить</a>";
						}
						if($user->has_access(1, $accessLVL+1)){
							echo " | <a href=\"posts.php?action=delete&amp;id=".$posts[$i]['id']."\">Удалить</a>";
						}
						echo "</span></td>";
					}else{
						echo "<td></td>";
					} //end-posts
					if(isset($comments[$i]['id'])){ //comments

						for($j = 0; $j < count($c_logins); $j++){
							if(isset($c_author_id) and ($comments[$i]['author'] === $c_author_id)) break;
							if($comments[$i]['author'] === $c_logins[$j]['id']){
								$c_author_id = $c_logins[$j]['id'];
								$c_author_login = $c_logins[$j]['login'];
								$c_author_group = $c_logins[$j]['group'];
								break;
							}
						}

						for($j = 0; $j < count($c_posts_meta); $j++){
							if(isset($post_id) and ($comments[$i]['post'] === $post_id)) break;
							if($comments[$i]['post'] === $c_posts_meta[$j]['id']){
								$post_id = $c_posts_meta[$j]['id'];
								$post = $j;
								break;
							}else $post = 0;
						}
						if(!isset($c_author_group)) $c_author_group = $user->get_user_group($comments[$i]['author']);
						if($user->get_user_id() == $comments[$i]['author']){
							$accessLVL = 3;
						}else if($c_author_group == 1){
							$accessLVL = 6;
						}else 
							$accessLVL = 4;
						if(NICE_LINKS){
							$slink = post_sef_links($c_posts_meta[$post]);
						}else 
							$slink = UCMS_DIR.'/?id='.$comments[$i]['post'];
						$link = "<a href=\"$slink#comment-".$comments[$i]['id']."\" target=\"_blank\">".htmlspecialchars($comments[$i]['comment'])."</a>";
						echo "<td><div class=\"title\">".$link."</div> (";
						if ((int) $comments[$i]['author'] == 0)
							echo $comments[$i]['author'].")";
						else 
							echo $c_author_login.")";
						echo "<span class=\"actions\"> ";
						if($user->has_access(2, $accessLVL)){
							echo "<a href=\"comments.php?action=update&amp;id=".$comments[$i]['id']."\">Изменить</a>";
						}
						if($user->has_access(2, $accessLVL+1)){
							echo " | <a href=\"comments.php?action=delete&amp;id=".$comments[$i]['id']."\">Удалить</a>";
						}
						echo "</span></td>";
					}else{
						echo "<td></td>";
					} //end-comments
					if(isset($pages[$i]['id'])){ //pages

						for($j = 0; $j < count($pa_logins); $j++){
							if(isset($pa_author_id) and ($pages[$i]['author'] === $pa_author_id)) break;
							if($pages[$i]['author'] === $pa_logins[$j]['id']){
								$pa_author_id = $pa_logins[$j]['id'];
								$pa_author_login = $pa_logins[$j]['login'];
								$pa_author_group = $pa_logins[$j]['group'];
								break;
							}
						}
						if(!isset($pa_author_group)) $pa_author_group = $user->get_user_group($pages[$i]['author']);
						if($user->get_user_id() == $pages[$i]['author']){
							$accessLVL = 2;
						}else if($pa_author_group == 1){
							$accessLVL = 6;
						}else 
							$accessLVL = 4;
						
						$link = NICE_LINKS ? page_sef_links($pages[$i]) : UCMS_DIR.'/?p='.$pages[$i]['id'];
						$link = "<a href=\"$link\" target=\"_blank\">".$pages[$i]['title']."</a>";		
						echo "<td><div class=\"title\">".$link."</div> (";

						if ((int) $pages[$i]['author'] == 0)
							echo $pages[$i]['author'].")";
						else 
							echo $pa_author_login.")";
						echo "<span class=\"actions\"> ";
						if($user->has_access(3, $accessLVL)){
							echo "<a href=\"pages.php?action=update&amp;id=".$pages[$i]['id']."\">Изменить</a>";
						}
						if($user->has_access(3, $accessLVL+1)){
							echo " | <a href=\"pages.php?action=delete&amp;id=".$pages[$i]['id']."\">Удалить</a>";
						}
						echo "</span></td>";
					}else{
						echo "<td></td>";
					} //end-pages
					if(isset($users[$i]['id'])){ //users
						if($user->get_user_id() == $users[$i]['id']){
							$accessLVL = 2;
						}else if($users[$i]['group'] == 1){
							$accessLVL = 6;
						}else 
							$accessLVL = 4;

						$link = NICE_LINKS ? UCMS_DIR.'/user/'.$users[$i]['login'] : UCMS_DIR.'/?action=profile&amp;'.$users[$i]['id'];
						$link = "<a href=\"$link\" target=\"_blank\">".$users[$i]['login']."</a>";
						echo "<td><div class=\"title\">".$link."</div>";
						echo "<span class=\"actions\"> ";
						if($user->has_access(4, $accessLVL)){
							echo "<a href=\"users.php?action=update&amp;id=".$users[$i]['id']."\">Изменить</a>";
						}
						if($user->has_access(4, $accessLVL+1) and $users[$i]['id'] > 1){
							echo " | <a href=\"users.php?action=delete&amp;id=".$users[$i]['id']."\">Удалить</a>";
						}
						echo "</span></td>";
					}else{
						echo "<td></td>";
					} //end-users
					echo "</tr>";
				}
			}
?>
		</table>
	</td>
		</tr>
	</table>
<?php include "footer.php"; ?>