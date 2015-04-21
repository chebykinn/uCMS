<?php

if( !defined("CORE_VERSION") ){
	define("CORE_VERSION", '2.0');
}

if( !defined("COMPATIBILITY_VERSION") ){
	define("COMPATIBILITY_VERSION", '2.x');
}

if( !defined("EXTENTION_INFO") ){
	define("EXTENTION_INFO", 'extention.info');
}

if( !defined("THEME_INFO") ){
	define("THEME_INFO", 'theme.info');
}

if( !defined("DEFAULT_THEME") ){
	define("DEFAULT_THEME", 'ucms');
}

if( !defined("ADMIN_THEME") ){
	define("ADMIN_THEME", 'admin');
}

if( !defined("INDEX_ACTION") ){
	define("INDEX_ACTION", 'index');
}

if( !defined("OTHER_ACTION") ){
	define("OTHER_ACTION", 'other');
}

if( !defined("ADMIN_ACTION") ){
	define("ADMIN_ACTION", 'admin');
}

if( !defined("CONTENT_PATH") ){
	define("CONTENT_PATH", ABSPATH.'content/');
}

if( !defined("UCMS_DIR") ){
	define("UCMS_DIR", '/');
}

if( !defined("CONTENT_URL_PATH") ){
	define("CONTENT_URL_PATH", UCMS_DIR.'content/');
}

if( !defined("TEMPLATES_PATH") ){
	define("TEMPLATES_PATH", CONTENT_PATH.'templates/');
}

if( !defined("EXTENTIONS_PATH") ){
	define("EXTENTIONS_PATH", CONTENT_PATH.'extentions/');
}

if( !defined("EXTENTIONS_URL_PATH") ){
	define("EXTENTIONS_URL_PATH", CONTENT_URL_PATH.'extentions/');
}

if( !defined("THEMES_PATH") ){
	define("THEMES_PATH", CONTENT_PATH.'themes/');
}

if( !defined("THEMES_URL_PATH") ){
	define("THEMES_URL_PATH", CONTENT_URL_PATH.'themes/');
}

if( !defined("CORE_PATH") ){
	define("CORE_PATH", ABSPATH.'core/');
}

if( !defined("INCLUDE_PATH") ){
	define("INCLUDE_PATH", CORE_PATH.'include/');
}

if( !defined("ERROR_TEMPLATE_NAME") ){
	define("ERROR_TEMPLATE_NAME", 'error');
}

if( !defined("ERROR_TEMPLATE") ){
	define("ERROR_TEMPLATE", ERROR_TEMPLATE_NAME.'.php');
}

if( !defined("DEFAULT_ERROR_TEMPLATE") ){
	define("DEFAULT_ERROR_TEMPLATE", TEMPLATES_PATH.ERROR_TEMPLATE_NAME.'.php');
}

if( !defined("UC_LOG_ALL") ){
	define("UC_LOG_ALL", 10);
}

if( !defined("UC_LOG_INFO") ){
	define("UC_LOG_INFO", 4);
}

if( !defined("UC_LOG_NOTICE") ){
	define("UC_LOG_NOTICE", 3);
}

if( !defined("UC_LOG_WARNING") ){
	define("UC_LOG_WARNING", 2);
}

if( !defined("UC_LOG_ERROR") ){
	define("UC_LOG_ERROR", 1);
}

if( !defined("UC_LOG_CRITICAL") ){
	define("UC_LOG_CRITICAL", 0);
}

if( !defined("LOG_FILE") ){
	define("LOG_FILE", CONTENT_PATH.'ucms.log');
}

if( !defined("LOG_LEVEL") ){
	define("LOG_LEVEL", UC_LOG_ALL);
}

if( !defined("LANGUAGES_PATH") ){
	define("LANGUAGES_PATH", CONTENT_PATH.'languages/');
}

if( !defined("MINUTE_IN_SECONDS") ){ // Time constants
	define('MINUTE_IN_SECONDS',	60);
	define('HOUR_IN_SECONDS',	60 * MINUTE_IN_SECONDS);
	define('DAY_IN_SECONDS',	24 * HOUR_IN_SECONDS);
	define('WEEK_IN_SECONDS',	 7 * DAY_IN_SECONDS);
	define('MONTH_IN_SECONDS',	 4 * WEEK_IN_SECONDS);
	define('YEAR_IN_SECONDS',  365 * DAY_IN_SECONDS);
}

if( !defined("SESSION_IDLE_LIFETIME") ){
	define("SESSION_IDLE_LIFETIME", 2 * HOUR_IN_SECONDS);
}

if( !defined("DEFAULT_DATABASE_NAME") ){
	define("DEFAULT_DATABASE_NAME", "default");
}
?>