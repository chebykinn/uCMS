<?php
/*Главный обработчик запросов*/
if ( UCMS_MAINTENANCE and !$user->has_access(5, 7) and !isset($_POST['login']) and !isset($_POST['password']) ) {
	if(file_exists(THEMEPATH."maintenance.php")){
		require THEMEPATH."maintenance.php";
	}else{
		require ERROR_TEMPLATES_PATH."maintenance.php";
	}
	exit;
}
if (!isset($_SERVER['HTTP_HOST']) || preg_replace("#(www.)#", "", $_SERVER['HTTP_HOST']) != preg_replace("#(http://)#", "", SITE_DOMAIN)) {
    header($_SERVER['SERVER_PROTOCOL'].' 400 Bad Request');
    echo "Встраивание сайта запрещено.";
    exit;
}

$url = urldecode($_SERVER['REQUEST_URI']);
$url = preg_replace("#(".UCMS_DIR.")#", '', $url);
$url_all = explode('/', $url);
$cat_select = '';

$action_array = array(
	'0' => 'search',
	'1' => 'logout',
	'2' => 'login',
	'3' => 'profile',
	'4' => 'userlist',
	'5' => 'registration',
	'6' => 'reset',
	'7' => 'activation',
	'8' => 'archive',
	'9' => 'redirect',
	'10' => PAGE_SEF_PREFIX,
	'11' => 'user',
	'12' => 'users',
	'13' => 'rss');

if(NICE_LINKS){
	$action = $url_all[1];
	if(isset($_GET['action']) and $_GET['action'] == 'search') 
		$action = 'search';
}else{
	if(isset($_GET['action']))
		$action = trim($_GET['action']);
	else $action = '';
}

if(!empty($action)){
	if(in_array($action, $action_array)){
		switch ($action) {
			case 'user':
				$action = 'profile';
				$dir = '';
			break;

			case 'users':
				$action = 'userlist';
				$dir = '';
			break;

			case 'redirect':
				$dir = 'include/';
			break;
			
			case PAGE_SEF_PREFIX:
				$dir = '';
				$action = 'pages';
			break;

			default:
				$dir = '';
			break;
		}
		require "$dir$action.php";
		exit;
	}
}

