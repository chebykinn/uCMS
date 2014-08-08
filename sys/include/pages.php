<?php 

	function page(){
		global $page_id, $user;
		if ($page_id != '' and $user->has_access(3, 1)){
			return true;
		}
		return false;	
	}

	function page_id(){
		global $page_id;
		if ($page_id != ''){
			echo $page_id;
		}
	}
		
	function page_title(){
		global $id_page;
		echo $id_page['title'];
		if($id_page['publish'] < 1) echo " (Черновик)";
	}

	function page_content(){
		global $id_page;
		echo $id_page['body'];
	}

	function page_author($plain = false){
		global $id_page, $user;
		if($plain or (int) $id_page['author'] == 0){
			echo $id_page['author'];
		}else{
			if($id_page['author'] != $user->get_user_id())
				$user_login = $user->get_user_login($id_page['author']);
			else $user_login = $user->get_user_login();
			$link = NICE_LINKS ? UCMS_DIR."/user/$user_login" : UCMS_DIR."/?action=profile&amp;id=$id_page[author]";
			echo "<a href=\"$link\">$user_login</a>";
		}
	}

	function page_date(){
		global $id_page;
		echo $id_page['date'];
	}

	function page_admin(){
		global $page_id, $user, $id_page;
		if($user->has_access(1, 2)){
			if($id_page['author'] == $user->get_user_id())
				$accessLVL = 2;
			elseif($user->get_user_group($id_page['author']) == 1){
				$accessLVL = 6;
			}else
				$accessLVL = 4;
			if ($page_id != '' and $user->has_access(3, $accessLVL)){
				echo '<a href="'.UCMS_DIR.'/admin/pages.php?action=update&amp;id='.$page_id.'" >Изменить</a>';
			}
		}
	}
?>