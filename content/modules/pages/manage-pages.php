<?php
	function pages_sort($a, $b){
		global $orderby, $order;
		if ($a[$orderby] == $b[$orderby]) {
			return 0;
		}
		if($order == "ASC")
			return ($a[$orderby] < $b[$orderby]) ? -1 : 1;
		else
			return ($a[$orderby] > $b[$orderby]) ? -1 : 1;
	}

	function add_page_form(){
		global $ucms;
		$ucms->template(get_module('path', 'pages').'forms/add-page-form.php', false);
	}

	function add_pages_tree($pages, $root, $tree_level = 0, $selected = -1){
		global $parent;
		$tree_level++;
		if($root == 0){
			$tree_level = 0;
		} 
		if(is_array($pages)){
			foreach ($pages as $page){
				if($page['parent'] == $root){
					$children_menu = add_pages_tree($pages, $page['id'], $tree_level, $selected);
					if($root == 0){
						$parent = '';
					}
					else{
						$parent = '';
						for ($i = 0; $i < $tree_level; $i++) { 
							$parent .= '—';
						}
					}

					$tree[] = '<option value="'.$page['id'].'" '.($page['id'] == $selected ? 'selected' : '').'>'.$parent.$page['title'].'</option>'.$children_menu;
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

	function add_page($p){
		global $udb, $user, $ucms, $event;
		$title = $udb->parse_value($p['title']);
		$body = $udb->parse_value($p['body']);
		$publish = (int) $p['publish'];
		$alias = $udb->parse_value($p['alias']);
		$parent = $udb->parse_value($p['parent']);
		$author = isset($p['author']) ? $user->get_user_id($udb->parse_value($p['author'])) : $user->get_user_id();
		$sort = $udb->parse_value($p['sort']);
		$author_login = (int) $author > 0 ? $user->get_user_login($author) : $author; 
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
		if(empty($alias)){
			$alias = $ucms->transliterate($title);
		}
		$alias = strtolower(preg_replace('/\s/', "_", $alias));
		$alias = strtolower(preg_replace(URL_REGEXP, "", $alias));
		if(preg_match("#(@parent_alias@|@parent_title@|@parent_id@)#", PAGE_SEF_LINK)){
			$test_where = "WHERE `p`.`parent` = '$parent'";
		}else{
			$test_where = "";
		}

		$test = $udb->get_rows("SELECT `p`.*, `u`.`login` AS `author_login`, `pa`.`id` AS `parent_id`, `pa`.`title` AS `parent_title`,
			`pa`.`alias` AS `parent_alias` FROM `".UC_PREFIX."pages` AS `p` 
			LEFT JOIN `".UC_PREFIX."users` AS `u` ON `u`.`id` = `p`.`author`
			LEFT JOIN `".UC_PREFIX."pages` AS `pa` ON `pa`.`id` = `p`.`parent`
			$test_where");
		$p = 1;
		$new_alias = $alias;
		if(!empty($test)){
			for ($i = 0; $i < count($test); $i++) { 
				if($new_alias == $test[$i]['alias']){
					$new_alias = "$alias-$p";
					$p++;
				}
			}
		}
		$alias = $new_alias;
		if(empty($title) or empty($body)){
			echo '<br><div class="error">'.$ucms->cout("module.pages.empty_fields.error", true).'</div>';
		}	
		else{
			$sql = "INSERT INTO `".UC_PREFIX."pages` (`id`, `title`, `alias`, `author`, `body`, `publish`, `date`, `parent`, `sort`)
			VALUES (null, '$title','$alias', '$author', '$body', '$publish', '$date', '$parent', '$sort')";
			$add = $udb->query($sql);
			if($add){
				$event->do_actions("page.added", array($author, $title));
				header("Location: ".get_current_url('alert', 'action')."&alert=added");
			}
			
		}
	}
		
	function manage_pages(){
		global $udb, $user, $ucms, $order, $orderby;

		$status = isset($_GET['status']) ? htmlspecialchars($_GET['status']) : "";
		$swhere = '';
		$safe_query = '';
		$overwrite_where = '1';
		switch ($status) {
			case 'published':
				$swhere = "WHERE `p`.`publish` > 0 AND `p`.`parent` = 0";
				$overwrite_where = "`p`.`publish` > 0";
			break;

			case 'draft':
				$swhere = "WHERE `p`.`publish` = 0 AND `p`.`parent` = 0";
				$overwrite_where = "`p`.`publish` = 0";
			break;

			case 'parent':
				$swhere = "WHERE `p`.`parent` = 0";
				$overwrite_where = "`p`.`parent` = 0";
				$only_parent = true;
			break;

			default:
				$swhere = "WHERE `p`.`parent` = 0";
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
				if($user->has_access("pages", $accessLVL)){
					$items[] = $id;
				}
			}
			$ids = implode(',', $items);
			if (count($items) > 0) {
				switch ($action) {
					case 1:
						$upd = $udb->query("UPDATE `".UC_PREFIX."pages` SET `publish` = '1' WHERE `id` IN ($ids)");
						if (count($items) > 1) {
							header("Location: ".get_current_url('alert')."&alert=published_multiple");
						}else 
							header("Location: ".get_current_url('alert')."&alert=published");
					break;
	
					case 2:
						$upd = $udb->query("UPDATE `".UC_PREFIX."pages` SET `publish` = '0' WHERE `id` IN ($ids)");
						if (count($items) > 1) {
							header("Location: ".get_current_url('alert')."&alert=drafted_multiple");
						}else 
							header("Location: ".get_current_url('alert')."&alert=drafted");
					break;
	
					case 3:
						$del = $udb->query("DELETE FROM `".UC_PREFIX."pages` WHERE `id` IN ($ids)");
						if (count($items) > 1) {
							header("Location: ".get_current_url('alert')."&alert=deleted_multiple");
						}else 
							header("Location: ".get_current_url('alert')."&alert=deleted");
					break;

					case 4:
						foreach ($items as $com) {
							$com_parent = $udb->get_val("SELECT `parent` FROM `".UC_PREFIX."pages` WHERE `id` = '$com'");
							$parent_exists = $udb->get_row("SELECT `title` FROM `".UC_PREFIX."pages` WHERE `id` = '$com_parent'");
							if(!$parent_exists){
								$udb->query("UPDATE `".UC_PREFIX."pages` SET `parent` = '0' WHERE `id` = '$com'");
							}
						}
						header("Location: ".get_current_url('alert')."&alert=updated");
					break;
					
				}
			}
		}
		
		$user_id = $user->get_user_id();
		$columns = array('title','author', 'date');
		$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
		$orderby = isset($_GET['orderby']) ? in_array($_GET['orderby'], $columns) ? htmlspecialchars($_GET['orderby']) : 'parent' : 'parent';
		$order = (isset($_GET['order']) and $_GET['order'] == 'ASC') ? 'DESC' : 'ASC';
		if(!isset($results)){
			$call = $udb->num_rows("SELECT * FROM `".UC_PREFIX."pages`");
			$cpublished = $udb->num_rows("SELECT * FROM `".UC_PREFIX."pages` WHERE `publish` > 0");
			$cdraft = $udb->num_rows("SELECT * FROM `".UC_PREFIX."pages` WHERE `publish` = 0");
			$cparent = $udb->num_rows("SELECT * FROM `".UC_PREFIX."pages` WHERE `parent` = 0");

			if($page <= 0) $page = 1;
			if($user->has_access("pages", 4))
				$count = $udb->num_rows("SELECT * FROM `".UC_PREFIX."pages` AS `p` $swhere ORDER BY `$orderby` $order");
			else $count = $udb->num_rows("SELECT * FROM `".UC_PREFIX."pages` AS `p` WHERE `author` = '$user_id' ORDER BY `$orderby` $order, `sort` ASC");
			$pages_count = 0;
			if($count != 0){ 
				$pages_count = ceil($count / $perpage); 
				if ($page > $pages_count):
					$page = $pages_count;
				endif; 
				$start_pos = ($page - 1) * $perpage;
				if(!$user->has_access("pages", 4)){
					$swhere = "WHERE `p`.`author` = '$user_id'";
				}
					$sql = "SELECT `p`.*, `pa`.`id` AS `parent_id`, `pa`.`alias` AS `parent_alias`, `pa`.`title` AS `parent_title`, `u`.`login`
					AS `author_login`, `u`.`group` AS `author_group`, `uf`.`value` AS `author_nickname` FROM `".UC_PREFIX."pages` AS `p` FORCE INDEX (PRIMARY)
					LEFT JOIN `".UC_PREFIX."pages` AS `pa` ON `pa`.`id` = `p`.`parent`
					LEFT JOIN `".UC_PREFIX."users` AS `u` ON `u`.`id` = `p`.`author` 
					LEFT JOIN `".UC_PREFIX."usersinfo` AS `uf` ON `uf`.`user_id` = `u`.`id` AND `name` = 'nickname'
					$swhere ORDER BY `$orderby` $order LIMIT $start_pos, $perpage";
			}else $sql  = "SELECT * FROM `".UC_PREFIX."pages` WHERE `id` = '0'";
		}else{
			$call = $status == '' ? $count : $ucms->cout("module.pages.search.label", true);
			$cpublished = $status == 'published' ? $count : $ucms->cout("module.pages.search.label", true);
			$cdraft = $status == 'draft' ? $count : $ucms->cout("module.pages.search.label", true);
			$cparent = $status == 'parent' ? $count : $ucms->cout("module.pages.search.label", true);
		}
		$s_link = "<a href=\"".UCMS_DIR."/admin/manage.php?module=pages";
		$m_link = (isset($_GET['query']) ? "&amp;query=".$safe_query : "")
				  .(isset($_GET['orderby']) ? "&amp;orderby=".$orderby : "")
				  .(isset($_GET['order']) ? "&amp;order=".$order : "")
				  .(isset($_GET['page']) ? "&amp;page=".$page : "");
		$lall = $status != '' ? $s_link.$m_link."\">".$ucms->cout("module.pages.all.link", true)."</a>" : "<b>".$ucms->cout("module.pages.all.link", true)."</b>"; 
		$lpublished = $status != 'published' ? $s_link."&amp;status=published".$m_link."\">".$ucms->cout("module.pages.published.link", true)."</a>" : "<b>".$ucms->cout("module.pages.published.link", true)."</b>"; 
		$ldraft =  $status != 'draft' ? $s_link."&amp;status=draft".$m_link."\">".$ucms->cout("module.pages.drafts.link", true)."</a>" : "<b>".$ucms->cout("module.pages.drafts.link", true)."</b>"; 
		$lparent =  $status != 'parent' ? $s_link."&amp;status=parent".$m_link."\">".$ucms->cout("module.pages.parents.link", true)."</a>" : "<b>".$ucms->cout("module.pages.parents.link", true)."</b>"; 
		?>
		<br>
		<?php $ucms->cout("module.pages.show.label"); ?> <?php echo $lall." ($call)"; ?> | <?php echo $lpublished." ($cpublished)"; ?> | <?php echo $ldraft." ($cdraft)"; ?> | <?php echo $lparent." ($cparent)"; ?>
		<br><br>
		<form action="manage.php?module=pages" method="post">
		<?php if($user->has_access("pages", 2)){ ?>
		<select name="actions" style="width: 250px;">
			<option><?php $ucms->cout("module.pages.selected.label"); ?></option>
			<option value="1"><?php $ucms->cout("module.pages.selected.publish.option"); ?></option>
			<option value="2"><?php $ucms->cout("module.pages.selected.draft.option"); ?></option>
			<option value="3"><?php $ucms->cout("module.pages.selected.delete.option"); ?></option>
			<option value="4"><?php $ucms->cout("module.pages.selected.fix-orphans.option"); ?></option>
		</select>
		<?php } ?>
		<input type="submit" value="<?php $ucms->cout("module.pages.selected.apply.button"); ?>" class="ucms-button-submit">
		<br>
		<?php
		if($pages_count > 1){
			echo "<br>";
			pages($page, $count, $pages_count, 15, false);
			echo '<br>';
		}?><br>
		<table class="manage">
		<?php
		$link1 = UCMS_DIR."/admin/manage.php?module=pages"
		.(isset($_GET['query']) ? "&amp;query=".$safe_query : "")
		.(isset($_GET['status']) ? "&amp;status=".$status : "")
		."&amp;orderby=title&amp;order=".$order
		.(isset($_GET['page']) ? "&amp;page=".$page : "");
		$link2 = UCMS_DIR."/admin/manage.php?module=pages"
		.(isset($_GET['query']) ? "&amp;query=".$safe_query : "")
		.(isset($_GET['status']) ? "&amp;status=".$status : "")
		."&amp;orderby=author&amp;order=".$order
		.(isset($_GET['page']) ? "&amp;page=".$page : "");
		$link3 = UCMS_DIR."/admin/manage.php?module=pages"
		.(isset($_GET['query']) ? "&amp;query=".$safe_query : "")
		.(isset($_GET['status']) ? "&amp;status=".$status : "")
		."&amp;orderby=date&amp;order=".$order
		.(isset($_GET['page']) ? "&amp;page=".$page : "");

		$mark = $order == "ASC" ? '↑' : '↓';
		?>
		<tr>
			<th style="width: 15px;"><input type="checkbox" name="select-all" value="1"></th>
			<th><a href="<?php echo $link1; ?>"><?php $ucms->cout("module.pages.table.name.header"); ?> <?php echo $mark; ?></a></th>
			<th><a href="<?php echo $link2; ?>"><?php $ucms->cout("module.pages.table.author.header"); ?> <?php echo $mark; ?></a></th>
			<th style="width: 200px;"><?php $ucms->cout("module.pages.table.status.header"); ?></th>
			<th><a href="<?php echo $link3; ?>"><?php $ucms->cout("module.pages.table.date.header"); ?> <?php echo $mark; ?></a></th>
			<th style="width: 115px;"><?php $ucms->cout("module.pages.table.manage.header"); ?></th>
		</tr>
		<?php
		if(!isset($results)){
			$pages = $udb->get_rows($sql);
		}else{
			$pages = $results;
		}
		if($pages){
			if(!isset($only_parent) and !isset($results)){
				$pages = get_pages_children($pages, 0);
				usort($pages, 'pages_sort');
			}
			$parent = '';
			if($orderby == 'parent' and !isset($results)){
				echo pages_tree($pages, 0, true);
			}
			else{
				for($i = 0; $i < count($pages); $i++){
					$status = $pages[$i]['publish'] == 1 ? $ucms->cout("module.pages.table.published.status", true) : $ucms->cout("module.pages.table.draft.status", true);
					$link = NICE_LINKS ? page_sef_links($pages[$i]) : UCMS_DIR.'/?p='.$pages[$i]['id'];
					if($user_id == $pages[$i]['author'])
						$accessLVL = 3;
					elseif($pages[$i]['author_group'] == 1){
						$accessLVL = 6;
					}else $accessLVL = 4;
					$tags = '<p><a><pre><img><br><b><em><i><strike><span>';
					?>
					<tr>
						<td><input type="checkbox" name="item[]" value="<?php echo $pages[$i]['id']; ?>"></td>
						<td><a target="_blank" href="<?php echo $link; ?>"><?php 
						echo !isset($results) ? htmlspecialchars($pages[$i]['title']) : strip_tags($pages[$i]['title'], $tags); ?></a></td>
						<td><b><?php 
						if((int) $pages[$i]['author'] == 0){
							echo $pages[$i]['author'];
						}else{
							echo '<a href="'.UCMS_DIR.'/admin/manage.php?module=users&action=update&id='.$pages[$i]['author'].'">'
						 	.(!empty($pages[$i]['author_nickname']) ? $pages[$i]['author_nickname'] : $pages[$i]['author_login']).'</a>';
						}
						?></b></td>
						<td><?php echo $status; ?></td>
							<td><?php echo $ucms->date_format($pages[$i]['date']); ?></td>
						<td><span class="actions"><?php if($user->has_access("pages", $accessLVL)){ ?>
						<a href="<?php echo UCMS_DIR ?>/admin/manage.php?module=pages&amp;action=update&amp;id=<?php echo $pages[$i]['id']?>"><?php $ucms->cout("module.pages.table.edit.button"); ?></a><?php } ?>
						<?php if($user->has_access("pages", $accessLVL+1)){ ?>
						 | <a href="<?php echo get_current_url('alert', 'action', 'id') ?>&amp;action=delete&amp;id=<?php echo $pages[$i]['id'];?>"><?php $ucms->cout("module.pages.table.delete.button"); ?></a><?php } ?></span></td>
					</tr>
					<?php
				}
			}
		}else{
			?>
			<tr>
				<td colspan="6" style="text-align:center;"><?php $ucms->cout("module.pages.table.no_pages.label"); ?></td>
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

	function pages_tree($pages, $root, $tree_level = 0){
		global $parent, $user, $ucms, $user_id, $udb;
		$tree_level++;
		if($root == 0){
			$tree_level = 0;
		} 
		if(is_array($pages)){
			foreach ($pages as $page){
				if($page['parent'] == $root){
					$children_menu = pages_tree($pages, $page['id'], $tree_level);
					$link = page_sef_links($page);
					if($root == 0){
						$parent = '';
					}
					else{
						$parent = '';
						for ($i = 0; $i < $tree_level; $i++) { 
							$parent .= '— ';
						}
					}

					$status = $page['publish'] == 1 ? $ucms->cout("module.pages.table.published.status", true) : $ucms->cout("module.pages.table.draft.status", true);
					$link = NICE_LINKS ? page_sef_links($page) : UCMS_DIR.'/?p='.$page['id'];
					if($user_id == $page['author'])
						$accessLVL = 3;
					elseif($page['author_group'] == 1){
						$accessLVL = 6;
					}else $accessLVL = 4;

					if(!empty($_GET['status']) and $_GET['status'] == 'published'){
						if($page['publish'] == 0){
							$tree[] = $children_menu;
						}else{
							$get_parent = $udb->get_row("SELECT `publish`,`title` FROM `".UC_PREFIX."pages` WHERE `id` = '$root' AND `publish` = 0");
							$parent_title = !empty($get_parent) ? " (".$ucms->cout("module.pages.table.parent.page.title", true, $get_parent['title']).")" : '';
							$tree[] = '<tr>
							<td><input type="checkbox" name="item[]" value="'.$page['id'].'"></td>
							<td><a target="_blank" href="'.$link.'">'.$parent.$page['title'].$parent_title.'</a></td>
							<td><b>'.((int) $page['author'] == 0 ? $page['author'] 
							: '<a href="'.UCMS_DIR.'/admin/manage.php?module=users&action=update&id='.$page['author'].'">'
						 	.(!empty($page['author_nickname']) ? $page['author_nickname'] : $page['author_login']).'</a>').'</b></td>
							<td>'.$status.'</td>
							<td>'.$ucms->date_format($page['date']).'</td>
							<td><span class="actions">'.($user->has_access("pages", $accessLVL) ? 
								'<a href="'.UCMS_DIR.'/admin/manage.php?module=pages&amp;action=update&amp;id='.$page['id'].'">'.$ucms->cout("module.pages.table.edit.button", true).'</a>' : '')
							.($user->has_access("pages", $accessLVL+1) 
								? ' | <a href="'.UCMS_DIR.'/admin/manage.php?module=pages&amp;action=delete&amp;id='.$page['id'].'">'.$ucms->cout("module.pages.table.delete.button", true).'</a>' : '').'</span></td>
							</tr>'.$children_menu;
						}
					}else{
						$tree[] = '<tr>
							<td><input type="checkbox" name="item[]" value="'.$page['id'].'"></td>
							<td><a target="_blank" href="'.$link.'">'.$parent.$page['title'].'</a></td>
							<td><b>'.((int) $page['author'] == 0 ? $page['author']
							 : '<a href="'.UCMS_DIR.'/admin/manage.php?module=users&action=update&id='.$page['author'].'">'
						 	.(!empty($page['author_nickname']) ? $page['author_nickname'] : $page['author_login']).'</a>').'</b></td>
							<td>'.$status.'</td>
							<td>'.$ucms->date_format($page['date']).'</td>
							<td><span class="actions">'.($user->has_access("pages", $accessLVL) ? 
								'<a href="'.UCMS_DIR.'/admin/manage.php?module=pages&amp;action=update&amp;id='.$page['id'].'">'.$ucms->cout("module.pages.table.edit.button", true).'</a>' : '')
							.($user->has_access("pages", $accessLVL+1) 
								? ' | <a href="'.UCMS_DIR.'/admin/manage.php?module=pages&amp;action=delete&amp;id='.$page['id'].'">'.$ucms->cout("module.pages.table.delete.button", true).'</a>' : '').'</span></td>
							</tr>'.$children_menu;
					}
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

	function get_pages_children($pages, $root){
		global $udb;
		if(is_array($pages)){
			foreach ($pages as $page){
				$child = $udb->get_rows("SELECT `p`.*, `pa`.`id` AS `parent_id`, `pa`.`alias` AS `parent_alias`, `pa`.`title` AS `parent_title`, `u`.`login`
					AS `author_login`, `u`.`group` AS `author_group`, `uf`.`value` AS `author_nickname` FROM `".UC_PREFIX."pages` AS `p` FORCE INDEX (PRIMARY)
					LEFT JOIN `".UC_PREFIX."pages` AS `pa` ON `pa`.`id` = `p`.`parent`
					LEFT JOIN `".UC_PREFIX."users` AS `u` ON `u`.`id` = `p`.`author` 
					LEFT JOIN `".UC_PREFIX."usersinfo` AS `uf` ON `uf`.`user_id` = `u`.`id` AND `name` = 'nickname'
					WHERE `p`.`parent` = '".$page['id']."'");
				$children[] = $page;
				if($child){
					$children = array_merge($children, get_pages_children($child, $page['id']));
				}
			}
			return $children;
		}else{
			return array();
		}
	}

	function delete_page($id){
		global $user, $udb, $event;
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
				header("Location: ".UCMS_DIR."/admin/manage.php?module=pages");
				return false;
			}
			if($user->has_access("pages", $accessLVL)){
				$del = $udb->query("DELETE FROM `".UC_PREFIX."pages` WHERE `id` = '$id'");
				if($del){
					fix_orphans($id);
					$event->do_actions("page.deleted");
					header("Location: ".get_current_url('alert', 'action')."&alert=deleted");
				}
			}else{
				header("Location: ".UCMS_DIR."/admin/manage.php?module=pages");
				return false;
			}
		}
	}
			
	function update_page_form($id){
		global $ucms;
		$ucms->template(get_module('path', 'pages').'forms/update-page-form.php', false, $id);
	}

	function update_page($p){
		global $udb, $user, $ucms, $event;
		$id = (int) $p['id'];
		$alias = $udb->parse_value($p['alias']);
		$title = $udb->parse_value($p['title']);
		$body = $udb->parse_value($p['body']);
		$parent = $udb->parse_value($p['parent']);
		$publish = (int) $p['publish'];
		$author = isset($p['author']) ? $user->get_user_id($udb->parse_value($p['author'])) : $user->get_user_id();
		$sort = $udb->parse_value($p['sort']);
		$author_login = (int) $author > 0 ? $user->get_user_login($author) : $author; 
		$day    = (!empty($p['day'])    and (int) $p['day']    > 0) ? (int) $p['day']    : date("d");
		$month  = (!empty($p['month'])  and (int) $p['month']  > 0) ? (int) $p['month']  : date("m");
		$year   = (!empty($p['year'])   and (int) $p['year']   > 0) ? (int) $p['year']   : date("Y");
		$hour   = (!empty($p['hour'])   and (int) $p['hour']   > 0) ? (int) $p['hour']   : date("H");
		$minute = (!empty($p['minute']) and (int) $p['minute'] > 0) ? (int) $p['minute'] : date("i");
		$second = (!empty($p['second']) and (int) $p['second'] > 0) ? (int) $p['second'] : date("s");
		
		$date = "$year-$month-$day $hour:$minute:$second";

		if(!$author)
			$author = $udb->parse_value($p['author']);
		if(empty($alias)){
			$alias = $ucms->transliterate($title);
		}
		$alias = strtolower(preg_replace('/\s/', "_", $alias));
		$alias = strtolower(preg_replace(URL_REGEXP, "", $alias));
		if(preg_match("#(@parent_alias@|@parent_title@|@parent_id@)#", PAGE_SEF_LINK)){
			$test_where = "WHERE `p`.`parent` = '$parent'";
		}else{
			$test_where = "";
		}
		$test = $udb->get_rows("SELECT `p`.*, `u`.`login` AS `author_login`, `pa`.`id` AS `parent_id`, `pa`.`title` AS `parent_title`,
			`pa`.`alias` AS `parent_alias` FROM `".UC_PREFIX."pages` AS `p`  FORCE INDEX (PRIMARY)
			LEFT JOIN `".UC_PREFIX."users` AS `u` ON `u`.`id` = `p`.`author`
			LEFT JOIN `".UC_PREFIX."pages` AS `pa` ON `pa`.`id` = `p`.`parent`
			$test_where");
		$p = 1;
		$new_alias = $alias;
		if(!empty($test)){
			for ($i = 0; $i < count($test); $i++) { 
				if($new_alias == $test[$i]['alias'] and $test[$i]['id'] != $id){
					$new_alias = "$alias-$p";
					$p++;
				}
			}
		}
		$alias = $new_alias;
		if(empty($title) or empty($body)){
			echo '<br><div class="error">'.$ucms->cout("module.pages.empty_fields.error", true).'</div>';
		}	
		else{
			$sql= "UPDATE `".UC_PREFIX."pages` SET `title` = '$title', `body` = '$body', `publish` = '$publish', `alias` = '$alias', `author` = '$author', `date` = '$date', `parent` = '$parent', `sort` = '$sort' WHERE `id` = '$id'";
			$upd = $udb->query($sql);
			if($upd){
				$event->do_actions("page.updated");
				header("Location: ".$ucms->get_back_url().(preg_match("/alert/", $ucms->get_back_url()) ? '' : "&alert=updated"));
			}	
		}
	}
	
	function fix_orphans($id){
		if(!$id) return false;
		global $udb;
		$id = (int) $id;
		$orphan = $udb->query("UPDATE `".UC_PREFIX."pages` SET `parent` = '0' WHERE `parent` = '$id'");
		return $orphan;
	}
?>
