<?php
$id = 0;
if(NICE_LINKS){
	$action2 = isset($url_all[4]) ? $url_all[4] : '';
	if(!empty($action2)){
		if($action2 == 'page'){
			$page = isset($url_all[5]) ? (int) $url_all[5] : 1;
			if($page <= 0) $page = 1;
		}
	}else{
		$page = 1;
	}
}else
	$page = (isset($_GET['page']) and $_GET['page'] > 0) ? (int) $_GET['page'] : 1;
	if(NICE_LINKS){
		$month = isset($url_all[3]) ? (int) $url_all[3] : 0;
		$year = isset($url_all[2]) ? (int) $url_all[2] : 0;
	}else{
		$month = isset($_GET['m']) ? (int) $_GET['m'] : 0;
		$year = isset($_GET['y']) ? (int) $_GET['y'] : 0;
	}
	
if($month and $year > 0){
	$count = $udb->num_rows("SELECT `id` FROM `".UC_PREFIX."posts` WHERE year(`date`) = '$year' and month(`date`) = '$month' and `publish` > 0");	
}else $count = 0;

if($month < 10) $month = '0'.$month;
if($month and $year > 0){
	if(isset($uc_months[$month])){
		$month_name = $uc_months[$month];
		add_title($action, 'module.posts.archive.title', array($uc_months[$month], $year));
	}else add_title($action, 'module.posts.no_archive.title');
}else add_title($action, 'module.posts.no_archive.title');

if($count != 0){ 
	
	$pages_count = ceil($count / POSTS_ON_PAGE); 
	if ($page > $pages_count):
		$page = $pages_count;
	endif; 
	$start_pos = ($page - 1) * POSTS_ON_PAGE; 
	$post_sql = "SELECT `p`.*, `u`.`login` AS `author_login`, `u`.`group` AS `author_group`, `uf`.`value` AS `author_nickname`,
	`c`.`name` AS `category_name`, `c`.`alias` AS `category_alias` FROM `".UC_PREFIX."posts` AS `p` FORCE INDEX (PRIMARY)
		LEFT JOIN `".UC_PREFIX."users` AS `u` ON `u`.`id` = `p`.`author`
		LEFT JOIN `".UC_PREFIX."usersinfo` AS `uf` ON `uf`.`user_id` = `u`.`id` AND `uf`.`name` = 'nickname'
		LEFT JOIN `".UC_PREFIX."categories` AS `c` ON `c`.`id` = `p`.`category`
		WHERE year(`p`.`date`) = '$year' and month(`p`.`date`) = '$month' and `p`.`publish` > 0 ORDER BY `p`.`date` DESC LIMIT ".$start_pos.", ".POSTS_ON_PAGE;
}else{
	$post_sql = "SELECT * FROM `".UC_PREFIX."posts` WHERE `id` = 0";
}
require_once POSTS_MODULE_PATH.'posts.php';
require $theme->get_path().'index.php';

?>