<?php
if(NICE_LINKS){
	$sef_id = $_SERVER['REQUEST_URI'];
	$array_url = preg_split ("/(\/|\..*$)/", $sef_id,1, PREG_SPLIT_NO_EMPTY);
	if (!$array_url) {
		$ID_page = 1;
	}else{
		if(isset($url_all[2]) and $url_all[2] != ''){
			$sef_value = $url_all[2];
		}else $sef_value = 0;
		$row = $udb->get_row("SELECT `id` FROM `".UC_PREFIX."users` WHERE `login` = '".$sef_value."' LIMIT 1");
		if ($row and $user->has_access(4, 1)){
			$ID_page = (int) $row['id'];
		}
	}
	if (isset($ID_page)){
		$user_id = $ID_page;
	}else{
		header("Location: ".UCMS_DIR."/users");
	} 
	if(isset($url_all[3]) and $url_all[3] == 'posts'){
		$page = isset($url_all[4]) ? (int) $url_all[5] : 1;
		$user_id = $user->get_user_id($url_all[2]);

		$count = $udb->num_rows("SELECT `id` FROM `".UC_PREFIX."posts` WHERE `publish` > 0 and `author` = '$user_id'");
		if($count != 0){ 
			$pages_count = ceil($count / POSTS_ON_PAGE); 
			if ($page > $pages_count):
				$page = $pages_count;
			endif; 
			$start_pos = ($page - 1) * POSTS_ON_PAGE; 
			$post_sql = "SELECT * FROM `".UC_PREFIX."posts` WHERE `publish` > 0 and `author` = '$user_id' ORDER BY `id` DESC LIMIT ".$start_pos.", ".POSTS_ON_PAGE;
		}else{
			$pages_count = 0;
			$post_sql = "SELECT * FROM `".UC_PREFIX."posts` WHERE `id` = 0 ORDER BY `id` DESC";
		}
		require 'include/posts.php';
	}elseif(isset($url_all[3]) and $url_all[3] == 'comments'){
		$user_id = $user->get_user_id($url_all[2]);
		$id = 1;
		$page = isset($url_all[4]) ? (int) $url_all[5] : 1;

		$count = $udb->num_rows("SELECT `id` FROM `".UC_PREFIX."comments` WHERE `approved` > 0 and `author` = '$user_id'");
		if($count != 0){ 
			$pages_count = ceil($count / POSTS_ON_PAGE); 
			if ($page > $pages_count):
				$page = $pages_count;
			endif; 
			$start_pos = ($page - 1) * POSTS_ON_PAGE; 
			$comment_sql = "SELECT * FROM `".UC_PREFIX."comments` WHERE `approved` > 0 and `author` = '$user_id' ORDER BY `id` DESC LIMIT ".$start_pos.", ".POSTS_ON_PAGE;
		}else{
			$pages_count = 0;
			$comment_sql = "SELECT * FROM `".UC_PREFIX."comments` WHERE `id` = 0 ORDER BY `id` DESC";
		}
		require 'include/comments.php';
	}
}
else{
	if(isset($_GET['id']) and $_GET['id'] != ''){
		$user_id = (int) $_GET['id'];
	}elseif(isset($_GET['posts']) and $_GET['posts'] != ''){
		$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
		$user_id = (int) $_GET['posts'];
		$count = $udb->num_rows("SELECT `id` FROM `".UC_PREFIX."posts` WHERE `publish` > 0 and `author` = '$user_id'");
		if($count != 0){ 
			$pages_count = ceil($count / POSTS_ON_PAGE); 
			if ($page > $pages_count):
				$page = $pages_count;
			endif; 
			$start_pos = ($page - 1) * POSTS_ON_PAGE; 
			$post_sql = "SELECT * FROM `".UC_PREFIX."posts` WHERE `publish` > 0 and `author` = '$user_id' ORDER BY `id` DESC LIMIT ".$start_pos.", ".POSTS_ON_PAGE;
		}else{
			$pages_count = 0;
			$post_sql = "SELECT * FROM `".UC_PREFIX."posts` WHERE `id` = 0 ORDER BY `id` DESC";
		}
		require 'include/posts.php';
	}elseif(isset($_GET['comments']) and $_GET['comments'] != ''){
		$user_id = (int) $_GET['comments'];
		$id = 1;
		$comment_sql = "SELECT * FROM `".UC_PREFIX."comments` WHERE `author` = '$user_id'";
		require 'include/comments.php';
	}else{
		header("Location:".UCMS_DIR."/?action=userlist");
	}
}
include 'sys/users/edit.php';
$edit = new edit();
$profile = $user->get_profile();
if($profile){
	$profile_login = $profile[0];
	$profile_email = $profile[1];
	$profile_group = $profile[2];
	$profile_avatar = $profile[3];
	$profile_surname = $profile[4];
	$profile_firstname = $profile[5];
	$profile_icq = $profile[6];
	$profile_skype = $profile[7];
	$profile_birthdate = $profile[8];
	$profile_addinfo = $profile[9];
}
if(file_exists(THEMEPATH.'profile.php'))
	require THEMEPATH.'profile.php';
else require UC_DEFAULT_THEMEPATH.'profile.php';
$udb->db_disconnect($con);
?>