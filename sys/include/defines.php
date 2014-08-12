<?php
/**
 *
 * Default uCMS constants and settings
 * @package uCMS
 * @since uCMS 1.2
 * @version uCMS 1.3
 *
*/
$uc_tables = array(			// Complete list of tables for current version of uCMS
		1 => UC_PREFIX."attempts",
		2 => UC_PREFIX."categories",
		3 => UC_PREFIX."comments",
		4 => UC_PREFIX."groups",
		5 => UC_PREFIX."links",
		6 => UC_PREFIX."messages",
		7 => UC_PREFIX."pages",
		8 => UC_PREFIX."posts",
		9 => UC_PREFIX."settings",
		10 => UC_PREFIX."users",
		11 => UC_PREFIX."usersinfo");

/**
 *
 * Initializing general uCMS constants
 * @package uCMS
 * @since uCMS 1.2
 * @version uCMS 1.3
 *
*/
function init_constants(){

	if(!defined("UCMS_VERSION")){
		define("UCMS_VERSION", "1.3 Beta 9 Hotfix");						// uCMS version
	}

	if(!defined("UCMS_MIN_PHP_VERSION")){							
		define("UCMS_MIN_PHP_VERSION", 5.2);						// minimum required PHP version
	}

	if(!defined("UCMS_MIN_MYSQL_VERSION")){							
		define("UCMS_MIN_MYSQL_VERSION", 5.0);						// minimum required MySQL version
	}
	
	if(!defined("UC_DEFAULT_PATH")){
		define("UC_DEFAULT_PATH", "");
	}

	if(!defined("UC_DEFAULT_THEME_NAME")){							// Default theme name
		define("UC_DEFAULT_THEME_NAME", "μCMS");
	}

	if(!defined("UC_DEFAULT_THEME_DIR")){							// Default theme directory
		define("UC_DEFAULT_THEME_DIR", "ucms");
	}

	if (!defined("UC_SYS_PATH")){									// System directory
		define("UC_SYS_PATH", 'sys/');
	}

	if (!defined("UC_INCLUDES_PATH")){
		define("UC_INCLUDES_PATH", UC_SYS_PATH.'include/');			// Path to include directory
	}

	if (!defined("UC_CONTENT_PATH")){
		define("UC_CONTENT_PATH", 'content/');						// Path to content directory
	}

	if (!defined("UC_THEMES_PATH")){
		define("UC_THEMES_PATH", UC_CONTENT_PATH.'themes/');		// Path to themes directory
	}

	if(!defined("UC_DEFAULT_THEMEPATH")){							
		define("UC_DEFAULT_THEMEPATH", UC_THEMES_PATH."ucms/");		// Path to default theme directory
	}

	if (!defined("WIDGETS_PATH")){
		define("WIDGETS_PATH", UC_CONTENT_PATH.'widgets/');			// Path to widgets directory
	}

	if (!defined("AVATARS_PATH")){
		define("AVATARS_PATH", UC_CONTENT_PATH.'avatars/');			// Path to avatars directory
	}

	if (!defined("TEMPLATES_PATH")){
		define("TEMPLATES_PATH", UC_CONTENT_PATH.'templates/');		// Path to templates directory
	}

	if (!defined("ERROR_TEMPLATES_PATH")){
		define("ERROR_TEMPLATES_PATH", TEMPLATES_PATH.'errors/');	// Path to error templates directory
	}

	if (!defined("GENERAL_TEMPLATES_PATH")){
		define("GENERAL_TEMPLATES_PATH", TEMPLATES_PATH.'general/');// Path to general templates directory
	}
	
	/* Модули с версии @uCMS 1.3 */

	if (!defined("MODULES_PATH")){
		define("MODULES_PATH", UC_CONTENT_PATH.'modules/');			// Path to modules directory
	}

	// <Default modules>
	
	if (!defined("POSTS_MODULE_PATH")){
		define("POSTS_MODULE_PATH", MODULES_PATH."posts/");			// Path to posts module directory
	}

	if (!defined("COMMENTS_MODULE_PATH")){
		define("COMMENTS_MODULE_PATH", MODULES_PATH."comments/");	// Path to comments module directory
	}

	if (!defined("PAGES_MODULE_PATH")){
		define("PAGES_MODULE_PATH", MODULES_PATH."pages/");			// Path to pages module directory
	}

	if (!defined("USERS_MODULE_PATH")){
		define("USERS_MODULE_PATH", MODULES_PATH."users/");			// Path to users module directory
	}

	if (!defined("THEMES_MODULE_PATH")){
		define("THEMES_MODULE_PATH", MODULES_PATH."themes/");		// Path to themes module directory
	}

	if (!defined("WIDGETS_MODULE_PATH")){
		define("WIDGETS_MODULE_PATH", MODULES_PATH."widgets/");		// Path to widgets module directory
	}

	if (!defined("LINKS_MODULE_PATH")){
		define("LINKS_MODULE_PATH", MODULES_PATH."links/");			// Path to links module directory
	}

	// </Default modules>

	if (!defined("LANGUAGES_PATH")){
		define("LANGUAGES_PATH", UC_CONTENT_PATH.'languages/');		// Path to languages directory
	}

	if (!defined("PLUGINS_PATH")){
		define("PLUGINS_PATH", UC_CONTENT_PATH.'plugins/');			// Path to plugins directory
	}

	if (!defined("UPLOADS_PATH")){
		define("UPLOADS_PATH", UC_CONTENT_PATH.'uploads/');			// Path to uploaded files directory
	}

	if (!defined("MAX_IDLE_TIME")){
		define("MAX_IDLE_TIME", 3);									// Max idle time for visitor
	}

	if (!defined("SETTINGS_NUM")){
		define("SETTINGS_NUM", 68);									// Default number of settings
	}

	if (!defined("UCMS_SITE_URL")){
		define("UCMS_SITE_URL", "http://ucms.ivan4b.ru");			// URL of uCMS site for core updates
	}

	if(!defined("EXT_PARAMS_NUM")){
		define("EXT_PARAMS_NUM", 5);								// Number of settings for extentions in files like moduleinfo.txt
	}

	if (!defined("MAX_PERMISSION_LEVEL")){
		define("MAX_PERMISSION_LEVEL", 16);							// Max allowed permission level for modules to use
	}

	define('MINUTE_IN_SECONDS',	60);								// Time constants
	define('HOUR_IN_SECONDS',	60 * MINUTE_IN_SECONDS);
	define('DAY_IN_SECONDS',	24 * HOUR_IN_SECONDS);
	define('WEEK_IN_SECONDS',	 7 * DAY_IN_SECONDS);
	define('MONTH_IN_SECONDS',	 4 * WEEK_IN_SECONDS);
	define('YEAR_IN_SECONDS',  365 * DAY_IN_SECONDS);

	if ( !defined( 'UC_CRON_LOCK_TIMEOUT' ) )
		define('UC_CRON_LOCK_TIMEOUT', 60);  						// Cron lock timeout in seconds

	if(!defined('UC_CRON'))
		define("UC_CRON", true);									// Enable or disable cron

	if(!defined('EDITABLE_FILETYPES'))
		define("EDITABLE_FILETYPES", 'inode/x-empty|text/x-php|text/plain|text/x-c|application/xml|text/html'); // Supported filetypes to edit
}

