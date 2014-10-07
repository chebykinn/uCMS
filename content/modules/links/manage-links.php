<?php
function manage_links(){
	global $udb, $user, $ucms;

	$status = isset($_GET['status']) ? htmlspecialchars($_GET['status']) : "";
	$swhere = '';
	$safe_query = '';
	$overwrite_where = '1';
	$overwrite_perpage = $perpage = 25;
	switch ($status) {
		case 'published':
			$swhere = "WHERE `l`.`publish` = 1";
			$overwrite_where = '`l`.`publish` = 1';
		break;

		case 'hidden':
			$swhere = "WHERE `l`.`publish` = 0";
			$overwrite_where = '`l`.`publish` = 0';
		break;

		default:
			$swhere = "";
			$overwrite_where = '1';
		break;
	}

	include get_module("path", "search").'search.php';

	if (isset($_POST['item']) and isset($_POST['actions'])){
		$items = array();
		$action = (int) $_POST['actions'];
		foreach ($_POST['item'] as $id) {
			$id = (int) $id;
			$link = $udb->get_row("SELECT * FROM `".UC_PREFIX."links` WHERE `id` = '$id'");
			$userd = $udb->get_row("SELECT `id`, `group` FROM `".UC_PREFIX."users` WHERE `id` = '$link[author]' LIMIT 1");
			if($link and count($link) > 0){
				if($userd){
					if($userd['id'] == $user->get_user_id()){
						$accessLVL = 2;
					}elseif($userd['group'] == ADMINISTRATOR_GROUP_ID){
						$accessLVL = 6;
					}else{
						$accessLVL = 4;
					}
				}else{
					$accessLVL = 4;
				}
			}
			if($action == 3) $accessLVL++;
			if($user->has_access("links", $accessLVL)){
				$items[] = $id;
			}
		}
		$ids = implode(',', $items);
		if (count($items) > 0) {
			switch ($action) {
				case 1:
					$upd = $udb->query("UPDATE `".UC_PREFIX."links` SET `publish` = '1' WHERE `id` IN ($ids)");
					if (count($items) > 1) {
						header("Location: ".get_current_url("alert", "action", "id")."&alert=published_multiple");
					}else 
						header("Location: ".get_current_url("alert", "action", "id")."&alert=published");
				break;
	
				case 2:
					$upd = $udb->query("UPDATE `".UC_PREFIX."links` SET `publish` = '0' WHERE `id` IN ($ids)");
					if (count($items) > 1) {
						header("Location: ".get_current_url("alert", "action", "id")."&alert=hidden_multiple");
					}else 
						header("Location: ".get_current_url("alert", "action", "id")."&alert=hidden");
				break;
	
				case 3:
					$del = $udb->query("DELETE FROM `".UC_PREFIX."links` WHERE `id` IN ($ids)");
					if (count($items) > 1) {
						header("Location: ".get_current_url("alert", "action", "id")."&alert=deleted_multiple");
					}else 
						header("Location: ".get_current_url("alert", "action", "id")."&alert=deleted");
				break;
				
			}
		}
	}
	$user_id = $user->get_user_id();
	$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
	$columns = array('name','author', 'date');
	$orderby = isset($_GET['orderby']) ? in_array($_GET['orderby'], $columns) ? '`l`.`'.htmlspecialchars($_GET['orderby']).'`' : '`l`.`id`' : '`l`.`id`';
	$order = (isset($_GET['order']) and $_GET['order'] == 'DESC') ? 'ASC' : 'DESC';
	
	if(!isset($results)){
		$call = $udb->num_rows("SELECT * FROM `".UC_PREFIX."links`");
		$cpublished = $udb->num_rows("SELECT * FROM `".UC_PREFIX."links` WHERE `publish` > 0");
		$cdraft = $udb->num_rows("SELECT * FROM `".UC_PREFIX."links` WHERE `publish` = 0");
		
		if($page <= 0) $page = 1;
		$count = $udb->num_rows("SELECT * FROM `".UC_PREFIX."links` AS `l` $swhere ORDER BY $orderby $order");
		$pages_count = 0;
		if($count != 0){ 
			$pages_count = ceil($count / $perpage); 
			if ($page > $pages_count):
				$page = $pages_count;
			endif; 
			$start_pos = ($page - 1) * $perpage;
			$sql = "SELECT `l`.*, `u`.`login` AS `author_login`, `u`.`group` AS `author_group`, `uf`.`value` AS `author_nickname` FROM `".UC_PREFIX."links` AS `l`
			LEFT JOIN `".UC_PREFIX."users` AS `u` ON `u`.`id` = `l`.`author`
			LEFT JOIN `".UC_PREFIX."usersinfo` AS `uf` ON `uf`.`user_id` = `u`.`id` AND `uf`.`name` = 'nickname'
 			$swhere ORDER BY $orderby $order LIMIT $start_pos, $perpage";
		}else $sql = "SELECT * FROM `".UC_PREFIX."links` WHERE `id` = '0'";
	}else{
		$call = $status == '' ? $count : $ucms->cout("module.links.search.label", true);
		$cpublished = $status == 'published' ? $count : $ucms->cout("module.links.search.label", true);
		$cdraft = $status == 'draft' ? $count : $ucms->cout("module.links.search.label", true);
	}

	$s_link = "<a href=\"".UCMS_DIR."/admin/manage.php?module=links";
	$m_link = (isset($_GET['orderby']) ? "?orderby=".$orderby : "")
	.(isset($_GET['query']) ? "&amp;query=".$safe_query : "")
	.(isset($_GET['order']) ? "&amp;order=".$order : "")
	.(isset($_GET['page']) ? "&amp;page=".$page : "");

	$lall = $status != '' ? $s_link.$m_link."\">".$ucms->cout("module.links.all.link", true)."</a>" : "<b>".$ucms->cout("module.links.all.link", true)."</b>"; 
	$lpublished = $status != 'published' ? $s_link."&amp;status=published".$m_link."\">".$ucms->cout("module.links.published.link", true)."</a>" : "<b>".$ucms->cout("module.links.published.link", true)."</b>"; 
	$ldraft =  $status != 'hidden' ? $s_link."&amp;status=hidden".$m_link."\">".$ucms->cout("module.links.hidden.link", true)."</a>" : "<b>".$ucms->cout("module.links.hidden.link", true)."</b>"; 
	?>
	<br>
	<?php $ucms->cout("module.links.show.label"); ?> <?php echo $lall." ($call)"; ?> | <?php echo $lpublished." ($cpublished)"; ?> | <?php echo $ldraft." ($cdraft)"; ?>
	<br><br>
	<form action="manage.php?module=links" method="post">
	<?php if($user->has_access("links", 4)){ ?>
	<select name="actions" style="width: 250px;">
		<option><?php $ucms->cout("module.links.selected.option"); ?></option>
		<option value="1"><?php $ucms->cout("module.links.selected.publish.option"); ?></option>
		<option value="2"><?php $ucms->cout("module.links.selected.hide.option"); ?></option>
		<option value="3"><?php $ucms->cout("module.links.selected.delete.option"); ?></option>
	</select>
	<?php } ?>
	<input type="submit" value="<?php $ucms->cout("module.links.selected.apply.button"); ?>" class="ucms-button-submit">
	<br>
	<?php
	if($pages_count > 1){
		echo "<br>";
		pages($page, $count, $pages_count, 15, false);
		echo '<br>';
	} ?><br>
	<table class="manage">
	<?php 
	$s_link = UCMS_DIR."/admin/manage.php?module=links";
	$m_link = (isset($_GET['query']) ? "&amp;status=".$safe_query : "")
	.(isset($_GET['status']) ? "&amp;status=".$status : "");
	$p_link = (isset($_GET['page']) ? "&amp;page=".$page : "");

	$link1 = $s_link.$m_link."&amp;orderby=name&amp;order=".$order.$p_link;

	$link2 = $s_link.$m_link."&amp;orderby=author&amp;order=".$order.$p_link;

	$link3 = $s_link.$m_link."&amp;orderby=date&amp;order=".$order.$p_link;

	$mark = $order == "ASC" ? '↑' : '↓';
	if(!isset($results))
		$links = $udb->get_rows($sql);
	else
		$links = $results;
	?>
	<tr>
		<th style="width: 15px;"><input type="checkbox" name="select-all" value="1"></th>
		<th><a href="<?php echo $link1; ?>"><?php $ucms->cout("module.links.table.header.name"); ?> <?php echo $mark; ?></a></th>
		<th><?php $ucms->cout("module.links.table.header.url"); ?></th>
		<th><?php $ucms->cout("module.links.table.header.description"); ?></th>
		<th><a href="<?php echo $link2; ?>"><?php $ucms->cout("module.links.table.header.author"); ?> <?php echo $mark; ?></a></th>
		<th><?php $ucms->cout("module.links.table.header.status"); ?></th>
		<th><a href="<?php echo $link3; ?>"><?php $ucms->cout("module.links.table.header.date"); ?> <?php echo $mark; ?></a></th>
		<th style="width: 115px;"><?php $ucms->cout("module.links.table.header.manage"); ?></th>
	</tr>
	<?php	
	if($links){ 
 		for ($i = 0; $i < count($links); $i++){
			if($links[$i]['author'] == $user->get_user_id()){
				$accessLVL = 2;
			}elseif($links[$i]['author_group'] == ADMINISTRATOR_GROUP_ID){
				$accessLVL = 6;
			}else{
				$accessLVL = 4;
			}
			$status = $links[$i]['publish'] == 1 ? $ucms->cout("module.links.table.status.published", true) : $ucms->cout("module.links.table.status.hidden", true);
			$link = NICE_LINKS ? SITE_DOMAIN.UCMS_DIR.'/redirect/'.$links[$i]['url'] : SITE_DOMAIN.UCMS_DIR.'/?action=redirect&amp;url='.$links[$i]['url'];
			echo "<tr>";
			echo "<td><input type=\"checkbox\" name=\"item[]\" value=\"".$links[$i]['id']."\"></td>";
			echo "<td>".$links[$i]['name']."</td>";
			echo "<td><a target=\"".$links[$i]['target']."\" rel=\"external\" href=\"".$link."\">".$links[$i]['url']."</a></td>";
			echo "<td>".$links[$i]['description']."</td>";
			echo "<td><b>".((int) $links[$i]['author'] > 0 
				? '<a href="'.UCMS_DIR.'/admin/manage.php?module=users&action=update&id='.$links[$i]['author'].'">'
				.(!empty($links[$i]['author_nickname']) ? $links[$i]['author_nickname'] : $links[$i]['author_login']).'</a>' : $links[$i]['author'])."</b></td>";
			echo "<td>".$status."</td>";
			echo "<td>".$ucms->date_format($links[$i]['date'])."</td>";
			echo "<td>".($user->has_access("links", $accessLVL) ?
			"<a href=\"manage.php?module=links&amp;action=update&amp;id=".$links[$i]['id']."\">".$ucms->cout("module.links.table.manage.edit", true)."</a>" : "")
			.($user->has_access("links", $accessLVL+1) ?
			" | <a href=\"".htmlspecialchars(get_current_url("action", "alert", "id"))."&amp;action=delete&amp;id=".$links[$i]['id']."\">".$ucms->cout("module.links.table.manage.delete", true)."</a>" : "")."</td>";
			echo "</tr>";
		} 
	}else{
		?>
		<tr><td colspan="8" style="text-align:center;"><?php $ucms->cout("module.links.table.no_pages.label"); ?></td></tr>
		<?php
	}
	?>
	</table></form>
	<?php
}

