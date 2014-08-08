<?php
if(NICE_LINKS)
	$tag = $udb->parse_value(get_url_action_value(TAG_SEF_PREFIX, false));
else $tag = !empty($_GET['key']) ? $udb->parse_value($_GET['key']) : false;
$page = get_current_page();
$count = $udb->num_rows("SELECT `id` FROM `".UC_PREFIX."posts` WHERE `keywords` LIKE '%$tag%' AND `publish` > 0 ORDER by `date` DESC");
if($count != 0){ 
	$pages_count = ceil($count / POSTS_ON_PAGE); 
	if ($page > $pages_count):
		$page = $pages_count;
	endif; 
	$start_pos = ($page - 1) * POSTS_ON_PAGE; 
	$post_sql = "SELECT  `p`.*, `u`.`login` AS `author_login`, `u`.`group` AS `author_group`, `uf`.`value` AS `author_nickname`,
		`c`.`name` AS `category_name`, `c`.`alias` AS `category_alias` FROM `".UC_PREFIX."posts` AS `p` FORCE INDEX (PRIMARY)
		LEFT JOIN `".UC_PREFIX."users` AS `u` ON `u`.`id` = `p`.`author`
		LEFT JOIN `".UC_PREFIX."usersinfo` AS `uf` ON `uf`.`user_id` = `u`.`id` AND `uf`.`name` = 'nickname'
		LEFT JOIN `".UC_PREFIX."categories` AS `c` ON `c`.`id` = `p`.`category`
		WHERE `p`.`keywords` LIKE '%$tag%' AND `p`.`publish` > 0 ORDER BY `p`.`date` DESC LIMIT ".$start_pos.", ".POSTS_ON_PAGE;
}else{
	$post_sql = "SELECT * FROM `".UC_PREFIX."posts` WHERE `id` = 0";
}
$tag = htmlspecialchars($tag);
add_title($action, 'module.posts.tag.title', array($tag));
require_once POSTS_MODULE_PATH.'posts.php';
require $theme->get_path().'index.php';
?>