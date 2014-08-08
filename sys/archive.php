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

if($count != 0){ 
	$pages_count = ceil($count / POSTS_ON_PAGE); 
	if ($page > $pages_count):
		$page = $pages_count;
	endif; 
	$start_pos = ($page - 1) * POSTS_ON_PAGE; 
	$post_sql = "SELECT * FROM `".UC_PREFIX."posts` WHERE year(`date`) = '$year' and month(`date`) = '$month' and `publish` > 0 ORDER BY `id` DESC LIMIT ".$start_pos.", ".POSTS_ON_PAGE;
}else{
	$post_sql = "SELECT * FROM `".UC_PREFIX."posts` WHERE `id` = 0";
}
require_once 'include/posts.php';
require_once 'include/comments.php';
require THEMEPATH.'index.php';

?>