/**
 *
 * Setting constants for default settings
 * @package uCMS
 * @since uCMS 1.2
 * @version uCMS 1.3
 * @return nothing
 *
*/
function define_settings(){	//настраиваемые константы
	global $setting, $user;
	for ($i = 0; $i < SETTINGS_NUM; $i++) { 
		$value = $setting[$i]['value'];
		switch ($setting[$i]['name']) {
			case 'date_format': case 'time_format':
				$value = stripcslashes($value);
			break;
		}
		$name = mb_strtoupper($setting[$i]['name'], "UTF-8");
		if (!defined("$name")){
			define("$name", $value);
		}
	}

	$p = explode("@", PAGE_SEF_LINK);
	$p = str_replace("/", "", $p[0]);
	define("PAGE_SEF_PREFIX", $p);

	if(!defined("URL_REGEXP")){
		define("URL_REGEXP", '/[^a-zA-Zа-яА-Я0-9_.-]/ui');
	}

	if (!defined("THEMEPATH")){										// Path to current theme directory
		define("THEMEPATH", UC_THEMES_PATH.THEMEDIR.'/');
	}

	if (!defined("UCMS_URL")){										// Full site URL
		define("UCMS_URL", SITE_DOMAIN.UCMS_DIR.'/');
	}

	if (!defined("DATETIME_FORMAT")){								// Datetime format string 
		define("DATETIME_FORMAT", DATE_FORMAT." ".TIME_FORMAT);
	}

	if(!defined("ADMINISTRATOR_GROUP_ID")){
		define("ADMINISTRATOR_GROUP_ID", 1);
	}

	if(!defined("MODERATOR_GROUP_ID")){
		define("MODERATOR_GROUP_ID", 2);
	}

	if(!defined("TRUSTED_GROUP_ID")){
		define("TRUSTED_GROUP_ID", 3);
	}

	if(!defined("USER_GROUP_ID")){
		define("USER_GROUP_ID", 4);
	}

	if(!defined("BANNED_GROUP_ID")){
		define("BANNED_GROUP_ID", 5);
	}

	if(!defined("GUEST_GROUP_ID")){
		define("GUEST_GROUP_ID", 6);
	}

	if(!defined("DEFAULT_GROUPS_AMOUNT")){
		define("DEFAULT_GROUPS_AMOUNT", 6);
	}

	date_default_timezone_set(UCMS_TIMEZONE);  						// Set current timezone
}

/**
 *
 * Print common settings
 * @package uCMS
 * @since uCMS 1.2
 * @version uCMS 1.3
 * @return nothing
 *
*/
function site_info($type){
	switch ($type) {
		case 'title':
			echo htmlspecialchars(SITE_TITLE);
		break;
		
		case 'description':
			echo htmlspecialchars(SITE_DESCRIPTION);
		break;

		case 'name':
			echo htmlspecialchars(SITE_NAME);
		break;

		case 'author':
			echo htmlspecialchars(SITE_AUTHOR);
		break;

		case 'domain':
			echo htmlspecialchars(SITE_DOMAIN);
		break;

		default:
			echo UCMS_VERSION;
		break;
	}
	
}