if(!NICE_LINKS){
	if(isset($_GET['p']) and $_GET['p'] > 0){
		$action = 'pages';
		require 'pages.php';
		exit;
	}else if(isset($_GET['id']) and $_GET['id'] > 0){
		$action = 'posts';
		$id = (int) $_GET['id'];
		$post_page = $udb->get_row("SELECT `title` FROM `".UC_PREFIX."posts` WHERE `id` = '$id' AND `publish` > 0 LIMIT 1");
		if(!$post_page){
			$ucms->panic(404);
		}
	}else if(isset($_GET['page']) and $_GET['page'] > 0){
		$action = 'page';
		$page = (int) $_GET['page'];
	}
	if(isset($_GET['category'])){
		$category = (int) $_GET['category'];
		$cat_select = "AND `category` = '$category'";
		$cat = $udb->get_row("SELECT `name` FROM `".UC_PREFIX."categories` WHERE `id` = '$category' AND `posts` > 0");
		if($cat){
			$action = 'category';
		}
	}
	if(!isset($_GET) or empty($_GET)){
		if(preg_match("/[a-zA-Zа-яА-Я0-9+]/", $url)){
			$ucms->panic(404);
		}
	}
}else{
	if(in_array('page', $url_all)){
		$action = 'page';
		$page = array_search("page", $url_all);
		$page = $url_all[$page+1];
		if($page <= 0) $page = 1;
	}
	if(in_array(CATEGORY_SEF_PREFIX, $url_all)){
		$category = $udb->parse_value($url_all[2]);
		$cat = $udb->get_row("SELECT `id`,`name` FROM `".UC_PREFIX."categories` WHERE `alias` = '$category' AND `posts` > 0");
		if(!empty($cat['id'])){
			$category = $cat['id'];
		}
		$cat_select = "AND `category` = '$category'";
	}elseif(!empty($action) and !in_array($action, $action_array) and $action != 'page'){
		$post_alias = $udb->parse_value($url);
		$sources = explode("/", POST_SEF_LINK);
		$matches = explode("/", substr($post_alias, 1));
		$i = 0;
		foreach($matches as $match){ 
			if(!empty($match) and isset($sources[$i])){
				if($match != $sources[$i]){
					if(preg_match("/@/", $sources[$i])){
						$match = $udb->parse_value($match);
						$post_page = $udb->get_row("SELECT `id`,`title` FROM `".UC_PREFIX."posts` WHERE `alias` = '$match' or `id` = '$match' or `title` = '$match' AND `publish` > 0 LIMIT 1");
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
			if(PAGE_SEF_PREFIX == ""){
				require "pages.php";
				exit;
			}else{
				$ucms->panic(404);
			}
		}else{
			$action = 'posts';
			$id = $post_page['id'];
		}
	}
}
if(!isset($id)) $id = 0;
if(!isset($page)) $page = 1;
$count = $udb->num_rows("SELECT `id` FROM `".UC_PREFIX."posts` WHERE `publish` > 0 $cat_select");
if($count != 0){ 
	$pages_count = ceil($count / POSTS_ON_PAGE); 
	if ($page > $pages_count):
		$page = $pages_count;
	endif; 
	$start_pos = ($page - 1) * POSTS_ON_PAGE; 
	$post_sql = "SELECT * FROM `".UC_PREFIX."posts` WHERE `publish` > 0 $cat_select ORDER BY `date` DESC, `id` DESC LIMIT ".$start_pos.", ".POSTS_ON_PAGE;
}else{
	$post_sql = "SELECT * FROM `".UC_PREFIX."posts` WHERE `id` = 0";
}
if(PAGES_MODULE)
	require_once 'include/pages.php';
if(POSTS_MODULE)
	require_once 'include/posts.php';
if(COMMENTS_MODULE){
	require_once 'admin/manage-comments.php';
	require_once 'include/comments.php';
}
require THEMEPATH.'index.php';
$udb->db_disconnect($con);

function title(){
		global $url_all, $udb, $page, $user, $action;
		if($user->has_access(6, 0)){
			echo "У вас нет доступа к сайту, вы забанены. - ".SITE_TITLE;
		}else{
			if(UCMS_DEBUG) echo "[DEBUG] ";
			//var_dump($action);
			switch ($action) {
				case 'search':
					echo 'Поиск - "'.htmlspecialchars(get_query()).'"';
				break;
				
				case 'login':
					echo 'Вход';
				break;

				case 'profile':
					global $profile_login;
					if(isset($profile_login))
						echo 'Пользователь '.$profile_login;
					else echo 'Пользователя не существует';
					global $post_sql, $comment_sql;
					if(isset($post_sql))
						echo " / Посты";
					if(isset($comment_sql))
						echo " / Комментарии";
				break;

				case 'userlist':
					echo 'Список пользователей';
				break;

				case 'registration':
					echo 'Регистрация';
				break;

				case 'reset':
					echo 'Восстановление пароля';
				break;

				case 'pages':
					global $page_page;
					if(isset($page_page['title']))
						echo $page_page['title'];
				break;

				case 'posts':
					global $post_page;
					if(isset($post_page['title']))
						echo $post_page['title'];
				break;

				case 'archive':
					global $month, $year, $months;
					if($month < 10) $month = '0'.$month;
					if($month and $year > 0){
						if(isset($months[$month])){
							echo 'Архив постов за '.mb_strtolower($months[$month], "UTF-8")." ".$year;
						}else echo 'Архив постов за ничего' ;
					}else echo 'Архив постов за ничего' ;
				break;

				case 'category':
					global $cat;
					if(isset($cat['name']))
						echo 'Посты в категории "'.$cat['name'].'"';
					else echo 'Такой категории нет';
				break;

				case 'error404':
					echo '404 страница не найдена';
				break;

				default:
					echo SITE_TITLE;
				break;
			}
			if($action == 'page')
				echo ' - Страница '.$page;
			else if($action != '')
				echo " - ".SITE_TITLE;
		}
	}

	function rss_link(){
		echo "<link href=\"".UCMS_DIR.(NICE_LINKS ? "/rss" : "/?action=rss")."\" rel=\"alternate\" type=\"application/rss+xml\" title=\"Лента постов - ".SITE_TITLE."\">\n";
	}
?>