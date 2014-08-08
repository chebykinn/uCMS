<?php
/* Стандартные константы 
* с версии 1.2 */
$settings_array = array(
		1 => "site_name",
		2 => "site_description",
		3 => "site_title",
		4 => "nice_links",
		5 => "user_avatars",
		6 => "user_messages",
		7 => "domain",
		8 => "theme_dir",
		9 => "theme_name",
		10 => "num_tries",
		11 => "ucms_dir",
		12 => "ucms_maintenance",
		13 => "phpmyadmin",
		14 => "unique_emails",
		15 => "modules",
		16 => "posts_on_page",
		17 => "default_group",
		18 => "allow_registration",
		19 => "admin_email",
		20 => "comments_email",
		21 => "new_user_email",
		22 => "timezone",
		23 => "post_sef_link",
		24 => "page_sef_link",
		25 => "category_sef_prefix",
		26 => "tag_sef_prefix",
		27 => "use_captcha",
		28 => "site_author",
		29 => "avatar_width",
		30 => "avatar_height");

function get_setting_value($name = ""){
	global $setting, $settings_array;
	if((int) $name > 0){
		if(isset($setting[$name-1]['value'])){
			return $setting[$name-1]['value'];
		}else return false;
	}elseif($key = array_search($name, $settings_array)){
		return $setting[$key-1]['value'];
	}else return false;
}

$tables = array(			//полный список таблиц текущей версии uCMS
		1 => UC_PREFIX."attempts",
		2 => UC_PREFIX."categories",
		3 => UC_PREFIX."comments",
		4 => UC_PREFIX."groups",
		5 => UC_PREFIX."links",
		6 => UC_PREFIX."messages",
		7 => UC_PREFIX."pages",
		8 => UC_PREFIX."posts",
		9 => UC_PREFIX."settings",
		10 => UC_PREFIX."stats",
		11 => UC_PREFIX."themes",
		12 => UC_PREFIX."users",
		13 => UC_PREFIX."usersinfo",
		14 => UC_PREFIX."widgets");

$months = array(			//список месяцев в году, вроде не меняется :)
		"01" => "Январь", 
		"02" => "Февраль", 
		"03" => "Март", 
		"04" => "Апрель", 
		"05" => "Май", 
		"06" => "Июнь", 
		"07" => "Июль", 
		"08" => "Август", 
		"09" => "Сентябрь", 
		"10" => "Октябрь", 
		"11" => "Ноябрь", 
		"12" => "Декабрь");

function init_constants(){	//основные константы uCMS

	if(!defined(UCMS_VERSION)){
		define(UCMS_VERSION, "1.2");	//версия uCMS
	}

	if(!defined(UCMS_MIN_PHP_VERSION)){			//минимально необходимая версия PHP
		define(UCMS_MIN_PHP_VERSION, 5.2);
	}
	
	if(!defined(UC_DEFAULT_PATH)){
		define(UC_DEFAULT_PATH, "");
	}

	if(!defined(UC_DEFAULT_THEME_NAME)){		//название стандартной темы
		define(UC_DEFAULT_THEME_NAME, "uCMS");
	}

	if(!defined(UC_DEFAULT_THEME_DIR)){			//папка стандартной темы
		define(UC_DEFAULT_THEME_DIR, "ucms");
	}

	if (!defined(UC_SYS_PATH)){					//системная папка
		define(UC_SYS_PATH, 'sys/');
	}

	if (!defined(UC_INCLUDES_PATH)){
		define(UC_INCLUDES_PATH, UC_SYS_PATH.'include/');	//путь к системной папке включаемых файлов
	}

	if (!defined(UC_USERS_PATH)){
		define(UC_USERS_PATH, UC_SYS_PATH.'users/');		//путь к системной папке пользователей
	}

	if (!defined(UC_CONTENT_PATH)){
		define(UC_CONTENT_PATH, 'content/');				//папка контента
	}

	if (!defined(UC_THEMES_PATH)){
		define(UC_THEMES_PATH, UC_CONTENT_PATH.'themes/');	//путь к папке тем
	}

	if(!defined(UC_DEFAULT_THEMEPATH)){			//путь к стандартной теме
		define(UC_DEFAULT_THEMEPATH, UC_THEMES_PATH."ucms/");
	}

	if (!defined(WIDGETS_PATH)){
		define(WIDGETS_PATH, UC_CONTENT_PATH.'widgets/');	//путь к папке виджетов
	}

	if (!defined(AVATARS_PATH)){
		define(AVATARS_PATH, UC_CONTENT_PATH.'avatars/');	//путь к папке аватаров
	}

	if (!defined(TEMPLATES_PATH)){
		define(TEMPLATES_PATH, UC_CONTENT_PATH.'templates/');	//путь к папке шаблонов
	}

	if (!defined(ERROR_TEMPLATES_PATH)){
		define(ERROR_TEMPLATES_PATH, TEMPLATES_PATH.'errors/');	//путь к папке шаблонов ошибок
	}

	if (!defined(GENERAL_TEMPLATES_PATH)){
		define(GENERAL_TEMPLATES_PATH, TEMPLATES_PATH.'general/');	//путь к папке основных шаблонов
	}
}

