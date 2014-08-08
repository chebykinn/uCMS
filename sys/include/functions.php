<?php
/**
 *
 * uCMS additional functions.
 * @package uCMS
 * @since 1.3
 * @version uCMS 1.3
 *
*/

/**
 *
 * Default page links function
 * @package uCMS
 * @since uCMS 1.0
 * @version uCMS 1.3
 * @return nothing
 *
*/
function pages($page, $count, $pages_count, $show_link, $sef = NICE_LINKS){
	global $id;
	if ($pages_count == 1) return false;
	$link = $_SERVER['REQUEST_URI'];
	if($sef){
		global $url_all;
		if(in_array("page", $url_all)){	
			$link = preg_replace("#(/page/(\d+))#", "", $link);
		}
		if(substr($link, -1) != "/")
			$alias = "/page/";
		else $alias = "page/";
	}else{
		if(isset($_GET['page'])){
			$link = get_current_url('page');
		}
		if(!empty($_GET) and !(count($_GET) == 1 and isset($_GET['page'])))  $link .= '&amp;';
		else $link .= '?';
		$alias = 'page=';
	}
	$separator = ' ';
	$style = 'class="pages"';
	$begin = $page - intval($show_link / 2);
	unset($show_dots);
	if ($pages_count <= $show_link + 1) $show_dots = 'no';
	if ($begin > 2 && ($pages_count - $show_link > 2)){
		echo '<a '.$style.' style="margin-right: 4px" href="'.$link.$alias.'1"> ← </a>';
		echo $separator;
	}
	for ($j = 0; $j <= $show_link; $j++){
		$i = $begin + $j;
		if ($i < 1) continue;
		if (!isset($show_dots) && $begin > 1) {
			echo '<a '.$style.' href="'.$link.$alias.($i-1).'"><b>...</b></a>';
			$show_dots = "no";
			echo $separator;
		}
		if ($i > $pages_count) break;
		if ($i == $page) {
			$style = 'class="pages-selected"';
			echo '<a '.$style.'><b>'.$i.'</b></a>';
			$style = 'class="pages"';
		} else {
			echo '<a '.$style.' href="'.$link.$alias.$i.'">'.$i.'</a>'; 
		}
		if (($i != $pages_count) && ($j != $show_link)) echo $separator;
		if (($j == $show_link) && ($i < $pages_count)) {
			echo $separator;
			echo '<a '.$style.' href="'.$link.$alias.($i+1).'"><b>...</b></a>';
			echo $separator;
		}
	}
	if ($begin + $show_link + 1 < $pages_count) {
		echo '<a '.$style.' href="'.$link.$alias.$pages_count.'"> → </a>';	
	}
	return true;
}

/**
 *
 * Another default page links function
 * @package uCMS
 * @since uCMS 1.0
 * @version uCMS 1.3
 * @return nothing
 *
*/
function pages_min($page, $count, $pages_count, $show_link, $to_left, $to_right, $to_top = '', $to_bottom = ''){
	global $id;
	if($id != 0){
		return false;
	}else{
		
		if($to_bottom != '' and $page != $pages_count){
			echo "<a href=\"?page=$pages_count\">$to_left</a> ";
		}

		if($page != $pages_count){
			echo "<a href=\"?page=".($page+1)."\">$to_left</a> ";
		}
		if($page > 1){
			echo "&nbsp;&nbsp;<a href=\"?page=".($page-1)."\">$to_right</a>";
		}

		if($to_top != '' and $page > 1){
			echo "<a href=\"?page=1\">$to_left</a> ";
		}
	}
	
}

/**
 *
 * Redirect user to given URL
 * @package uCMS
 * @since uCMS 1.1
 * @version uCMS 1.3
 * @return nothing
 *
*/
function redirect(){
	$url = $_SERVER['REQUEST_URI'];
	if(NICE_LINKS){
		$url = preg_replace(array('#(/redirect/)#', '#('.UCMS_DIR.')#'), '', $url);
	}else{
		$url = $_GET['url'];
	}
	if(preg_match('#(http?|ftp|https):/\S+[^\s.,>)\];\'\"!?]#i', $url)){
		header("Location: $url");
		exit();
	}
}

/**
 *
 * Display title for current page
 * @package uCMS
 * @since uCMS 1.0
 * @version uCMS 1.3
 * @return nothing
 *
*/
function title(){
	global $page, $user, $action, $titles, $url_all, $ucms;
	if($user->has_access("all_equal", 0) and class_exists("uSers")){
		echo $ucms->cout("title.user_banned", true).htmlspecialchars(SITE_TITLE);
	}else{
		if(UCMS_DEBUG) echo "[DEBUG] ";
		if(isset($titles[$action])){
			echo htmlspecialchars($titles[$action]);
		}
		if(in_array('page', $url_all) || $action == 'page' || isset($_GET['page'])){
			echo $ucms->cout("title.page_num", true).$page;
		}
		if($action != 'index')
			echo " — ".htmlspecialchars(SITE_TITLE);
	}
}

/**
 *
 * Display link for posts rss feed
 * @package uCMS
 * @since uCMS 1.2
 * @version uCMS 1.3
 * @return nothing
 *
*/
function rss_link(){
	global $ucms;
	echo "<link href=\"".UCMS_DIR.(NICE_LINKS ? "/rss" : "/?action=rss")."\" rel=\"alternate\" type=\"application/rss+xml\" title=\"".$ucms->cout("rss_link.title", true).SITE_TITLE."\">\n";
}

/**
 *
 * Perform default tasks when the site is loaded
 * @package uCMS
 * @since uCMS 1.3
 * @version uCMS 1.3
 * @return nothing
 *
*/
function loaded(){
	if(!is_sheduled_cron_event('check_ucms_update'))
		shedule_cron_event(time(), 'daily', 'check_ucms_update');
}

/**
 *
 * Perform default tasks when the site page is almost stopped loading 
 * @package uCMS
 * @since uCMS 1.3
 * @version uCMS 1.3
 * @return nothing
 *
*/
function shutdown(){
	global $udb, $user, $ucms, $con;
	$udb->query("UPDATE `".UC_PREFIX."users` SET `online` = '0' WHERE UNIX_TIMESTAMP() - UNIX_TIMESTAMP(lastlogin) > 3600");
	$udb->db_disconnect($con);
}

/**
 *
 * Display path to current theme
 * @package uCMS
 * @since uCMS 1.0
 * @version uCMS 1.3
 * @return nothing
 *
*/
function theme_path(){
	global $theme;
	if(is_activated_module("themes")){
		echo $theme->get_path(true);
	}
}

/**
 *
 * Check for uCMS update
 * @package uCMS
 * @since uCMS 1.3
 * @version uCMS 1.3
 * @return nothing
 *
*/
function check_ucms_update(){
	global $ucms, $user;
	$file = UCMS_SITE_URL."/pub/version";
	$file_headers = @get_headers($file);
	$strings = @file($file);
	if(!empty($strings[0])){
		if($strings[0] != UCMS_VERSION){
			$_SESSION['ucms-update-available'] = $strings[0];
		}elseif(isset($_SESSION['ucms-update-available'])){
			unset($_SESSION['ucms-update-available']);
		}
	} 
}

$event->bind_action("site.loaded", "loaded");
$event->bind_action("site.shutdown", "shutdown");

// Some useful variables

$uc_months = $ucms->get_months();
$url = urldecode($_SERVER['REQUEST_URI']); // Site url
$url = preg_replace("#(".UCMS_DIR.")#", '', $url);
$url_all = explode('/', $url); // Url array
?>