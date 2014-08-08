<?php
	/**
	 *
	 * Comments API and setup
	 * @package uCMS Comments
	 * @since uCMS 1.0
	 * @version uCMS 1.3
	 *
	*/
	if(isset($action) and $action == 'search' and !isset($results)) $results = array();

	if(!isset($comment_sql) and !isset($results) and (!in_url('admin') or (isset($action) and $action == 'posts'))){
		if(!$user->has_access("comments", 4)){
			$swhere = "WHERE `c`.`post` = '$id' AND `c`.`approved` > 0";
		}else $swhere = "WHERE `c`.`post` = '$id'";
		if(TREE_COMMENTS) $swhere .= " AND `c`.`parent` = '0'";
		if(COMMENTS_PAGING){
			$comments_page = get_current_page();
			$comments_count = $udb->num_rows("SELECT `id` FROM `".UC_PREFIX."comments` AS `c` $swhere");
			$comments_pages_count = ceil($comments_count / COMMENTS_ON_PAGE); 
			if ($comments_page > $comments_pages_count):
				$comments_page = $comments_pages_count;
			endif; 
			$start_pos = ($comments_page - 1) * COMMENTS_ON_PAGE;
			if($start_pos < 0) $start_pos = 0; 
			$comment_sql = "SELECT `c`.*, `u`.`login` AS `comment_author_login`, `u`.`avatar` AS `comment_author_avatar`, `u`.`group` AS `comment_author_group`,
			`uf`.`value` AS `comment_author_nickname`, `g`.`permissions` AS `comment_author_permissions` FROM `".UC_PREFIX."comments` AS `c` FORCE INDEX (PRIMARY)
			LEFT JOIN `".UC_PREFIX."users` AS `u` ON `u`.`id` = `c`.`author`
			LEFT JOIN `".UC_PREFIX."usersinfo` AS `uf` ON `uf`.`user_id` = `u`.`id` AND `uf`.`name` = 'nickname'
			LEFT JOIN `".UC_PREFIX."groups` AS `g` ON  `g`.`id` = `u`.`group`
			$swhere ORDER BY `c`.`date` ".COMMENTS_SORT." LIMIT ".$start_pos.", ".COMMENTS_ON_PAGE;
		}else{
			$comment_sql = "SELECT `c`.*, `u`.`login` AS `comment_author_login`, `u`.`avatar` AS `comment_author_avatar`, `u`.`group` AS `comment_author_group`,
			`uf`.`value` AS `comment_author_nickname`, `g`.`permissions` AS `comment_author_permissions` FROM `".UC_PREFIX."comments` AS `c` FORCE INDEX (PRIMARY)
			LEFT JOIN `".UC_PREFIX."users` AS `u` ON `u`.`id` = `c`.`author`
			LEFT JOIN `".UC_PREFIX."usersinfo` AS `uf` ON `uf`.`user_id` = `u`.`id` AND `uf`.`name` = 'nickname'
			LEFT JOIN `".UC_PREFIX."groups` AS `g` ON  `g`.`id` = `u`.`group`
			$swhere ORDER BY `c`.`date` ".COMMENTS_SORT;
		}
	}
	if(!isset($results)){
		$comments = $udb->get_rows($comment_sql);
		if(TREE_COMMENTS and $comments and count($comments) > 0 and !isset($no_childs)){
			$comments = get_comments_children($comments, 0);
		}
	}
	else
		$comments = $results;
	if($comments and count($comments) > 0){
		$comments_count = count($comments);
	}else{
		$comments_count = 0;
	}

	/**
	 *
	 * Get array of all comments children
	 * @package uCMS
	 * @since uCMS 1.3
	 * @version uCMS 1.3
	 * @return array
	 *
	*/
	function get_comments_children($comments, $root){
		global $udb;
		if(is_array($comments)){
			foreach ($comments as $comment){
				$child = $udb->get_rows("SELECT `c`.*, `u`.`login` AS `comment_author_login`, `u`.`avatar` AS `comment_author_avatar`, `u`.`group` AS `comment_author_group`,
				`uf`.`value` AS `comment_author_nickname`, `g`.`permissions` AS `comment_author_permissions` FROM `".UC_PREFIX."comments` AS `c` FORCE INDEX (PRIMARY)
				LEFT JOIN `".UC_PREFIX."users` AS `u` ON `u`.`id` = `c`.`author`
				LEFT JOIN `".UC_PREFIX."usersinfo` AS `uf` ON `uf`.`user_id` = `u`.`id` AND `uf`.`name` = 'nickname'
				LEFT JOIN `".UC_PREFIX."groups` AS `g` ON  `g`.`id` = `u`.`group`
				WHERE `parent` = '".$comment['id']."'");
				$children[] = $comment;
				if($child){
					$children = array_merge($children, get_comments_children($child, $comment['id']));
				}
			}
			return $children;
		}else{
			return array();
		}
	}

	/**
	 *
	 * Check if there are comments for current post
	 * @package uCMS
	 * @since uCMS 1.0
	 * @version uCMS 1.3
	 * @return bool
	 *
	*/
	function is_comments(){
		global $id, $user, $comments_count;
		if(isset($comments_count) and $comments_count != 0 and $user->has_access("comments", 1)){ 
			return true;
		}
		return false;
	}
	
	/**
	 *
	 * Get number of comments for current post
	 * @package uCMS
	 * @since uCMS 1.0
	 * @version uCMS 1.3
	 * @return int
	 *
	*/
	function comments_count(){
		global $comments_count;
		if(isset($comments_count))
			return $comments_count;
		return false;
	}

	/**
	 *
	 * Print or return comment id at current iterator $comment for prepared array or from $comments array
	 * @package uCMS
	 * @since uCMS 1.0
	 * @version uCMS 1.3
	 * @return string
	 *
	*/
	function comment_id($return = false, $comment = -1, $comments = ""){
		if($comment == -1){
			global $comment;
		}
		if($comments == ""){
			global $comments;
		}
		$column = !empty($comments[$comment]['cid']) ? 'cid' : 'id';
		if(!$return){
			echo $comments[$comment][$column];
		}
		else{
			return $comments[$comment][$column];
		}
	}
	
	/**
	 *
	 * Return comment id at current iterator $comment for prepared array or from $comments array
	 * @package uCMS
	 * @since uCMS 1.0
	 * @version uCMS 1.3
	 * @return string
	 *
	*/
	function get_comment_id($comment = -1, $comments = ""){
		if($comment == -1){
			global $comment;
		}
		if($comments == ""){
			global $comments;
		}
		$column = !empty($comments[$comment]['cid']) ? 'cid' : 'id';
		return $comments[$comment][$column];
	}

	/**
	 *
	 * Get prepared array for comments to current post
	 * @package uCMS
	 * @since uCMS 1.0
	 * @version uCMS 1.3
	 * @return array
	 *
	*/
	function get_comments_array(){
		global $comments;
		return $comments;
	}

	/**
	 *
	 * Return comment parent at current iterator $comment for prepared array or from $comments array
	 * @package uCMS
	 * @since uCMS 1.0
	 * @version uCMS 1.3
	 * @return string
	 *
	*/
	function get_comment_parent($comment = -1, $comments = ""){
		if($comment == -1){
			global $comment;
		}
		if($comments == ""){
			global $comments;
		}

		return $comments[$comment]['parent'];
	}

	/**
	 *
	 * Return comment rating at current iterator $comment for prepared array or from $comments array
	 * @package uCMS
	 * @since uCMS 1.0
	 * @version uCMS 1.3
	 * @return string
	 *
	*/
	function get_comment_rating($comment = -1, $comments = ""){
		if($comment == -1){
			global $comment;
		}
		if($comments == ""){
			global $comments;
		}

		return $comments[$comment]['rating'];
	}
	
	/**
	 *
	 * Print or return comment avatar at current iterator $comment for prepared array or from $comments array
	 * @package uCMS
	 * @since uCMS 1.0
	 * @version uCMS 1.3
	 * @return string
	 *
	*/
	function comment_author_avatar($return = false, $comment = -1, $comments = ""){
		if($comment == -1){
			global $comment;
		}
		if($comments == ""){
			global $comments;
		}
		global $user, $c_authors;
		if (USER_AVATARS){
			$avatar = empty($comments[$comment]['comment_author_avatar']) ? "no-avatar.jpg" : $comments[$comment]['comment_author_avatar'];
			if(!$return)
				echo UCMS_DIR."/".AVATARS_PATH.$avatar;
			else{
				return UCMS_DIR."/".AVATARS_PATH.$avatar;
			}
		}
	}
	
	/**
	 *
	 * Print or return comment text at current iterator $comment for prepared array or from $comments array
	 * @package uCMS
	 * @since uCMS 1.0
	 * @version uCMS 1.3
	 * @return string
	 *
	*/
	function comment_content($return = false, $comment = -1, $comments = ""){
		if($comment == -1){
			global $comment;
		}
		if($comments == ""){
			global $comments;
		}

		if(!$return)
			echo $comments[$comment]['comment'];
		else {
			return $comments[$comment]['comment'];
		}
	}
	
	/**
	 *
	 * Print or return comment date at current iterator $comment for prepared array or from $comments array
	 * @package uCMS
	 * @since uCMS 1.0
	 * @version uCMS 1.3
	 * @return string
	 *
	*/
	function comment_date($return = false, $comment = -1, $comments = ""){
		if($comment == -1){
			global $comment;
		}
		if($comments == ""){
			global $comments;
		}
		global $ucms;
		$column = !empty($comments[$comment]['cdate']) ? 'cdate' : 'date';
		$date = $ucms->date_format($comments[$comment][$column]);
		if(!$return)
			echo $date;
		else
			return $date;
	}

	/**
	 *
	 * Print or return link to the comment author's profile at current iterator $comment for prepared array or from $comments array
	 * @package uCMS
	 * @since uCMS 1.0
	 * @version uCMS 1.3
	 * @return string
	 *
	*/
	function comment_author_link($return = false, $comment = -1, $comments = ""){
		if($comment == -1){
			global $comment;
		}
		if($comments == ""){
			global $comments;
		} 
		global $user;
		$column = !empty($comments[$comment]['cauthor']) ? 'cauthor' : 'author';
		$author = $comments[$comment]['comment_author_login'];
		if(empty($author)) return false; 
		$link = NICE_LINKS ? UCMS_DIR."/user/$author" : UCMS_DIR."/?action=profile&amp;id=".$comments[$comment][$column];
		if(!$return)
			echo $link;
		else
			return $link;
	}
	
	/**
	 *
	 * Print or return name of the comment author at current iterator $comment for prepared array or from $comments array
	 * @package uCMS
	 * @since uCMS 1.0
	 * @version uCMS 1.3
	 * @return string
	 *
	*/
	function comment_author_plain($return = false, $comment = -1, $comments = ""){
		if($comment == -1){
			global $comment;
		}
		if($comments == ""){
			global $comments;
		}
		global $user, $group, $ucms;
		$admin = false;
		$author = $comments[$comment]['comment_author_login'];
		$nickname = $comments[$comment]['comment_author_nickname'];
		$post_author = defined("POST_AUTHOR") ? POST_AUTHOR : "";
		if(empty($comments[$comment]['comment_author_permissions'])){
			$comments[$comment]['comment_author_permissions'] = GUEST_GROUP_PERMISSIONS;
		}
		$permissions = $group->get_permissions_array($comments[$comment]['comment_author_permissions']);
		if($permissions['comments'] >= 4) $admin = true;
		// if(empty($author)) return $ucms->cout("module.comments.deleted_user_login", $return);
		if(isset($author) and $author == $post_author or $admin){
			if($author == $post_author) $color = "#0099ff"; else $color = "blue";
			if(!$return)
				echo '<span style="color: '.$color.';">'.(!empty($nickname) ? $nickname : $author).'</span>';
			else
				return; '<span style="color: '.$color.';">'.(!empty($nickname) ? $nickname : $author).'</span>';
			$admin = false;
		}else{
			if(!$return)
				echo $author;
			else return $author;
		}	
	}

	/**
	 *
	 * Print or return HTML link to the comment author's profile at current iterator $comment for prepared array or from $comments array
	 * @package uCMS
	 * @since uCMS 1.0
	 * @version uCMS 1.3
	 * @return string
	 *
	*/
	function comment_author($return = false, $comment = -1, $comments = ""){
		if($comment == -1){
			global $comment;
		}
		if($comments == ""){
			global $comments;
		}
		global $user, $group, $ucms;
		$column = !empty($comments[$comment]['cauthor']) ? 'cauthor' : 'author';
		$admin = false;
		$post_author = defined("POST_AUTHOR") ? POST_AUTHOR : "";
		if(empty($comments[$comment]['comment_author_permissions'])){
			$comments[$comment]['comment_author_permissions'] = GUEST_GROUP_PERMISSIONS;
		}
		$permissions = $group->get_permissions_array($comments[$comment]['comment_author_permissions']);
		if($permissions['comments'] >= 4) $admin = true;
		$author = $comments[$comment]['comment_author_login'];
		$nickname = $comments[$comment]['comment_author_nickname'];
		$link = NICE_LINKS ? UCMS_DIR."/user/$author" : UCMS_DIR."/?action=profile&amp;id=".$comments[$comment][$column];
		// if(empty($author)) return $ucms->cout("module.comments.deleted_user_login", $return);
		if(isset($author) and $author == $post_author or $admin){
			if($author == $post_author) $color = "#0099ff"; else $color = "blue";
			if(!$return)
				echo '<b><a href="'.$link.'"><span style="color: '.$color.';">'.(!empty($nickname) ? $nickname : $author).'</span></a></b>';
			else
				return '<b><a href="'.$link.'"><span style="color: '.$color.';">'.(!empty($nickname) ? $nickname : $author).'</span></a></b>';
			$admin = false;
		}elseif((int) ($comments[$comment][$column]) === 0){
			if(!$return)
				echo '<b>'.$comments[$comment][$column].'</b>';
			else
				return '<b>'.$comments[$comment][$column].'</b>';
		}else{
			if(!$return)
				echo '<b><a href="'.$link.'">'.(!empty($nickname) ? $nickname : $author).'</a></b>';
			else
				return '<b><a href="'.$link.'">'.(!empty($nickname) ? $nickname : $author).'</a></b>';
		}	
	}
	
	/**
	 *
	 * Return comment status at current iterator $comment for prepared array or from $comments array
	 * @package uCMS
	 * @since uCMS 1.0
	 * @version uCMS 1.3
	 * @return string
	 *
	*/
	function get_approve_status($comment = -1, $comments = ""){
		if($comment == -1){
			global $comment;
		}
		if($comments == ""){
			global $comments;
		}
		global $user;
		if($user->has_access("comments", 4)){			
			return $comments[$comment]['approved'];
		}
	}
	
	/**
	 *
	 * Print or return manage tools for comment at current iterator $comment for prepared array or from $comments array
	 * @package uCMS
	 * @since uCMS 1.0
	 * @version uCMS 1.3
	 * @return string
	 *
	*/
	function comment_admin($return = false, $comment = -1, $comments = ""){
		if($comment == -1){
			global $comment;
		}
		if($comments == ""){
			global $comments;
		}
		global $user, $ucms;
		$group = empty($comments[$comment]['comment_author_group']) ? GUEST_GROUP_ID : $comments[$comment]['comment_author_group'];
		$column = !empty($comments[$comment]['cauthor']) ? 'cauthor' : 'author';
		if($user->get_user_id() == $comments[$comment][$column])
			$accessLVL = 3;
		elseif($group == 1){
			$accessLVL = 6;
		}else $accessLVL = 4;
		if($user->has_access("comments", $accessLVL)){
			if(!$return)
				echo '<p style="text-align: right;">';
			else
				$return = '<p style="text-align: right;">';
			if (get_approve_status($comment, $comments) == 0 and $accessLVL >= 3):
				if(!$return){
					$ucms->cout("module.comments.not_approved.label");
					echo '<br>';
					echo '<a href="'.UCMS_DIR.'/admin/manage.php?module=comments&amp;action=approve&amp;id='.get_comment_id($comment, $comments).'">'.$ucms->cout("module.comments.approve.button", true).'</a> |';
				}else{
					$return .= $ucms->cout("module.comments.not_approved.label", true).'<br><a href="'.UCMS_DIR.'/admin/manage.php?module=comments&amp;action=approve&amp;id='.get_comment_id($comment, $comments).'">'.$ucms->cout("module.comments.approve.button", true).'</a> |';
				}
			endif;	
				if(!$return){	
					echo ' <a href="'.UCMS_DIR.'/admin/manage.php?module=comments&amp;action=update&amp;id='.get_comment_id($comment, $comments).'" >'.$ucms->cout("module.comments.edit.button", true).'</a> | <a href="'.UCMS_DIR.'/admin/manage.php?module=comments&amp;action=delete&amp;id='.get_comment_id($comment, $comments).'" >'.$ucms->cout("module.comments.delete.button", true).'</a></p>';
				}else{
					$return .= ' <a href="'.UCMS_DIR.'/admin/manage.php?module=comments&amp;action=update&amp;id='.get_comment_id($comment, $comments).'" >'.$ucms->cout("module.comments.edit.button", true).'</a> | <a href="'.UCMS_DIR.'/admin/manage.php?module=comments&amp;action=delete&amp;id='.get_comment_id($comment, $comments).'" >'.$ucms->cout("module.comments.delete.button", true).'</a></p>';
					return $return;
				}
		}
	}
	
	/**
	 *
	 * Load comments for current post from theme's file
	 * @package uCMS
	 * @since uCMS 1.0
	 * @version uCMS 1.3
	 * @return false if comments are disabled
	 *
	*/
	function list_comments(){
		global $comment, $event, $udb, $ucms, $user, $comments_count, $comments_pages_count, $comments_page;
		if(is_comments_enabled() and COMMENTS_ENABLED){
			include THEMEPATH.'comments.php';
		}else return false;
	}


	/**
	 *
	 * Return comment post at current iterator $comment for prepared array or from $comments array
	 * @package uCMS
	 * @since uCMS 1.3
	 * @version uCMS 1.3
	 * @return string
	 *
	*/
	function get_comment_post($comment = -1, $comments = ""){
		if($comment == -1){
			global $comment;
		}
		if($comments == ""){
			global $comments;
		}
		if(!empty($comments[$comment]['title']) and $comments[$comment]['alias']){			
			return post_sef_links($comments[$comment]);
		}
	}
?>