function add_link_form(){
	global $user, $ucms;
	$ucms->template(get_module('path', 'links').'forms/add-link.php', false);
}

function update_link_form($id){
	global $user, $udb, $ucms;
	$id = (int) $id;
	$ucms->template(get_module('path', 'links').'forms/update-link.php', false, $id);
}


function add_link($p){
	global $udb, $user, $event, $ucms;
	$title = $udb->parse_value($p['title']);
	$description = $udb->parse_value($p['body']);
	$author = $user->get_user_id($udb->parse_value($p['author']));
	if(!$author) $author = $udb->parse_value($p['author']);
	$publish = isset($p['publish']) ? 1 : 0;
	$target = $udb->parse_value($p['target']);
	$url = $udb->parse_value($p['url']);
	if(!preg_match("#(http://)#", $url)){
		$url = 'http://'.$url;
	}
	if(!empty($title) and !empty($url) and !empty($description) and $author){
		$add = $udb->query("INSERT INTO `".UC_PREFIX."links` (`id`, `name`, `publish`, `url`, `description`, `author`, `target`, `date`)
			VALUES (NULL, '$title', '$publish', '$url', '$description', '$author', '$target', NOW())");
		if($add){
			$event->do_actions("link.added");
			header("Location: ".get_current_url("alert", "action", "id")."&alert=added");
		}
	}else{
		echo "<div class=\"error\">".$ucms->cout("module.links.error.empty_fields.label", true)."</div><br>";
	}
	
}

function update_link($p){
	global $udb, $user, $event;
	$id = (int) $p['id'];
	$title = $udb->parse_value($p['title']);
	$description = $udb->parse_value($p['body']);
	$author = $user->get_user_id($udb->parse_value($p['author']));
	if(!$author) $author = $udb->parse_value($p['author']);
	$publish = isset($p['publish']) ? 1 : 0;
	$target = $udb->parse_value($p['target']);
	$url = $udb->parse_value($p['url']);
	if(!preg_match("#(http://|ftp://)#", $url)){
		$url = 'http://'.$url;
	}
	if(!empty($title) and !empty($url) and !empty($description) and $author){
		$upd = $udb->query("UPDATE `".UC_PREFIX."links` SET `name` = '$title', `publish` = '$publish', `url` = '$url', `description` = '$description', `author` = '$author', `target` = '$target' WHERE `id` = '$id'");
		if($upd){
			$event->do_actions("link.updated");
			header("Location: ".get_current_url("alert", "action", "id")."&alert=updated");
		}
	}else{
		echo "<div class=\"error\">".$ucms->cout("module.links.error.empty_fields.label", true)."</div><br>";
	}
	
}

function delete_link($id){
	global $udb, $user, $event;
	$id = (int) $id;
	if(!empty($id) and $id > 0){
		$link = $udb->get_row("SELECT * FROM `".UC_PREFIX."links` WHERE `id` = '$id'");
		$userd = $udb->get_row("SELECT `id`, `group` FROM `".UC_PREFIX."users` WHERE `id` = '$link[author]' LIMIT 1");
		$accessLVL = 4;
		if($link and count($link) > 0 and $userd){
			if($userd['id'] == $user->get_user_id()){
				$accessLVL = 2;
			}elseif($userd['group'] == ADMINISTRATOR_GROUP_ID){
				$accessLVL = 6;
			}else{
				$accessLVL = 4;
			}
		}

		if($user->has_access("links", $accessLVL)){
			$del = $udb->query("DELETE FROM `".UC_PREFIX."links` WHERE `id` = '$id'");
			if($del){
				$event->do_actions("link.deleted");
				header("Location: ".get_current_url("alert", "action", "id")."&alert=deleted");
			}
		}
	}else return false;
}
?>