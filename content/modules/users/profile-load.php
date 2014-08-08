<?php
$user_profile_page = false;
$user_messages_page = false;
$user_edit_page = false;
$user_posts_page = false;
$user_comments_page = false;
if(NICE_LINKS){
	if(is_url_key(2)){
		$sef_value = $udb->parse_value(get_url_key_value(2, 0));
	}else{
		header("Location: ".UCMS_DIR."/user/".$user->get_user_login());
		exit;
	} 
	$row = $udb->get_row("SELECT `id` FROM `".UC_PREFIX."users` WHERE `login` = '".$sef_value."' LIMIT 1");
	if ($row and $user->has_access("users", 1)){
		$ID_page = (int) $row['id'];
	}
	if (isset($ID_page)){
		$user_id = $ID_page;
	}else{
		header("Location: ".UCMS_DIR."/users");
	} 
	if(in_url('posts')){
		$page = (int) get_url_action_value('page', 1);
		$user_id = $user->get_user_id(get_url_key_value(2, false));
		$user_posts_page = true;
	}elseif(in_url('comments')){
		$page = (int) get_url_action_value('page', 1);
		$user_id = $user->get_user_id(get_url_key_value(2, false));
		$user_comments_page = true;
	}elseif(in_url('messages')){
		$page = (int) get_url_action_value('page', 1);
		$user_id = $user->get_user_id(get_url_key_value(2, false));
		$user_messages_page = true;
	}elseif(in_url('edit')){
		$user_id = $user->get_user_id(get_url_key_value(2, false));
		$user_edit_page = true;
	}
}
else{
	if(isset($_GET['id']) and $_GET['id'] != ''){
		$user_id = (int) $_GET['id'];
	}elseif(isset($_GET['posts']) and $_GET['posts'] != ''){
		$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
		$user_id = (int) $_GET['posts'];
		$user_posts_page = true;
	}elseif(isset($_GET['comments']) and $_GET['comments'] != ''){
		$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
		$user_id = (int) $_GET['comments'];
		$user_comments_page = true;
	}elseif(isset($_GET['messages']) and $_GET['messages'] != ''){
		$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
		$user_id = (int) $_GET['messages'];
		$user_messages_page = true;
	}elseif(isset($_GET['edit'])){
		$user_id = (int) $_GET['edit'];
		$user_edit_page = true;
	}else{
		$user_id = $user->get_user_id();
		header("Location: ".UCMS_DIR."/?action=profile&id=$user_id");
		exit;
	}
}

if($user_posts_page){
	$count = $udb->num_rows("SELECT `id` FROM `".UC_PREFIX."posts` WHERE `publish` > 0 and `author` = '$user_id'");
	if($count != 0){ 
		$pages_count = ceil($count / POSTS_ON_PAGE); 
		if ($page > $pages_count):
			$page = $pages_count;
		endif; 
		$start_pos = ($page - 1) * POSTS_ON_PAGE; 
		$post_sql = "SELECT `p`.*, `u`.`login` AS `author_login`, `u`.`group` AS `author_group`, `uf`.`value` AS `author_nickname`,
		`c`.`name` AS `category_name`, `c`.`alias` AS `category_alias` FROM `".UC_PREFIX."posts` AS `p`
		LEFT JOIN `".UC_PREFIX."users` AS `u` ON `u`.`id` = `p`.`author`
		LEFT JOIN `".UC_PREFIX."usersinfo` AS `uf` ON `uf`.`user_id` = `u`.`id` AND `uf`.`name` = 'nickname'
		LEFT JOIN `".UC_PREFIX."categories` AS `c` ON `c`.`id` = `p`.`category`
		WHERE `p`.`publish` > 0 AND `p`.`author` = '$user_id' ORDER BY `p`.`id` DESC LIMIT ".$start_pos.", ".POSTS_ON_PAGE;
	}else{
		$pages_count = 0;
		$post_sql = "SELECT * FROM `".UC_PREFIX."posts` WHERE `id` = 0 ORDER BY `id` DESC";
	}
	require POSTS_MODULE_PATH.'posts.php';
}elseif($user_comments_page){
	$count = $udb->num_rows("SELECT `id` FROM `".UC_PREFIX."comments` WHERE `approved` > 0 and `author` = '$user_id'");
	if($count != 0){ 
		$pages_count = ceil($count / POSTS_ON_PAGE); 
		if ($page > $pages_count):
			$page = $pages_count;
		endif; 
		$start_pos = ($page - 1) * POSTS_ON_PAGE;
		$no_childs = true;
		$comment_sql = "SELECT `c`.*, `c`.`id` AS `cid`, `c`.`author` AS `cauthor`, `c`.`date` AS `cdate`, `u`.`avatar` AS `comment_author_avatar`,
		`u`.`login` AS `comment_author_login`, `u`.`group` AS `comment_author_group`, `uf`.`value` AS `comment_author_nickname`,
		`p`.`id`, `p`.`title`, `p`.`alias`, `p`.`date`, `p`.`category`, `p`.`author`, `uu`.`login` AS `author_login`,
		`ca`.`name` AS `category_name`, `ca`.`alias` AS `category_alias` FROM `".UC_PREFIX."comments` AS `c`
		LEFT JOIN `".UC_PREFIX."users` AS `u` ON `c`.`author` = `u`.`id` 
		LEFT JOIN `".UC_PREFIX."usersinfo` AS `uf` ON `uf`.`user_id` = `u`.`id` AND `uf`.`name` = 'nickname' 
		INNER JOIN `".UC_PREFIX."posts` AS `p` ON `p`.`id` = `c`.`post`
		LEFT JOIN `".UC_PREFIX."users` AS `uu` ON `p`.`author` = `uu`.`id` 
		LEFT JOIN `".UC_PREFIX."categories` AS `ca` ON `ca`.`id` = `p`.`category`
		WHERE `c`.`approved` > 0 and `c`.`author` = '$user_id' ORDER BY `c`.`id` DESC LIMIT ".$start_pos.", ".POSTS_ON_PAGE;
	}else{
		$pages_count = 0;
		$comment_sql = "SELECT * FROM `".UC_PREFIX."comments` WHERE `id` = 0 ORDER BY `id` DESC";
	}
	require COMMENTS_MODULE_PATH.'comments.php';
}
include 'edit.php';
$edit = new edit($user);
$profile = $user->get_profile();
if($profile){
	$profile_login = $profile[0];
	$profile_email = $profile[1];
	$profile_group_id = $profile[2];
	$profile_group = $profile[3];
	$profile_avatar = $profile[4];
	$profile_info = $profile[5];
	$profile_nickname = $user->get_user_info('nickname', $user_id, $profile_info);
	$title = "module.users.site.title.profile";
	$messages_inbox = $pm->get_inbox_messages();
	$messages_outbox = $pm->get_outbox_messages();

	if($user_posts_page){
		$title = "module.users.site.title.profile.posts";
	}
	elseif($user_comments_page){
		$title = "module.users.site.title.profile.comments";
	}
	elseif($user_messages_page){
		$title = "module.users.site.title.profile.messages";
	}
	elseif($user_edit_page){
		$title = "module.users.site.title.profile.edit";
	}
	else{
		$user_profile_page = true;
	}
	$user_title_name = !empty($profile_nickname) ? $profile_nickname : $profile_login;
	add_title($action, $title, array($user_title_name));
}
if(file_exists($theme->get_path().'profile.php'))
	require $theme->get_path().'profile.php';
else require UC_DEFAULT_THEMEPATH.'profile.php';

?>