function define_settings(){	//настраиваемые константы
	global $setting, $user;

	if(!defined(SITE_NAME)){
		define(SITE_NAME, $setting[0]['value']);	//название сайта
	}

	if(!defined(SITE_DESCRIPTION)){					//описание сайта
		define(SITE_DESCRIPTION, $setting[1]['value']);
	}

	if(!defined(SITE_TITLE)){						//заголовок сайта
		define(SITE_TITLE, $setting[2]['value']);
	}

	if(!defined(NICE_LINKS)){						//индикатор статуса красивых ссылок
		define(NICE_LINKS, (bool) $setting[3]['value']);
	}

	if(!defined(USER_AVATARS)){						//индикатор статуса аватаров пользователей
		define(USER_AVATARS, (bool) $setting[4]['value']);
	}

	if(!defined(USER_MESSAGES)){					//индикатор статуса личных сообщений пользователей
		define(USER_MESSAGES, (bool) $setting[5]['value']);
	}

	if(!defined(SITE_DOMAIN)){						//домен сайта
		define(SITE_DOMAIN, $setting[6]['value']);
	}

	if (!defined(THEMEDIR)){						//папка текущей темы
		define(THEMEDIR, $setting[7]['value']);
	}

	if (!defined(THEMEPATH)){						//путь к папке текущей темы
		define(THEMEPATH, UC_THEMES_PATH.THEMEDIR.'/');
	}

	if(!defined(THEMENAME)){						//название текущей темы
		define(THEMENAME, $setting[8]['value']);
	}

	if(!defined(LOGIN_ATTEMPTS_NUM)){				//количество попыток входа
		define(LOGIN_ATTEMPTS_NUM, $setting[9]['value']);
	}

	if(!defined(UCMS_DIR)){							//папка, в которой находится сама uCMS, если она находится в корне сайта, то константа пустая
		define(UCMS_DIR, $setting[10]['value']);
	}

	if(!defined(UCMS_MAINTENANCE)){					//индикатор режима техобслуживания
		define(UCMS_MAINTENANCE, $setting[11]['value']);
	}

	if(!defined(PHPMYADMIN_LINK)){					//ссылка на PHPMyAdmin
		define(PHPMYADMIN_LINK, $setting[12]['value']);
	}

	if(!defined(UNIQUE_EMAILS)){					//индикатор статуса уникальных email'ов
		define(UNIQUE_EMAILS, $setting[13]['value']);
	}

	if(!defined(UC_MODULES)){						//индикаторы модулей uCMS
		define(UC_MODULES, $setting[14]['value']);
		$modules = explode(',', UC_MODULES);
		define(POSTS_MODULE, (bool) $modules[0]);	//модуль постов
		define(COMMENTS_MODULE, (bool) $modules[1]);//модуль комментариев
		define(PAGES_MODULE, (bool) $modules[2]);	//модуль страниц
		define(USERS_MODULE, (bool) $modules[3]);	//модуль пользователей
		define(THEMES_MODULE, (bool) $modules[4]);	//модуль тем
		define(WIDGETS_MODULE, (bool) $modules[5]);	//модуль виджетов

	}

	if(!defined(POSTS_ON_PAGE)){					//количество выводимых постов на одной странице
		define(POSTS_ON_PAGE, $setting[15]['value']);
	}

	if(!defined(DEFAULT_GROUP)){					//стандартная группа для пользователей
		define(DEFAULT_GROUP, $setting[16]['value']);
	}

	if(!defined(ALLOW_REGISTRATION)){				//индикатор статуса регистрации
		define(ALLOW_REGISTRATION, (bool) $setting[17]['value']);
	}

	if(!defined(ADMIN_EMAIL)){						//email администратора
		define(ADMIN_EMAIL, $setting[18]['value']);
	}

	if(!defined(COMMENTS_EMAIL)){					//email для новых комментариев
		define(COMMENTS_EMAIL, $setting[19]['value']);
	}

	if(!defined(NEW_USER_EMAIL)){					//email для новых пользователей
		define(NEW_USER_EMAIL, $setting[20]['value']);
	}

	if(!defined(UCMS_TIMEZONE)){					//временная зона
		define(UCMS_TIMEZONE, $setting[21]['value']);
	}

	if(!defined(POST_SEF_LINK)){					//структура ссылок на посты
		define(POST_SEF_LINK, $setting[22]['value']);
	}

	if(!defined(PAGE_SEF_LINK)){					//структура ссылок на страницы
		define(PAGE_SEF_LINK, $setting[23]['value']);
		$p = explode("@", PAGE_SEF_LINK);
		$p = str_replace("/", "", $p[0]);
		define(PAGE_SEF_PREFIX, $p);
	}

	if(!defined(CATEGORY_SEF_PREFIX)){				//префикс к ссылкам на категории
		define(CATEGORY_SEF_PREFIX, $setting[24]['value']);
	}

	if(!defined(TAG_SEF_PREFIX)){					//префикс к ссылкам на теги
		define(TAG_SEF_PREFIX, $setting[25]['value']);
	}

	if(!defined(USE_CAPTCHA)){						//индикатор капчи: 0 - выключена, 1 - при регистрации, 2 - при добавлении комментария у гостей, 3 - при добавлении комментария у пользователей
		define(USE_CAPTCHA, (int) $setting[26]['value']);
	}
	
	if(!defined(SITE_AUTHOR)){						//автор сайта
		define(SITE_AUTHOR, $setting[27]['value']);
	}

	if (!defined(AVATAR_WIDTH)){					//ширина аватара
		define(AVATAR_WIDTH, $setting[28]['value']);
	}

	if (!defined(AVATAR_HEIGHT)){					//высота аватара
		define(AVATAR_HEIGHT, $setting[29]['value']);
	}

	if(!defined(UCMS_URL)){							//полный адрес к uCMS
		define(UCMS_URL, SITE_DOMAIN.UCMS_DIR);
	}
}

