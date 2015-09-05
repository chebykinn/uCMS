<?php
$cat_select = 'AND `c`.`hidden` != 1';
$page = get_current_page();
if(!NICE_LINKS){
	if(isset($_GET['p']) and $_GET['p'] > 0){
		$action = 'pages';
		require PAGES_MODULE_PATH.'pages-load.php';
		exit;
	}else if(isset($_GET['id']) and $_GET['id'] > 0){
		$action = 'posts';
		$id = (int) $_GET['id'];
		$post_page = $udb->get_row("SELECT `title` FROM `".UC_PREFIX."posts` WHERE `id` = '$id' AND `publish` > 0 LIMIT 1");
		add_title($action, $post_page['title']);
		if(is_activated_module('comments')){
			require_once COMMENTS_MODULE_PATH.'manage-comments.php';
			require_once COMMENTS_MODULE_PATH.'comments.php';
		}else{
			function list_comments(){
				return false;
			}
		}
		if(!$post_page){
			$ucms->panic(404);
		}
	}
	if(isset($_GET['category'])){
		$category = (int) $_GET['category'];
		$cat_select = "AND `p`.`category` = '$category'";
		$cat = $udb->get_row("SELECT `name` FROM `".UC_PREFIX."categories` WHERE `id` = '$category' AND `posts` > 0");
		if($cat){
			$action = 'category';
			$category_name = $cat['name'];
			add_title($action, 'module.posts.category.title', array($category_name));
		}else{
			add_title($action, 'module.posts.no_category.title');
		}
	}
}else{
	$is_post_page = false;
	if(POSTS_THEME_FILE != 'index' and is_url_key(2)){
		$is_post_page = true;
	}
	if(in_url(CATEGORY_SEF_PREFIX)){
		$category = $udb->parse_value(get_url_action_value(CATEGORY_SEF_PREFIX, 0));
		$cat = $udb->get_row("SELECT `id`,`name` FROM `".UC_PREFIX."categories` WHERE `alias` = '$category' AND `posts` > 0");
		if(!empty($cat['id'])){
			$action = 'category';
			$category = $cat['id'];
			$category_name = $cat['name'];
			add_title($action, 'module.posts.category.title', array($category_name));
		}else{
			add_title($action, 'module.posts.no_category.title');
			$category_name = '';
		}
		$cat_select = "AND `p`.`category` = '$category'";
	}elseif(!empty($action) and ($is_post_page || $action == 'other' || !in_array($action, $url_actions))){
		$post_alias = $udb->parse_value(urldecode($url));
		$post_alias = mb_substr($post_alias, 1, mb_strlen($post_alias, "UTF-8"), "UTF-8");
		$sources = explode("/", POST_SEF_LINK);
		$matches = explode("/", $post_alias);
		$i = 0;

		foreach($matches as $match){ 
			if(!empty($match) and isset($sources[$i])){
				if($match != $sources[$i]){
					if(preg_match("/@/", $sources[$i])){
						$match = $udb->parse_value($match);
						$id_check = is_int($match) ? "or `id` = '$match'" : "";
						$post_page = $udb->get_row("SELECT `id`,`title` FROM `".UC_PREFIX."posts` WHERE `alias` = '$match'$id_check or `title` = '$match' AND `publish` > 0 LIMIT 1");
						if($post_page){ 
							if(isset($sources[$i+1])){
								if(preg_match("/@/", $sources[$i+1])){
									unset($post_page);
								}else break;
							}else
								break;
						}else{
							if(isset($sources[$i+1])){
								if(!preg_match("/@/", $sources[$i+1]))
									break;
							}else break;
						}
					}else break;
				}
				
			}
			$i++;
		}
		if(!isset($post_page)) $post_page = false;
		if(!$post_page){
			if(PAGE_SEF_PREFIX == "" and !get_url_action_value('page', false)){
				require PAGES_MODULE_PATH.'pages-load.php';
				exit;
			}else{
				if(in_url('page')){
					$action = POSTS_THEME_FILE;
				}else{
					$ucms->panic(404);
				}
			}
		}else{
			$action = 'posts';
			$id = $post_page['id'];
			add_title($action, $post_page['title']);
			if(is_activated_module('comments')){
				require_once COMMENTS_MODULE_PATH.'manage-comments.php';
				require_once COMMENTS_MODULE_PATH.'comments.php';
			}else{
				function list_comments(){
					return false;
				}
			}
		}
	}
}

if($action == 'other'){
	$ucms->panic(404);
}
if(!isset($id)) $id = 0;
if(empty($page)) $page = 1;
// if($id !== 0){
	$count = $udb->num_rows("SELECT `p`.`id`  FROM `".UC_PREFIX."posts` AS `p` 
					LEFT JOIN `".UC_PREFIX."categories` AS `c` ON `c`.`id` = `p`.`category` WHERE `p`.`publish` > 0 $cat_select");
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
		WHERE `p`.`publish` > 0 $cat_select ORDER BY `p`.`date` DESC, `p`.`id` DESC LIMIT ".$start_pos.", ".POSTS_ON_PAGE;
	}else{
		$post_sql = "SELECT * FROM `".UC_PREFIX."posts` WHERE `id` = 0";
	}
// }
if(is_activated_module('pages'))
	require_once PAGES_MODULE_PATH.'pages.php';
//if(POSTS_MODULE)
	require_once POSTS_MODULE_PATH.'posts.php';

if($action != 'index'){
	add_title(POSTS_THEME_FILE, POSTS_LIST_TITLE);
}

require $theme->get_path().POSTS_THEME_FILE.'.php';
?>