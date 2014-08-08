<?php
$id = isset($id) ? $id : 0;
	if($id > 0){
		if(!isset($comment_sql)){
			if($user->has_access(2, 4)){
			$comment_sql = "SELECT * FROM `".UC_PREFIX."comments` WHERE `post` = '$id' ORDER BY `date` ASC";
			}else{
				$comment_sql = "SELECT * FROM `".UC_PREFIX."comments` WHERE `post` = '$id' and `approved` = 1 ORDER BY `date` ASC";
			}
		}
		$comments = $udb->get_rows($comment_sql);

		if($comments){
			$comments_count = count($comments);
			
			if($comments_count != 0){ 
				for($i = 0; $i < count($comments); $i++){
					$c_ids[] = $comments[$i]['id'];
					$c_bodies[] = $comments[$i]['comment'];
					$c_authors[] = $udb->parse_value($comments[$i]['author']);
					$c_dates[] = $comments[$i]['date'];
					$c_approves[] = $comments[$i]['approved'];
				}
				$c_authors2 = implode("','", $c_authors);
				$c_authors2 = "'".$c_authors2."'";
				$c_logins = $udb->get_rows("SELECT `id`, `group`, `login`, `avatar` FROM `".UC_PREFIX."users` WHERE `id` in ($c_authors2) ");
			}
		}else{
			$comments_count = 0;
		}
	}

	function is_comments(){
		global $id, $user;
		if (isset($id)){
			global $comments_count;
			if($comments_count != 0 and $user->has_access(2, 1)){ 
				return true;
			}
			return false;	
		}
	}
		
	function comments_count(){
		global $id;
		if (isset($id)){
			global $comments_count;
			return $comments_count;	
		}
	}

	
	function comment_id(){
		global $comment, $id;
		if (isset($id)){
			global $c_ids;
			echo $c_ids[$comment];
		}
	}
	
	function get_comment_id(){
		global $comment;
		global $id;
		if (isset($id)){
			global $c_ids;
			return $c_ids[$comment];
		}
	}
	
	function comment_author_avatar(){
		global $comment, $id, $user, $c_authors, $c_logins;
		if (isset($id) and USER_AVATARS){
			if((int) ($c_authors[$comment]) === 0)
				echo UCMS_DIR."/".AVATARS_PATH."no-avatar.jpg";
			else{
				for($i = 0; $i < count($c_logins); $i++){
					if($c_authors[$comment] === $c_logins[$i]['id']){
						$avatar = $c_logins[$i]['avatar'];
						break;
					}
				}
				echo UCMS_DIR."/".AVATARS_PATH.$avatar;
			}
		}
	}
	
	function comment_content(){
		global $comment;
		global $id;
		if (isset($id)){
			global $c_bodies;
			echo $c_bodies[$comment];
		}
	}
	
	function comment_date(){
		global $comment, $id, $ucms;
		if (isset($id)){
			global $c_dates;
			$date = $ucms->format_date($c_dates[$comment]);
			echo $date;
		}
	}
	function comment_author_link(){
		global $comment, $id, $user, $c_authors, $c_logins;
		if (isset($id)){
			for($i = 0; $i < count($c_logins); $i++){
				if($c_authors[$comment] === $c_logins[$i]['id']){
					$author = $c_logins[$i]['login'];
					break;
				}
			}
			if(isset($author) and $author){
				$link = NICE_LINKS ? UCMS_DIR."/user/$author" : UCMS_DIR."/?action=profile&amp;id=".$c_authors[$comment];
				echo $link;
			}else return false;
		}
	}
	
	function comment_author_plain(){
		global $comment, $user, $id;
		if (isset($id)){
			global $c_authors, $c_logins;
			$admin = false;
			for($i = 0; $i < count($c_logins); $i++){
				if($c_authors[$comment] === $c_logins[$i]['id']){
					$author = $c_logins[$i]['login'];
					if($c_logins[$i]['group'] == 1) $admin = true;
					break;
				}
			}
			if(isset($author) and $author == 'IVaN4B' or $admin){
				echo '<span style="color: blue;">'.$author.'</span>';
				$admin = false;
			}elseif((int) ($c_authors[$comment]) === 0){
				echo $c_authors[$comment];
			}else{
				echo $author;
			}
			
		}
	}

	function comment_author(){
		global $comment, $user, $id;
		if (isset($id)){
			global $c_authors, $c_logins;
			$admin = false;
			for($i = 0; $i < count($c_logins); $i++){
				if($c_authors[$comment] === $c_logins[$i]['id']){
					$author = $c_logins[$i]['login'];
					if($c_logins[$i]['group'] == 1) $admin = true;
					break;
				}
			}
			if(isset($author))
				$link = NICE_LINKS ? UCMS_DIR."/user/$author" : UCMS_DIR."/?action=profile&amp;id=".$c_authors[$comment];
			else $link = "";
			if(isset($author) and $author == 'IVaN4B' or $admin){

				echo '<b><a href="'.$link.'"><span style="color: blue;">'.$author.'</span></a></b>';
				$admin = false;
			}elseif((int) ($c_authors[$comment]) === 0){
				echo '<b>'.$c_authors[$comment].'</b>';
			}else{
				echo '<b><a href="'.$link.'">'.$author.'</a></b>';
			}
			
		}
	}
	
	function get_approve_status(){
		global $comment, $id, $c_approves, $user;
		if (isset($id)){
			if($user->has_access(2, 4)){			
				return $c_approves[$comment];
			}
		}
	}
	
	function comment_admin(){
		global $comment, $id, $user, $c_authors, $c_logins;
		if (isset($id)){
			for($i = 0; $i < count($c_logins); $i++){
				if($c_authors[$comment] === $c_logins[$i]['id']){
					$group = $c_logins[$i]['group'];
					break;
				}
			}
			if(!isset($group)) $group = $user->get_user_group($c_authors[$comment]);
			if($user->get_user_id() == $c_authors[$comment])
				$accessLVL = 3;
			elseif($group == 1){
				$accessLVL = 6;
			}else $accessLVL = 4;
			if($user->has_access(2, $accessLVL)){
				echo '<p style="text-align: right;">';
				if (get_approve_status() == 0 and $accessLVL > 3):
					echo 'Не одобрен<br>';
					echo '<a href="'.UCMS_DIR.'/admin/comments.php?action=approve&amp;id='.get_comment_id().'">[ Одобрить ]</a> |'; 
				endif;		
					echo ' <a href="'.UCMS_DIR.'/admin/comments.php?action=update&amp;id='.get_comment_id().'" >[ Изменить ]</a> | <a href="admin/comments.php?action=delete&amp;id='.get_comment_id().'" >[ Удалить ]</a></p>';
			}
		}
	}
	
	function list_comments(){
		global $comment;
		if(is_comments_enabled()){
			if(COMMENTS_MODULE) include THEMEPATH.'comments.php';
			else return false;
		}
	}
?>