function site_info($type){
	switch ($type) {
		case 'title':
			echo SITE_TITLE;
		break;
		
		case 'description':
			echo SITE_DESCRIPTION;
		break;

		case 'name':
			echo SITE_NAME;
		break;

		case 'author':
			echo SITE_AUTHOR;
		break;

		case 'domain':
			echo SITE_DOMAIN;
		break;

		default:
			echo UCMS_VERSION;
		break;
	}
	
}

function theme_path(){
	echo UCMS_DIR."/".THEMEPATH;
}

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
		if((int) $posts['author'] > 0)
			$post_author = $user->get_user_login($posts['author']);
		else 
			$post_author = $posts['author'];
		$slink = preg_replace("/@author@/", $post_author, $slink);
	}

	if(preg_match("/@category@/", POST_SEF_LINK) or preg_match("/@category_alias@/", POST_SEF_LINK)){
		$category = $udb->get_row("SELECT `name`, `alias` FROM `".UC_PREFIX."categories` WHERE `id` = '$posts[category]'");
		$slink = preg_replace("/@category@/", $category['name'], $slink);
		$slink = preg_replace("/@category_alias@/", $category['alias'], $slink);
	}

	$slink = preg_replace("/@alias@/", $posts['alias'], $slink);
	$slink = preg_replace("/@id@/", $posts['id'], $slink);
	$slink = preg_replace("/@title@/", $posts['title'], $slink);
	$slink = preg_replace("/@year@/", $year, $slink);
	$slink = preg_replace("/@month@/", $month, $slink);
	$slink = preg_replace("/@day@/", $day, $slink);
	$slink = preg_replace("/@hour@/", $hour, $slink);
	$slink = preg_replace("/@minute@/", $minute, $slink);
	$slink = preg_replace("/@second@/", $second, $slink);
	return $slink = UCMS_DIR."/".$slink;
}

function page_sef_links($pages){
	global $user;
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
			$page_author = $user->get_user_login($pages['author']);
		else 
			$page_author = $pages['author'];
		$slink = preg_replace("/@author@/", $page_author, $slink);
	}
	$slink = preg_replace("/@alias@/", $pages['alias'], $slink);
	$slink = preg_replace("/@id@/", $pages['id'], $slink);
	$slink = preg_replace("/@title@/", $pages['title'], $slink);
	$slink = preg_replace("/@year@/", $year, $slink);
	$slink = preg_replace("/@month@/", $month, $slink);
	$slink = preg_replace("/@day@/", $day, $slink);
	$slink = preg_replace("/@hour@/", $hour, $slink);
	$slink = preg_replace("/@minute@/", $minute, $slink);
	$slink = preg_replace("/@second@/", $second, $slink);
	return $slink = UCMS_DIR."/".$slink;
}
?>