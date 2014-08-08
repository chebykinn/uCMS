<?php 
	function add_comment($p){
		global $user, $ucms, $udb, $passed_check, $event;
		
		$comment = parse_comment($p['comment']);
		$guest = ($user->has_access("comments", 2) and !$user->logged()) ? true : false;
		$parent = $udb->parse_value($p['parent']);
		$rating = isset($p['rating']) ? $udb->parse_value($p['rating']) : 0;
		$email = "";
		if($guest){
			$author = ($user->has_access("comments", 2) and !$user->logged()) ? $udb->parse_value($p['guest-name']) : $user->get_user_id();
			$email = $udb->parse_value($p['guest-email']);
		}else{
			$author = $user->get_user_id();
			$email =  $user->get_user_email();
		}

		$ip = $user->get_user_ip();
		$post = (int) $p['post'];
		$comments_moderation = explode(",", COMMENTS_MODERATION);
		$moderation = in_array($user->get_user_group(), $comments_moderation);
		$passed_check = true;
		$event->do_actions("comment.add.check");
		if(!$passed_check) return 3;

		if(!$comment){
			return 1;
		}else{
			if(!$user->has_access("comments", 1)){
				return 2;
			}
			$approve = !$moderation ? '1' : '0';
			$udb->query("INSERT INTO `".UC_PREFIX."comments` (`id`, `post`, `comment`, `author`, `approved`, `date`, `parent`, `ip`, `email`, `rating`)
				VALUES (null, '$post', '$comment','$author', '$approve', NOW(), '$parent', '$ip', '$email', '$rating')");
			if (!$moderation){
				$comments_nums = $udb->num_rows("SELECT `id` FROM `".UC_PREFIX."comments` WHERE `approved` = '1' and `post` = $post");
				$com_count = $udb->query("UPDATE `".UC_PREFIX."posts` SET `comments` = '$comments_nums' WHERE `id` = '$post'");
			}
			$_SESSION['add-comment'] = true;
			$event->do_actions("comment.added", array($author, $comment, $guest, $email));
		}
	}		
	
	function alert_added(){
		global $user, $ucms;
		if(isset($_SESSION['add-comment']) and $_SESSION['add-comment']){
			echo '<div class="success" id="comment-added">';
			$comments_moderation = explode(",", COMMENTS_MODERATION);
			if(in_array($user->get_user_group(), $comments_moderation)){
				$ucms->cout("module.comments.alert.added.moderation");
			}else{
				$ucms->cout("module.comments.alert.added");
			}
			echo '</div>';
			unset($_SESSION['add-comment']);
		}
	}

	function add_comment_form(){
		global $id, $user, $event, $ucms;
		if (isset($id)){
			if(!$user->has_access("comments", 2)){
				if(!$user->logged() and ALLOW_REGISTRATION){
					$register_link = NICE_LINKS ? UCMS_DIR.'/registration' : UCMS_DIR.'/?action=registration';
					$ucms->cout("module.comments.register_to_add.label", false, $register_link);
				}else{
					echo '<br><div style="width: 400px;">'.$ucms->cout("module.comments.not_allowed_to_add.label", true).'</div>';
				}	
			}else{
				$back_link = NICE_LINKS ? UCMS_DIR.'/new-comment' : UCMS_DIR.'/?action=new-comment';
				$ucms->template(get_module('path','comments').'forms/add-comment.php', true, $id, $back_link);
			}
		}
	}
	
	function update_comment_form($id){
		global $udb, $user, $uc_months, $ucms, $event;
		$id = (int) $id;
		$row = $udb->get_row("SELECT * FROM `".UC_PREFIX."comments` WHERE `id` = '$id' LIMIT 1");
		if($user->get_user_id() == $row['author']){
			$accessLVL = 3;
		}elseif($user->get_user_group($row['author']) == ADMINISTRATOR_GROUP_ID){
			$accessLVL = 6;
		}else $accessLVL = 4;
		if($row and count($row) > 0){
			if($user->has_access("comments", $accessLVL)){
				$ucms->template(get_module('path','comments').'forms/update-comment.php', false, $row);
			}
		}else{
			header("Location: manage.php?module=comments");
		}
	}

	function update_comment($p){
		global $udb, $user, $event, $ucms;
		$id = (int) $p['id'];
		$author = $user->get_user_id($udb->parse_value($p['author']));
		if(!$author) $author = $udb->parse_value($p['author']);

		$day    = (!empty($p['day'])    and (int) $p['day']    > 0) ? (int) $p['day']    : date("d");
		$month  = (!empty($p['month'])  and (int) $p['month']  > 0) ? (int) $p['month']  : date("m");
		$year   = (!empty($p['year'])   and (int) $p['year']   > 0) ? (int) $p['year']   : date("Y");
		$hour   = (!empty($p['hour'])   and (int) $p['hour']   > 0) ? (int) $p['hour']   : date("H");
		$minute = (!empty($p['minute']) and (int) $p['minute'] > 0) ? (int) $p['minute'] : date("i");
		$second = (!empty($p['second']) and (int) $p['second'] > 0) ? (int) $p['second'] : date("s");
		
		$date = "$year-$month-$day $hour:$minute:$second";

		$referer = $udb->parse_value($p['referer']);
		$comment = parse_comment($p['comment']);
		$rating = isset($p['rating']) ? $udb->parse_value($p['rating']) : 0;
		$event->do_actions("comment.update.check");
		if(!$comment){
			echo '<div class="error">'.$ucms->cout("module.comments.alert.error.empty_comment", true).'<br>
			<a href="manage.php?module=comments&amp;action=update&amp;id='.$id.'">'.$ucms->cout("module.comments.alert.go_back.link", true).'</a></div>';
		}else if($user->has_access("comments", 3)){
			$sql = "UPDATE `".UC_PREFIX."comments` SET `comment` = '$comment', `approved` = '1', `author` = '$author', `date` = '$date', `rating` = '$rating' WHERE id = '$id'";
			$upd = $udb->query($sql);
			if($upd){
				$event->do_actions("comment.updated");
				if(preg_match("#(/admin/)#", $referer)){
				header("Location: ".UCMS_DIR."/admin/manage.php?module=comments&alert=updated");
			}else
				header("Location: ".$referer);
			}
			
		}
	}
	
	function delete_comment($id){
		global $ucms, $udb, $user, $event;
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
				header("Location: ".UCMS_DIR."/admin/manage.php?module=comments");
				return false;
			}

			if($user->has_access("comments", $accessLVL)){
				$sql = "SELECT * FROM `".UC_PREFIX."comments` WHERE `id` = '$id' LIMIT 1";
				$comment = $udb->get_row($sql);
				$post = $comment['post'];
				$sqc = "DELETE FROM `".UC_PREFIX."comments` WHERE `id` = '$id'";
				$del2 = $udb->query($sqc);
				$comments_nums = $udb->num_rows("SELECT `id` FROM `".UC_PREFIX."comments` WHERE `approved` = 1 and `post` = '$post'");
				$del1 = $udb->query("UPDATE `".UC_PREFIX."posts` SET `comments` = '$comments_nums' WHERE `id` = '$post'");
				if($del1 and $del2){
					fix_orphans($id);
					$event->do_actions("comment.deleted");
					if(preg_match("#(/admin/)#", $ucms->get_back_url())){

						header("Location: ".get_current_url('action', 'id')."&alert=deleted");
					}else
						header("Location: ".$ucms->get_back_url());
				}
			}else
				header("Location: ".UCMS_DIR."/admin/manage.php?module=comments");
		}
	}

	function fix_orphans($id){
		if(!$id) return false;
		global $udb;
		$id = (int) $id;
		$comment = $udb->query("UPDATE `".UC_PREFIX."comments` SET `parent` = '0' WHERE `parent` = '$id'");
		return $comment;
	}
			
	function approve_comment($id){
		global $udb, $user, $ucms, $event;
		if(!$id){
			return false;
		}
		else if($user->has_access("comments", 4)){
			$id = (int) $id;
			$sql = "SELECT `post` FROM `".UC_PREFIX."comments` WHERE id='$id'";
			$comment = $udb->get_row($sql);
			$post = $comment['post'];
			$sqa = "UPDATE `".UC_PREFIX."comments` SET `approved` = '1' WHERE id = '$id'";
			$upd = $udb->query($sqa);
			$comments_nums = $udb->num_rows("SELECT `id` FROM `".UC_PREFIX."comments` WHERE `approved` = 1 and `post` = '$post'");
			$add = $udb->query("UPDATE `".UC_PREFIX."posts` SET `comments` = '$comments_nums' WHERE `id` = '$post'");
			if($add and $upd){
				$event->do_actions("comment.approved");
				if(preg_match("#(/admin/)#", $ucms->get_back_url())){
					header("Location: ".get_current_url('action', 'id')."&alert=approved");
				}else
					header("Location: ".$ucms->get_back_url());	
			}
			
		}
	}

	function manage_comments(){
		global $ucms, $user, $udb;
		$comment_sql = '';
		if(!empty($_GET['status'])){
			if($_GET['status'] != 'approved')
				$overwrite_where = '`approved` = 0';
		}else $overwrite_where = '1';
		$safe_query = '';
		$overwrite_perpage = $perpage = 25;

		include get_module("path", "search").'search.php';

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
				if($user->has_access("comments", $accessLVL)){
					$items[] = $id;
				}
			}
			$ids = implode(',', $items);
			if (count($items) > 0) {
				switch ($action) {
					case 1:
						$upd = $udb->query("UPDATE `".UC_PREFIX."comments` SET `approved` = '1' WHERE `id` IN ($ids)");
						if (count($items) > 1) {
							header("Location: ".get_current_url('alert')."&alert=approved_multiple");
						}else 
							header("Location: ".get_current_url('alert')."&alert=approved");
					break;
	
					case 2:
						$upd = $udb->query("UPDATE `".UC_PREFIX."comments` SET `approved` = '0' WHERE `id` IN ($ids)");
						if (count($items) > 1) {
							header("Location: ".get_current_url('alert')."&alert=hidden_multiple");
						}else 
							header("Location: ".get_current_url('alert')."&alert=hidden");
					break;
	
					case 3:
						$del = $udb->query("DELETE FROM `".UC_PREFIX."comments` WHERE `id` IN ($ids)");
						if (count($items) > 1) {
							header("Location: ".get_current_url('alert')."&alert=deleted_multiple");
						}else 
							header("Location: ".get_current_url('alert')."&alert=deleted");
					break;
					
					case 4:
						foreach ($items as $com) {
							$com_parent = $udb->get_val("SELECT `parent` FROM `".UC_PREFIX."comments` WHERE `id` = '$com'");
							$parent_exists = $udb->get_row("SELECT `comment` FROM `".UC_PREFIX."comments` WHERE `id` = '$com_parent'");
							if(!$parent_exists){
								$udb->query("UPDATE `".UC_PREFIX."comments` SET `parent` = '0' WHERE `id` = '$com'");
							}
						}
						header("Location: ".get_current_url('alert')."&alert=fixed");
					break;

				}
			}
		}
			
		$user_id = $user->get_user_id();
		$columns = array('author', 'date', 'id', 'post', 'comment', 'rating', 'relevance');
		$status = isset($_GET['status']) ? htmlspecialchars($_GET['status']) : "";
		$orderby = isset($_GET['orderby']) ? in_array($_GET['orderby'], $columns) ? '`c`.`'.htmlspecialchars($_GET['orderby']).'`' : '`c`.`date`' : '`c`.`date`';
		$order = (isset($_GET['order']) and $_GET['order'] == 'DESC') ? 'ASC' : 'DESC';
		if(!isset($results)){
			$call = $udb->num_rows("SELECT * FROM `".UC_PREFIX."comments`");
			$capproved = $udb->num_rows("SELECT * FROM `".UC_PREFIX."comments` WHERE `approved` > 0");
			$cunapproved = $udb->num_rows("SELECT * FROM `".UC_PREFIX."comments` WHERE `approved` = 0");
			$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
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
			if($user->has_access("comments", 4))
				$count = $udb->num_rows("SELECT * FROM `".UC_PREFIX."comments` AS `c` $swhere ORDER BY $orderby $order");
			else $count = $udb->num_rows("SELECT * FROM `".UC_PREFIX."comments` AS `c` WHERE `c`.`author` = '$user_id' ORDER BY $orderby $order");
			$pages_count = 0;
			if($count != 0){ 
				$pages_count = ceil($count / $perpage); 
				if ($page > $pages_count):
					$page = $pages_count;
				endif; 
				$start_pos = ($page - 1) * $perpage;
				if(!$user->has_access("comments", 4)){
					$swhere = "WHERE `author` = '$user_id'";
				}
				$sql = "SELECT `c`.*, `c`.`id` AS `cid`, `c`.`author` AS `cauthor`, `c`.`date` AS `cdate`,
				`u`.`login` AS `comment_author_login`, `u`.`group` AS `comment_author_group`, `uf`.`value` AS `comment_author_nickname`,
				`p`.`id`, `p`.`title`, `p`.`alias`, `p`.`date`, `p`.`category`, `p`.`author`, `uu`.`login` AS `author_login`,
				`ca`.`name` AS `category_name`, `ca`.`alias` AS `category_alias` FROM `".UC_PREFIX."comments` AS `c` FORCE INDEX (PRIMARY)
				LEFT JOIN `".UC_PREFIX."users` AS `u` ON `c`.`author` = `u`.`id` 
				LEFT JOIN `".UC_PREFIX."usersinfo` AS `uf` ON `uf`.`user_id` = `u`.`id` AND `uf`.`name` = 'nickname' 
				INNER JOIN `".UC_PREFIX."posts` AS `p` ON `p`.`id` = `c`.`post`
				LEFT JOIN `".UC_PREFIX."users` AS `uu` ON `p`.`author` = `uu`.`id` 
				LEFT JOIN `".UC_PREFIX."categories` AS `ca` ON `ca`.`id` = `p`.`category`
				$swhere ORDER BY $orderby $order LIMIT $start_pos, $perpage";
			}else $sql  = "SELECT * FROM `".UC_PREFIX."comments` WHERE `id` = '0'";
		}else{
			$call = $status == '' ? $count : $ucms->cout("module.comments.search.label", true);
			$capproved = $status == 'approved' ? $count : $ucms->cout("module.comments.search.label", true);
			$cunapproved = $status == 'unapproved' ? $count : $ucms->cout("module.comments.search.label", true);
		}
		$s_link = "<a href=\"".UCMS_DIR."/admin/manage.php?module=comments";

		$g_link =
		 (isset($_GET['query']) ? "&amp;query=".$safe_query : "")
		.(isset($_GET['orderby']) ? "&amp;orderby=".$orderby : "")
		.(isset($_GET['order']) ? "&amp;order=".$order : "")
		.(isset($_GET['page']) ? "&amp;page=".$page : "")."\">";

		$lall = $status != '' ? $s_link.$g_link.$ucms->cout("module.comments.show.all.link", true)."</a>" : "<b>".$ucms->cout("module.comments.show.all.link", true)."</b>";
		$lapproved = $status != 'approved' ? $s_link."&amp;status=approved".$g_link.$ucms->cout("module.comments.show.approved.link", true)."</a>" : "<b>".$ucms->cout("module.comments.show.approved.link", true)."</b>";
		$lunapproved =  $status != 'unapproved' ? $s_link."&amp;status=unapproved".$g_link.$ucms->cout("module.comments.show.not_approved.link", true)."</a>" : "<b>".$ucms->cout("module.comments.show.not_approved.link", true)."</b>";
		?>
		<?php if($user->has_access("comments", 4)){ ?>
		<br>
		<?php $ucms->cout("module.comments.show.label"); echo $lall." ($call)"; ?> | <?php echo $lapproved." ($capproved)"; ?> | <?php echo $lunapproved." ($cunapproved)"; ?>
		<br><br>
		<form action="" method="post">
		<select name="actions" style="width: 250px;">
			<option><?php $ucms->cout("module.comments.selected.option"); ?></option>
			<option value="1"><?php $ucms->cout("module.comments.selected.approve.option"); ?></option>
			<option value="2"><?php $ucms->cout("module.comments.selected.hide.option"); ?></option>
			<option value="3"><?php $ucms->cout("module.comments.selected.delete.option"); ?></option>
			<option value="4"><?php $ucms->cout("module.comments.selected.repair.option"); ?></option>
		</select>
		<input type="submit" value="<?php $ucms->cout("module.comments.selected.apply.button"); ?>" class="ucms-button-submit">
		<br>
		<?php 
		}
		if($pages_count > 1){
			echo "<br>";
			pages($page, $count, $pages_count, 15, false);
			echo '<br>';
		}
		if(!isset($results)){
			$comments = $udb->get_rows($sql);
		}else{
			$comments = $results;
		}
		$link1 = UCMS_DIR."/admin/manage.php?module=comments"
		.(isset($_GET['query']) ? "&amp;query=".$safe_query : "")
		.(isset($_GET['status']) ? "&amp;status=".$status : "")
		."&amp;orderby=author&amp;order=".$order
		.(isset($_GET['page']) ? "&amp;page=".$page : "");
		$link2 = UCMS_DIR."/admin/manage.php?module=comments"
		.(isset($_GET['query']) ? "&amp;query=".$safe_query : "")
		.(isset($_GET['status']) ? "&amp;status=".$status : "").
		"&amp;orderby=date&amp;order=".$order
		.(isset($_GET['page']) ? "&amp;page=".$page : "");
		$mark = $order == "ASC" ? '↑' : '↓';
		echo '<br><table class="manage">';
		echo '<tr>
				<th style="width: 15px;"><input type="checkbox" name="select-all" value="1"></th>
				<th><a href="'.$link1.'">'.$ucms->cout("module.comments.table.header.author", true).' '.$mark.'</a></th>
				<th style="width: 30%">'.$ucms->cout("module.comments.table.header.comment", true).'</th>
				<th>'.$ucms->cout("module.comments.table.header.post", true).'</th>
				<th>'.$ucms->cout("module.comments.table.header.email", true).'</th>
				<th>'.$ucms->cout("module.comments.table.header.ip", true).'</th>
				<th><a href="'.$link2.'">'.$ucms->cout("module.comments.table.header.date", true).' '.$mark.'</a></th>
				<th>'.$ucms->cout("module.comments.table.header.status", true).'</th>
				<th style="width: 180px;">'.$ucms->cout("module.comments.table.header.manage", true).'</th>
			</tr>';
		if($comments){ 
			for($i = 0; $i < count($comments); $i++) {
				$link = NICE_LINKS ? "<a href=\"".post_sef_links($comments[$i])."#comment-".$comments[$i]['cid']."\">".htmlspecialchars($comments[$i]['title'])."</a>" : "<a href=\"".UCMS_DIR."/?id=".$comments[$i]['post']."#comment-".$comments[$i]['cid']."\">".$comments[$i]['title']."</a>";
				?>
				<tr>
				<td><input type="checkbox" name="item[]" value="<?php echo $comments[$i]['cid']; ?>"></td>
				<td><b><?php 
				if((int) ($comments[$i]['cauthor']) > 0) {
					echo '<a href="'.UCMS_DIR.'/admin/manage.php?module=users&action=update&id='.$comments[$i]['cauthor'].'">'
					.(!empty($comments[$i]['author_nickname']) ? $comments[$i]['author_nickname'] : (!empty($comments[$i]['comment_author_login']) ?
					$comments[$i]['comment_author_login'] : $ucms->cout("module.comments.deleted_user_login"))).'</a>';  
				}
				else {
					echo $comments[$i]['cauthor']; 
				} ?>
				</b></td>
				<td>
				<?php
				$limit = 100;
				$comment = $comments[$i]['comment'];
				$tags = '<p><a><pre><img><br><b><em><i><strike><span>';
				if(mb_strlen($comment, 'UTF-8') > $limit){
					echo (isset($results) ? strip_tags(mb_substr($comment, 0, $limit, 'UTF-8'), $tags) : htmlspecialchars(mb_substr($comment, 0, $limit, 'UTF-8'))).'...';
				}else{
					echo isset($results) ? strip_tags($comment, $tags) : htmlspecialchars($comment);
				}
				?>
				</td>
				<td><?php echo $link; ?></td>
				<td><?php echo $comments[$i]['email']; ?></td>
				<td><?php echo $comments[$i]['ip']; ?></td>
				<td><?php echo $ucms->date_format($comments[$i]['cdate'])?></td>
				<td><?php ($comments[$i]['approved'] == 1 ? $ucms->cout("module.comments.table.status.approved") : $ucms->cout("module.comments.table.status.not_approved")); ?></td>
				<td><span class="actions">
				<?php
				if ($comments[$i]['approved'] == 0 and $user->has_access("comments", 4)):
					echo '<a href="'.htmlspecialchars(get_current_url()).'&amp;action=approve&amp;id='.$comments[$i]['cid'].'">'.$ucms->cout("module.comments.table.manage.approve", true).'</a> | '; 
				endif;
				if($user_id == $comments[$i]['cauthor'])
					$accessLVL = 3;
				elseif($comments[$i]['comment_author_group'] == 1){
					$accessLVL = 6;
				}else $accessLVL = 4;
				if($user->has_access("comments", $accessLVL)) 
					echo '<a href="manage.php?module=comments&amp;action=update&amp;id='.$comments[$i]['cid'].'" >'.$ucms->cout("module.comments.table.manage.edit", true).'</a>
					 | <a class="delete" href="'.htmlspecialchars(get_current_url()).'&amp;action=delete&amp;id='.$comments[$i]['cid'].'" >'.$ucms->cout("module.comments.table.manage.delete", true).'</a>';
				echo '</span></td>';
				echo '</tr>';
	
			}
			echo '</table></form>';
		}else{
			?>
			<tr>
				<td colspan="9" style="text-align:center;"><?php $ucms->cout("module.comments.table.empty.label"); ?></td>
			</tr>
			</table></form>
			<?php
		}
	}

	function parse_comment($text){
		global $udb, $user;
		$text = $udb->parse_value($text);
		if($user->has_access("comments", 3)){
			$replacement = NICE_LINKS ?  UCMS_URL."redirect/http://" : UCMS_URL."?action=redirect&amp;url=http://";
			if(!preg_match("#($replacement)#", $text))
				$text = preg_replace("#(http://)#", $replacement, $text);
			$text = strip_tags($text, '<p><a><pre><img><br><b><em><i><strike><span>');	
		}else{
			$text = strip_tags($text);
		}
		return $text;	
		
	}
?>