/**
 *
 * Parse sef link for posts
 * @package uCMS
 * @since uCMS 1.2
 * @version uCMS 1.3
 * @return string (full link)
 *
*/
function post_sef_links($posts){
	global $user, $udb;
	if(is_bool($posts)) return false;
	if(count($posts) == 0) return false;
	$post_date = explode(" ", $posts['date']);
	$date = $post_date[0];
	$time = $post_date[1];
	$date = explode("-", $date);
	$year = $date[0];
	$month = $date[1];
	$day = $date[2];
	$time = explode(":", $time);
	$hour = $time[0];
	$minute = $time[1];
	$second = $time[2];
	$slink = POST_SEF_LINK;
	if(preg_match("/@author@/", POST_SEF_LINK)){
		if((int) $posts['author'] > 0){
			$post_author = !empty($posts['author_login']) ? $posts['author_login'] : $user->get_user_login($posts['author']);
		}
		else {
			$post_author = $posts['author'];
		}
		$slink = preg_replace("/@author@/", $post_author, $slink);
	}

	if(preg_match("/@category@/", POST_SEF_LINK) or preg_match("/@category_alias@/", POST_SEF_LINK)){
		$category = (!empty($posts['category_name']) AND !empty($posts['category_alias']))
		? $posts : $udb->get_row("SELECT `name` AS `category_name`, `alias` AS `category_alias` FROM `".UC_PREFIX."categories` WHERE `id` = '$posts[category]'");
		$slink = preg_replace("/@category@/", $category['category_name'], $slink);
		$slink = preg_replace("/@category_alias@/", $category['category_alias'], $slink);
	}
	$patterns = array(
					0 => "/@alias@/",
					1 => "/@id@/",
					2 => "/@title@/",
					3 => "/@year@/",
					4 => "/@month@/",
					5 => "/@day@/",
					6 => "/@hour@/",
					7 => "/@minute@/",
					8 => "/@second@/");
	$replaces = array(
					0 => $posts['alias'],
					1 => $posts['id'],
					2 => $posts['title'],
					3 => $year,
					4 => $month,
					5 => $day,
					6 => $hour,
					7 => $minute, 
					8 => $second);
	$slink = preg_replace($patterns, $replaces, $slink);
	return $slink = UCMS_DIR."/".$slink;
}

/**
 *
 * Parse sef link for pages
 * @package uCMS
 * @since uCMS 1.2
 * @version uCMS 1.3
 * @return string (full link)
 *
*/
function page_sef_links($pages){
	global $user, $udb;
	$page_date = explode(" ", $pages['date']);
	$date = $page_date[0];
	$time = $page_date[1];
	$date = explode("-", $date);
	$year = $date[0];
	$month = $date[1];
	$day = $date[2];
	$time = explode(":", $time);
	$hour = $time[0];
	$minute = $time[1];
	$second = $time[2];
	$slink = PAGE_SEF_LINK;
	if(preg_match("/@author@/", PAGE_SEF_LINK)){
		if((int) $pages['author'] > 0)
			$page_author = !empty($pages['author_login']) ? $pages['author_login'] : $user->get_user_login($pages['author']);
		else 
			$page_author = $pages['author'];
		$slink = preg_replace("/@author@/", $page_author, $slink);
	}
	if(preg_match("#(@parent_alias@|@parent_id@|@parent_title@)#", PAGE_SEF_LINK)){
		$parent = (!empty($pages['parent_id']) and !empty($pages['parent_alias']) and !empty($pages['parent_title'])) 
		? $pages : $udb->get_row("SELECT `id` AS `parent_id`, `alias` AS `parent_alias`, `title` AS `parent_title`
		FROM `".UC_PREFIX."pages` WHERE `id` = '".$pages['parent']."' LIMIT 1");
		if($parent and count($parent) > 0){
			$slink = preg_replace("/@parent_alias@/", $parent['parent_alias'], $slink);
			$slink = preg_replace("/@parent_id@/", $parent['parent_id'], $slink);
			$slink = preg_replace("/@parent_title@/", $parent['parent_title'], $slink);
		}else{
			$slink = preg_replace("#(@parent_alias@/|@parent_id@/|@parent_title@/)#", '', $slink);
		}
	}
	$patterns = array(
					0 => "/@alias@/",
					1 => "/@id@/",
					2 => "/@title@/",
					3 => "/@year@/",
					4 => "/@month@/",
					5 => "/@day@/",
					6 => "/@hour@/",
					7 => "/@minute@/",
					8 => "/@second@/");
	$replaces = array(
					0 => $pages['alias'],
					1 => $pages['id'],
					2 => $pages['title'],
					3 => $year,
					4 => $month,
					5 => $day,
					6 => $hour,
					7 => $minute, 
					8 => $second);
	$slink = preg_replace($patterns, $replaces, $slink);
	return $slink = UCMS_DIR."/".$slink;
}
?>