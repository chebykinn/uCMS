<?php
	
	function add_categories_tree($categories, $root, $tree_level = 0, $selected = -1){
		global $parent;
		$tree_level++;
		if($root == 0){
			$tree_level = 0;
		} 
		if(is_array($categories)){
			foreach ($categories as $category){
				if($category['parent'] == $root){
					$children_menu = add_categories_tree($categories, $category['id'], $tree_level, $selected);
					if($root == 0){
						$parent = '';
					}
					else{
						$parent = '';
						for ($i = 0; $i < $tree_level; $i++) { 
							$parent .= '—';
						}
					}

					$tree[] = '<option value="'.$category['id'].'" '.($category['id'] == $selected ? 'selected' : '').'>'.$parent.$category['name'].'</option>'.$children_menu;
				}
			}
			if(isset($tree)){
				return implode('', $tree);
			}else{
				return '';
			}
		}else{
			return false;
		}
	}

	function category_posts_number($type, $id){
		global $udb;
		if($type == "add"){
			$udb->query("UPDATE `".UC_PREFIX."categories` SET `posts` = `posts` + 1 WHERE `id` = '$id'");
		}elseif($type == "delete"){
			$udb->query("UPDATE `".UC_PREFIX."categories` SET `posts` = `posts` - 1 WHERE `id` = '$id'");
		}
		$parent_id = $udb->get_val("SELECT `parent` FROM `".UC_PREFIX."categories` WHERE `id` = '$id'");
		if( (int) $parent_id != 0 ){
			category_posts_number($type, $parent_id);
		}
	}

	function add_post_form(){
		global $user, $udb, $uc_months, $ucms;
		$ucms->template(get_module("path", "posts").'forms/add-post-form.php', false);
	}

	function add_post($p){
		global $udb, $ucms, $user, $event;
		$title = $udb->parse_value($p['title']);
		$body = $udb->parse_value($p['body']);
		$keywords = $udb->parse_value($p['keywords']);
		$publish = (int) $p['publish'];
		if(isset($p['pin'])) $publish = 2;
		$comment = isset($p['comment']) ? (int) $p['comment'] : 0;
		$alias = $udb->parse_value($p['alias']);
		$author = isset($p['author']) ? $user->get_user_id($udb->parse_value($p['author'])) : $user->get_user_id();
		$day    = (!empty($p['day'])    and (int) $p['day']    > 0) ? (int) $p['day']    : date("d");
		$month  = (!empty($p['month'])  and (int) $p['month']  > 0) ? (int) $p['month']  : date("m");
		$year   = (!empty($p['year'])   and (int) $p['year']   > 0) ? (int) $p['year']   : date("Y");
		$hour   = (!empty($p['hour'])   and (int) $p['hour']   > 0) ? (int) $p['hour']   : date("H");
		$minute = (!empty($p['minute']) and (int) $p['minute'] > 0) ? (int) $p['minute'] : date("i");
		$second = (!empty($p['second']) and (int) $p['second'] > 0) ? (int) $p['second'] : date("s");
		$date = "$year-$month-$day $hour:$minute:$second";
		if(!$author)
			$author = $udb->parse_value($p['author']);
		if((int) $author == 0 and !$user->logged()){
			$author = isset($_SESSION['guest_login']) ? $_SESSION['guest_login'] : "Guest";
		}
		$category = (int) $p['category'];
		if(empty($title) or empty($body)){
			echo '<br><div class="error">'.$ucms->cout("module.posts.empty_fields.error", true).'</div>';
		}	
		else if($user->has_access("posts", 2)){
			if(empty($alias)){
				$alias = $ucms->transliterate($title);
			}
			$alias = strtolower(preg_replace('/\s/', "_", $alias));
			$alias = strtolower(preg_replace(URL_REGEXP, "", $alias));
			$test = $udb->get_row("SELECT `id` FROM `".UC_PREFIX."posts` WHERE `alias` = '$alias'");
			if($test){
				$i = 0;
				$testalias = "";
				while($test){
					$i++;
					$testalias = $alias.'-'.$i;
					$test = $udb->get_row("SELECT `id` FROM `".UC_PREFIX."posts` WHERE `alias` = '$testalias'");
					$testalias = "";
				}
				$alias .= "-$i";
			}
			if($publish > 0){
				category_posts_number("add", $category);
			}
			$sql = "INSERT INTO `".UC_PREFIX."posts` (`id`, `title`, `body`, `keywords`, `publish`, `alias`, `author`, `category`, `comments`, `date`) 
			VALUES (null, '$title','$body', '$keywords', '$publish', '$alias', '$author', '$category', '$comment', '$date')";
			$add = $udb->query($sql);
			if($add){
				$event->do_actions("post.added", array($author, $title));
				header("Location: ".get_current_url('action', 'alert', 'id')."&alert=added");
			}else echo '<div class="error">'.$ucms->cout("module.posts.adding_post.error", true).'</div>';
			
		}
	}
		
		function manage_posts(){
			global $user, $udb, $ucms;

			$status = isset($_GET['status']) ? htmlspecialchars($_GET['status']) : "";
			$swhere = '';
			$safe_query = '';
			$overwrite_where = '1';
			$no_query = true;

			switch ($status) {
				case 'published':
					$swhere = "WHERE `p`.`publish` = 1";
					$overwrite_where = "`p`.`publish` > 0";
				break;
	
				case 'draft':
					$swhere = "WHERE `p`.`publish` = 0";
					$overwrite_where = "`p`.`publish` = 0";
				break;
	
				case 'pinned':
					$swhere = "WHERE `p`.`publish` = 2";
					$overwrite_where = "`p`.`publish` = 2";
				break;
	
				default:
					$swhere = "";
					$overwrite_where = "1";
				break;
			}
			$overwrite_perpage = $perpage = 25;

			include get_module("path", "search").'search.php';

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
					if($user->has_access("posts", $accessLVL)){
						$items[] = $id;
					}
				}
				$ids = implode(',', $items);
				if (count($items) > 0) {
					switch ($action) {
						case 1:
							$upd = $udb->query("UPDATE `".UC_PREFIX."posts` SET `publish` = '1' WHERE `id` IN ($ids)");
							if (count($items) > 1) {
								header("Location: ".get_current_url('action', 'alert', 'id')."&alert=published_multiple");
							}else 
								header("Location: ".get_current_url('action', 'alert', 'id')."&alert=published");
						break;
						
						case 2:
							$upd = $udb->query("UPDATE `".UC_PREFIX."posts` SET `publish` = '2' WHERE `id` IN ($ids)");
							if (count($items) > 1) {
								header("Location: ".get_current_url('action', 'alert', 'id')."&alert=pinned_multiple");
							}else 
								header("Location: ".get_current_url('action', 'alert', 'id')."&alert=pinned");
						break;

						case 3:
							$upd = $udb->query("UPDATE `".UC_PREFIX."posts` SET `publish` = '0' WHERE `id` IN ($ids)");
							if (count($items) > 1) {
								header("Location: ".get_current_url('action', 'alert', 'id')."&alert=drafted_multiple");
							}else 
								header("Location: ".get_current_url('action', 'alert', 'id')."&alert=drafted");
						break;
		
						case 4:
							$categories = $udb->get_rows("SELECT `category` FROM `".UC_PREFIX."posts` WHERE `id` IN ($ids)");
							foreach ($categories as $category) {
								$count = $udb->query("UPDATE `".UC_PREFIX."categories` SET `posts` = `posts` - 1 WHERE `id` = '$category[category]'");
							}
							$udb->query("DELETE FROM `".UC_PREFIX."comments` WHERE `post` IN ($ids)");
							$del = $udb->query("DELETE FROM `".UC_PREFIX."posts` WHERE `id` IN ($ids)");
							if (count($items) > 1) {
								header("Location: ".get_current_url('action', 'alert', 'id')."&alert=deleted_multiple");
							}else 
								header("Location: ".get_current_url('action', 'alert', 'id')."&alert=deleted");
						break;
						
					}
				}
			}
			$user_id = $user->get_user_id();
			$columns = array('title','author', 'comments', 'date');
			$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
			$orderby = isset($_GET['orderby']) ? in_array($_GET['orderby'], $columns) ? '`p`.`'.htmlspecialchars($_GET['orderby']).'`' : '`p`.`id`' : '`p`.`id`';
			$order = (isset($_GET['order']) and $_GET['order'] == 'DESC') ? 'ASC' : 'DESC';
			if(!isset($results)){
				$call = $udb->num_rows("SELECT * FROM `".UC_PREFIX."posts`");
				$cpublished = $udb->num_rows("SELECT * FROM `".UC_PREFIX."posts` WHERE `publish` = 1");
				$cdraft = $udb->num_rows("SELECT * FROM `".UC_PREFIX."posts` WHERE `publish` = 0");
				$cpinned = $udb->num_rows("SELECT * FROM `".UC_PREFIX."posts` WHERE `publish` = 2");
				
				if($page <= 0) $page = 1;
				if($user->has_access("posts", 4))
					$count = $udb->num_rows("SELECT * FROM `".UC_PREFIX."posts` AS `p` $swhere ORDER BY $orderby $order");
				else
					$count = $udb->num_rows("SELECT * FROM `".UC_PREFIX."posts` AS `p` WHERE `p`.`author` = '$user_id' ORDER BY $orderby $order");
				$pages_count = 0;
				if($count != 0){ 
					$pages_count = ceil($count / $perpage); 
					if ($page > $pages_count):
						$page = $pages_count;
					endif; 
					$start_pos = ($page - 1) * $perpage;
					if(!$user->has_access("posts", 4)){
						$swhere = "WHERE `p`.`author` = '$user_id'";
					}
					$sql = "SELECT`p`.*, `u`.`login` AS `author_login`, `u`.`group` AS `author_group`, `uf`.`value` AS `author_nickname`,
					`c`.`name` AS `category_name`, `c`.`alias` AS `category_alias` FROM `".UC_PREFIX."posts` AS `p` FORCE INDEX (PRIMARY)
					LEFT JOIN `".UC_PREFIX."users` AS `u` ON `u`.`id` = `p`.`author`
					LEFT JOIN `".UC_PREFIX."usersinfo` AS `uf` ON `uf`.`user_id` = `u`.`id` AND `uf`.`name` = 'nickname'
					LEFT JOIN `".UC_PREFIX."categories` AS `c` ON `c`.`id` = `p`.`category`$swhere ORDER BY $orderby $order LIMIT $start_pos, $perpage";
				}else{
					$sql = "SELECT * FROM `".UC_PREFIX."posts` WHERE `id` = '0'";
				}
			}else{
				$call = $status == '' ? $count : $ucms->cout("module.posts.search.label", true);
				$cpublished = $status == 'published' ? $count : $ucms->cout("module.posts.search.label", true);
				$cdraft = $status == 'draft' ? $count : $ucms->cout("module.posts.search.label", true);
				$cpinned = $status == 'pinned' ? $count : $ucms->cout("module.posts.search.label", true);
			}
			$s_link = "<a href=\"".UCMS_DIR."/admin/manage.php?module=posts";
			$m_link = (isset($_GET['query']) ? "&amp;query=".$safe_query : "")
				  .(isset($_GET['orderby']) ? "&amp;orderby=".$orderby : "")
				  .(isset($_GET['order']) ? "&amp;order=".$order : "")
				  .(isset($_GET['page']) ? "&amp;page=".$page : "");

			$lall = $status != '' ? $s_link.$m_link."\">".$ucms->cout("module.posts.lall.label", true)."</a>"
			 : "<b>".$ucms->cout("module.posts.lall.label", true)."</b>"; 

			$lpublished = $status != 'published' ? $s_link."&amp;status=published".$m_link."\">".$ucms->cout("module.posts.lpublished.label", true)."</a>" 
			 : "<b>".$ucms->cout("module.posts.lpublished.label", true)."</b>"; 

			$ldraft =  $status != 'draft' ? $s_link."&amp;status=draft".$m_link."\">".$ucms->cout("module.posts.ldraft.label", true)."</a>"
			 : "<b>".$ucms->cout("module.posts.ldraft.label", true)."</b>"; 

			$lpinned =  $status != 'pinned' ? $s_link."&amp;status=pinned".$m_link."\">".$ucms->cout("module.posts.lpinned.label", true)."</a>"
			 : "<b>".$ucms->cout("module.posts.lpinned.label", true)."</b>"; 
			
			?>
			<br>
			<?php echo $ucms->cout("module.posts.show.label", true).' '.$lall." ($call)"; ?> | <?php echo $lpinned." ($cpinned)"; ?> | <?php echo $lpublished." ($cpublished)"; ?> | <?php echo $ldraft." ($cdraft)"; ?>
			<br><br>
			<form action="<?php get_current_url('action', 'alert', 'id'); ?>" method="post">
			<?php if($user->has_access("posts", 2)){ ?>
			<select name="actions" style="width: 250px;">
				<option><?php $ucms->cout("module.posts.selected.label"); ?></option>
				<option value="1"><?php $ucms->cout("module.posts.selected.publish.label"); ?></option>
				<option value="2"><?php $ucms->cout("module.posts.selected.pin.label"); ?></option>
				<option value="3"><?php $ucms->cout("module.posts.selected.draft.label"); ?></option>
				<option value="4"><?php $ucms->cout("module.posts.selected.delete.label"); ?></option>
			</select>
			<?php } ?>
			<input type="submit" value="<?php $ucms->cout("module.posts.apply.button"); ?>" class="ucms-button-submit">
			<br>
			<?php
			if($pages_count > 1){
				echo "<br>";
				pages($page, $count, $pages_count, 15, false);
				echo '<br>';
			}?><br>
			<table class="manage">
			<?php
			$s_link = UCMS_DIR."/admin/manage.php?module=posts";
			$m_link = (isset($_GET['query']) ? "&amp;status=".$safe_query : "")
			.(isset($_GET['status']) ? "&amp;status=".$status : "");
			$p_link = (isset($_GET['page']) ? "&amp;page=".$page : "");

			$link1 = $s_link.$m_link."&amp;orderby=title&amp;order=".$order.$p_link;

			$link2 = $s_link.$m_link."&amp;orderby=author&amp;order=".$order.$p_link;

			$link3 = $s_link.$m_link."&amp;orderby=comments&amp;order=".$order.$p_link;

			$link4 = $s_link.$m_link."&amp;orderby=date&amp;order=".$order.$p_link;

			$mark = $order == "ASC" ? '↑' : '↓';
			?>
			<tr>
				<th style="width: 15px;"><input type="checkbox" name="select-all" value="1"></th>
				<th style="width: 30%;"><a href="<?php echo $link1; ?>"><?php $ucms->cout("module.posts.table.header.title"); ?> <?php echo $mark; ?></a></th>
				<th><?php $ucms->cout("module.posts.table.header.category"); ?></th>
				<th style="min-width: 50px;"><a href="<?php echo $link2; ?>"><?php $ucms->cout("module.posts.table.header.author"); ?> <?php echo $mark; ?></a></th>
				<th><a href="<?php echo $link3; ?>"><?php $ucms->cout("module.posts.table.header.comments"); ?> <?php echo $mark; ?></a></th>
				<th><?php $ucms->cout("module.posts.table.header.tags"); ?></th>
				<th><?php $ucms->cout("module.posts.table.header.status"); ?></th>
				<th><a href="<?php echo $link4; ?>"><?php $ucms->cout("module.posts.table.header.date"); ?> <?php echo $mark; ?></a></th>
				<th style="width: 115px;"><?php $ucms->cout("module.posts.table.header.manage"); ?></th>
			</tr>
			<?php
			if(!isset($results)){
				$m_posts = $udb->get_rows($sql);
			}else{
				$m_posts = $results;
			}

			if($m_posts){
				$p_count = count($m_posts);
			}else $p_count = 0;
			if($p_count != 0){

				for($i = 0; $i < $p_count; $i++){

					switch ($m_posts[$i]['publish']) {
						case 0:
							$status = 'module.posts.status.draft';
						break;

						case 1:
							$status = 'module.posts.status.published';
						break;

						case 2:
							$status = 'module.posts.status.pinned';
						break;

						default:
							$status = 'module.posts.status.published';
						break;
					}
					$link = NICE_LINKS ? post_sef_links($m_posts[$i]) : UCMS_DIR.'/?id='.$m_posts[$i]['id'];
					if($user_id == $m_posts[$i]['author'])
						$accessLVL = 2;
					elseif($m_posts[$i]['author_group'] == 1){
						$accessLVL = 6;
					}else
						$accessLVL = 4;
					?>
					<tr>
						<td><input type="checkbox" name="item[]" value="<?php echo $m_posts[$i]['id']; ?>"></td>
						<td style="width: 25%"><a target="_blank" href="<?php echo $link; ?>"><?php 
						$tags = '<p><a><pre><img><br><b><em><i><strike><span>';
						echo !isset($results) ? htmlspecialchars($m_posts[$i]['title']) : strip_tags($m_posts[$i]['title'], $tags); ?></a></td>
						<td><?php echo $m_posts[$i]['category_name']; ?></td>
						<td><b><?php
							if((int) $m_posts[$i]['author'] == 0){
								echo $m_posts[$i]['author'];
							}else{
						 		echo '<a href="'.UCMS_DIR.'/admin/manage.php?module=users&action=update&id='.$m_posts[$i]['author'].'">'
						 		.(!empty($m_posts[$i]['author_nickname']) ? $m_posts[$i]['author_nickname'] : $m_posts[$i]['author_login']).'</a>';
							}
						 ?></b></td>
						<td style="text-align: center;"><?php if($m_posts[$i]['comments'] < 0) $ucms->cout('module.posts.status.closed_comments'); else echo $m_posts[$i]['comments']; ?></td>
						<td><?php echo $m_posts[$i]['keywords']; ?></td>						
						<td><?php $ucms->cout($status); ?></td>
						<td><?php echo $ucms->date_format($m_posts[$i]['date']); ?></td>
						<td><span class="actions"><?php 
						if($user->has_access("posts", $accessLVL)){ ?><a href="manage.php?module=posts&amp;action=update&amp;id=<?php echo $m_posts[$i]['id']?>"><?php $ucms->cout("module.posts.actions.edit.label"); ?></a><?php }
						if($user->has_access("posts", $accessLVL+1)){ ?>
						| <a href="<?php echo htmlspecialchars(get_current_url('action', 'alert', 'id')); ?>&amp;action=delete&amp;id=<?php echo $m_posts[$i]['id'];?>"><?php $ucms->cout("module.posts.actions.delete.label"); ?></a><?php 
						} ?></span></td>
					</tr>
					<?php	
				};
			}else{
			?>
				<tr>
					<td colspan="9" style="text-align:center;"><?php $ucms->cout("module.posts.no_posts.label"); ?></td>
				</tr>
			<?php
			}
			echo '</table></form>';
		}
			
		function delete_post($id){
			global $user, $udb, $event;	
			if(!$id)
				return false;
			else{
				$id = (int) $id;
				$post = $udb->get_row("SELECT `author`, `category` FROM `".UC_PREFIX."posts` WHERE `id` = '$id' LIMIT 1");
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
					header("Location: ".UCMS_DIR."/admin/manage.php?module=posts");
					return false;
				}
				if($user->has_access("posts", $accessLVL)){
					$count = $udb->query("UPDATE `".UC_PREFIX."categories` SET `posts` = `posts` - 1 WHERE `id` = '$post[category]'");
					$del = $udb->query("DELETE FROM `".UC_PREFIX."posts` WHERE `id` = '$id'");
					$udb->query("DELETE FROM `".UC_PREFIX."comments` WHERE `post` = '$id'");
					if($del){
						$event->do_actions("post.deleted");
						header("Location: ".get_current_url('action', 'alert', 'id')."&alert=deleted");
					}else echo '<div class="error">'.$ucms->cout("module.posts.deleting_post.error", true).'</div>';
				}else{
					header("Location: ".UCMS_DIR."/admin/manage.php?module=posts");
					return false;
				}
			}
		}
			
		function update_post_form($id){
			global $user, $udb, $uc_months, $ucms;
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
				if($user->has_access("posts", $accessLVL)){
					$ucms->template(get_module("path", "posts").'forms/update-post-form.php', false, $post, $category);
				}
			}else{
				header("Location: manage.php?module=posts");
			}
		}

		function update_post($p){
			global $udb, $ucms, $user, $event;
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

			$day    = (!empty($p['day'])    and (int) $p['day']    > 0) ? (int) $p['day']    : date("d");
			$month  = (!empty($p['month'])  and (int) $p['month']  > 0) ? (int) $p['month']  : date("m");
			$year   = (!empty($p['year'])   and (int) $p['year']   > 0) ? (int) $p['year']   : date("Y");
			$hour   = (!empty($p['hour'])   and (int) $p['hour']   > 0) ? (int) $p['hour']   : date("H");
			$minute = (!empty($p['minute']) and (int) $p['minute'] > 0) ? (int) $p['minute'] : date("i");
			$second = (!empty($p['second']) and (int) $p['second'] > 0) ? (int) $p['second'] : date("s");
			$date = "$year-$month-$day $hour:$minute:$second";
			if(!$author)
				$author = $udb->parse_value($p['author']);
			if($author == $user->get_user_id()){
				$accessLVL = 2;
			}elseif($user->get_user_group($author) == 1){
				$accessLVL = 6;
			}else $accessLVL = 4;
			if(empty($title) or empty($body)){
				echo '<br><div class="error">'.$ucms->cout("module.posts.empty_fields.error", true).'</div>';
			}	
			else if($user->has_access("posts", $accessLVL)){
				if(empty($alias)){
					$alias = $ucms->transliterate($title);
				}
				$alias = strtolower(preg_replace('/\s/', "_", $alias));
				$alias = strtolower(preg_replace(URL_REGEXP, "", $alias));

				$test = $udb->get_row("SELECT `id` FROM `".UC_PREFIX."posts` WHERE `alias` = '$alias'");
				if($test and $test['id'] != $id){
					$i = 0;
					$testalias = "";
					while($test){
						$i++;
						$testalias = $alias.'-'.$i;
						$test = $udb->get_row("SELECT `id` FROM `".UC_PREFIX."posts` WHERE `alias` = '$testalias'");
						$testalias = "";
					}
					$alias .= "-$i";
				}
				$test = $udb->get_row("SELECT `category`, `publish` FROM `".UC_PREFIX."posts` WHERE `id` = '$id'");
				if($test['publish'] == 0 and $publish >= 1) $udb->query("UPDATE `".UC_PREFIX."categories` SET `posts` = `posts` + 1 WHERE `id` = '$category'");
				else if($test['publish'] > 0 and $publish == 0) $udb->query("UPDATE `".UC_PREFIX."categories` SET `posts` = `posts` - 1 WHERE `id` = '$category'");
				if($test['category'] != $category and $test['publish'] > 0){
					category_posts_number("add", $category);
					category_posts_number("delete", $test['category']);
				}
				if($comment > -1){
					$comment = $udb->num_rows("SELECT `id` FROM `".UC_PREFIX."comments` WHERE `post` = '$id'");
				}else
					$udb->query("DELETE FROM `".UC_PREFIX."comments` WHERE `post` = '$id'");
				$sql = "UPDATE `".UC_PREFIX."posts` SET `title` = '$title', `body` = '$body', `keywords` = '$keywords', `publish` = '$publish', `alias` = '$alias', `category` = '$category', `comments` = '$comment', `author` = '$author', `date` = '$date' WHERE `id` = '$id'";
				$update = $udb->query($sql);
				if($update){
					$event->do_actions("post.updated");
					header("Location: ".$ucms->get_back_url().(preg_match("/alert/", $ucms->get_back_url()) ? '' : "&alert=updated"));
				}else echo '<div class="error">'.$ucms->cout("module.posts.updating_post.error", true).'</div>';
				
			}
		}

		function manage_categories(){
			global $user, $ucms, $udb;

			$perpage = 25;
			$user_id = $user->get_user_id();
			$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
			$columns = array('name','posts');
			$orderby = isset($_GET['orderby']) ? in_array($_GET['orderby'], $columns) ? htmlspecialchars($_GET['orderby']) : 'parent' : 'parent';
			$order = (isset($_GET['order']) and $_GET['order'] == 'ASC') ? 'DESC' : 'ASC';
	
			$call = $udb->num_rows("SELECT * FROM `".UC_PREFIX."categories`");
			if($orderby != 'parent'){
				$swhere = "";
			}else{
				$swhere = "WHERE `parent` = '0'";
			}
			if($page <= 0) $page = 1;
			$count = $udb->num_rows("SELECT * FROM `".UC_PREFIX."categories` $swhere ORDER BY `$orderby` $order");
			$pages_count = 0;
			if($count != 0){ 
				$pages_count = ceil($count / $perpage); 
				if ($page > $pages_count):
					$page = $pages_count;
				endif; 
				$start_pos = ($page - 1) * $perpage;
				$sql = "SELECT * FROM `".UC_PREFIX."categories` $swhere ORDER BY `$orderby` $order LIMIT $start_pos, $perpage";
				
			}
			$link1 = UCMS_DIR."/admin/manage.php?module=posts&amp;section=categories"
			."&amp;orderby=name&amp;order=".$order
			.(isset($_GET['page']) ? "&amp;page=".$_GET['page'] : "");
	
			$link2 = UCMS_DIR."/admin/manage.php?module=posts&amp;section=categories"
			."&amp;orderby=posts&amp;order=".$order
			.(isset($_GET['page']) ? "&amp;page=".$_GET['page'] : "");
	
			$mark = $order == "ASC" ? '↑' : '↓';
			?>
			<br>
			<b><?php $ucms->cout("module.posts.categories_total.label"); ?></b> <?php echo $call; ?>
			<br><br>
			<form action="manage.php?module=posts&amp;section=categories" method="post">
			<?php if($user->has_access("posts", 7)){ ?>
			<select name="actions" style="width: 250px;">
				<option><?php $ucms->cout("module.posts.selected.label"); ?></option>
				<option value="1"><?php $ucms->cout("module.posts.selected.recount_posts.label"); ?></option>
				<option value="2"><?php $ucms->cout("module.posts.selected.delete.label"); ?></option>
				<option value="3"><?php $ucms->cout("module.posts.selected.fix-orphans.label"); ?></option>
			</select>
			<?php } ?>
			<input type="submit" value="<?php $ucms->cout("module.posts.apply.button"); ?>" class="ucms-button-submit">
			<br><br>
			<?php
			if($pages_count > 1){
				pages($page, $count, $pages_count, 15, false);
				echo '<br><br>';
			}
			echo '<table class="manage">';
			?>
			<tr>
				<th style="width: 15px;"><input type="checkbox" name="select-all" value="1"></th>
				<th><a href="<?php echo $link1; ?>"><?php $ucms->cout("module.posts.table.header.name"); ?> <?php echo $mark; ?></a></th>
				<th><?php $ucms->cout("module.posts.table.header.alias"); ?></th>
				<th style="width: 50px;"><a href="<?php echo $link2; ?>"><?php $ucms->cout("module.posts.table.header.posts_num"); ?> <?php echo $mark; ?></a></th>
				<th style="width: 115px;"><?php $ucms->cout("module.posts.table.header.manage"); ?></th>
			</tr>
			<?php
			
			if($count != 0){
				$category = $udb->get_rows($sql);
				if($orderby == 'parent'){
					$category = get_categories_children($category, 0);
					usort($category, 'parent_sort');
					$parent = '';
					echo categories_tree($category, 0);
				}else{
					for ($i = 0; $i < $count; $i++) { 
						?>
						<tr>
							<td><input type="checkbox" name="item[]" value="<?php echo $category[$i]['id']; ?>"></td>
							<td style="width: 300px;"><?php echo $category[$i]['name']; ?></td>
							<td><?php echo $category[$i]['alias']; ?></td>
							<td><?php echo $category[$i]['posts']; ?></td>
							<td><span class="actions"><a href="manage.php?module=posts&amp;section=categories&amp;update=<?php echo $category[$i]['id']; ?>"><?php $ucms->cout("module.posts.	actions.edit.label"); ?></a> | <a href="manage.php?module=posts&amp;section=categories&amp;delete=<?php echo $category[$i]['id']; ?>"><?php $ucms->cout("module.	posts.actions.delete.label"); ?></a></span></td>
						</tr>
						<?php
					}
				}
			}else{
			?>
				<tr>
					<td colspan="5" style="text-align:center;"><?php $ucms->cout("module.posts.no_categories.label"); ?></td>
				</tr>
			<?php
			}
			echo '</table></form>';
		}

	function parent_sort($a, $b){
		if ($a['parent'] == $b['parent']) {
        	return 0;
   		}
   		return ($a['parent'] < $b['parent']) ? -1 : 1;
	}
	
	function get_categories_children($categories, $root){
		global $udb;
		if(is_array($categories)){
			foreach ($categories as $category){
				$child = $udb->get_rows("SELECT * FROM `".UC_PREFIX."categories` WHERE `parent` = '".$category['id']."'");
				$children[] = $category;
				if($child){
					$children = array_merge($children, get_categories_children($child, $category['id']));
				}
			}
			return $children;
		}else{
			return array();
		}
	}

	function categories_tree($categories, $root, $tree_level = 0){
		global $parent, $user, $ucms, $user_id;
		$tree_level++;
		if($root == 0){
			$tree_level = 0;
		} 
		if(is_array($categories)){
			foreach ($categories as $category){
				if($category['parent'] == $root){
					$children_menu = categories_tree($categories, $category['id'], $tree_level);
					if($root == 0){
						$parent = '';
					}
					else{
						$parent = '';
						for ($i = 0; $i < $tree_level; $i++) { 
							$parent .= '— ';
						}
					}
					$tree[] = '<tr>
						<td><input type="checkbox" name="item[]" value="'.$category['id'].'"></td>
						<td style="width: 300px;">'.$parent.$category['name'].'</td>
						<td>'.$category['alias'].'</td>
						<td>'.$category['posts'].'</td>
						<td><span class="actions"><a href="manage.php?module=posts&amp;section=categories&amp;update='.$category['id'].'">'.$ucms->cout("module.posts.actions.edit.label", true).'</a> | <a href="manage.php?module=posts&amp;section=categories&amp;delete='.$category['id'].'">'.$ucms->cout("module.posts.actions.delete.label", true).'</a></span></td>
					</tr>'.$children_menu;
				}
			}
			if(isset($tree)){
				return implode('', $tree);
			}else{
				return '';
			}
		}else{
			return false;
		}
	}	
	

	function add_category($p){
		global $udb, $event, $ucms;
		$name = $udb->parse_value($p['name']);
		$alias = $udb->parse_value($p['alias']);
		$parent = $udb->parse_value($p['parent']);
		$sort = $udb->parse_value($p['sort']);
		$alias = strtolower(preg_replace('/\s/', "_", $alias));
		$alias = strtolower(preg_replace(URL_REGEXP, "", $alias));
		if($name != '' and $alias != ''){
			$test = $udb->get_row("SELECT `id` FROM `".UC_PREFIX."categories` WHERE `alias` = '$alias'");
			if($test){
				$i = 0;
				$testalias = "";
				while($test){
					$i++;
					$testalias = $alias.'-'.$i;
					$test = $udb->get_row("SELECT `id` FROM `".UC_PREFIX."categories` WHERE `alias` = '$testalias'");
					$testalias = "";
				}
				$alias .= "-$i";
			}
			$add = $udb->query("INSERT IGNORE INTO `".UC_PREFIX."categories` (`id`, `name`, `alias`, `posts`, `parent`, `sort`)
				VALUES (null, '$name', '$alias', '0', '$parent', '$sort')");
			if($add) {
				$event->do_actions("category.added");
				header("Location: ".get_current_url("alert", 'delete', 'update')."&alert=added");
			}else echo '<div class="error">'.$ucms->cout("module.posts.adding_category.error", true).'</div>';
			
		}else echo '<div class="error">'.$ucms->cout("module.posts.empty_fields.categories.error", true).'</div>';
	}

	function update_category($p){
		global $udb, $event, $ucms;
		$id = $p['id'] != '' ? (int) $p['id'] : 0;
		$name = $udb->parse_value($p['name']);
		$alias = $udb->parse_value($p['alias']);
		$parent = $udb->parse_value($p['parent']);
		$sort = $udb->parse_value($p['sort']);
		
		if($name != ''){
			$update = $udb->query("UPDATE `".UC_PREFIX."categories` SET `name` = '$name' WHERE `id` = '$id'");
		}
		if($alias != ''){
			$alias = strtolower(preg_replace('/\s/', "_", $alias));
			$alias = strtolower(preg_replace(URL_REGEXP, "", $alias));
			$test = $udb->get_row("SELECT `id` FROM `".UC_PREFIX."categories` WHERE `alias` = '$alias'");
			if($test and $test['id'] != $id){
				$i = 0;
				$testalias = "";
				while($test){
					$i++;
					$testalias = $alias.'-'.$i;
					$test = $udb->get_row("SELECT `id` FROM `".UC_PREFIX."categories` WHERE `alias` = '$testalias'");
					$testalias = "";
				}
				$alias .= "-$i";
			}
			$update = $udb->query("UPDATE `".UC_PREFIX."categories` SET `alias` = '$alias' WHERE `id` = '$id'");
		}
		if($parent != ''){
			$update = $udb->query("UPDATE `".UC_PREFIX."categories` SET `parent` = '$parent' WHERE `id` = '$id'");
		}
		if($sort != ''){
			$update = $udb->query("UPDATE `".UC_PREFIX."categories` SET `sort` = '$sort' WHERE `id` = '$id'");
		}
		if($update) {
			$event->do_actions("category.updated");
			header("Location: ".UCMS_DIR."/admin/manage.php?module=posts&section=categories&alert=updated");
		}else echo '<div class="error">'.$ucms->cout("module.posts.updating_category.error", true).'</div>';
	}

	function delete_category($id){
		global $udb, $user, $event, $ucms;
		$id = (int) $id;
		if($id > 1 and $user->has_access("posts", 7)){
			$delete = $udb->query("DELETE FROM `".UC_PREFIX."categories` WHERE `id` = '$id' LIMIT 1");
			if($delete) {
				$posts = $udb->query("UPDATE `".UC_PREFIX."posts` SET `category` = '1' WHERE `category` = '$id'");
				$num_posts = $udb->num_rows("SELECT `id` FROM `".UC_PREFIX."posts` WHERE `category` = '1'");
				$recount = $udb->query("UPDATE `".UC_PREFIX."categories` SET `posts` = '$num_posts' WHERE `id` = '1'");
				fix_orphans($id);
				header("Location: ".get_current_url("alert", 'delete', 'update')."&alert=deleted");
			}else echo '<div class="error">'.$ucms->cout("module.posts.deleting_category.error", true).'</div>';
		}
	}

	function fix_orphans($id){
		if(!$id) return false;
		global $udb;
		$id = (int) $id;
		$orphan = $udb->query("UPDATE `".UC_PREFIX."categories` SET `parent` = '0' WHERE `parent` = '$id'");
		return $orphan;
	}
?>