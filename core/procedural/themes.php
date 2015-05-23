<?php

function get_load_time(){
	return uCMS::getInstance()->getLoadTime();
}

function get_queries_count(){
	return DatabaseConnection::getDefault()->getQueriesCount();
}

function get_header(){
	return Theme::getCurrent()->loadBlock('header');
}

function get_sidebar(){
	return Theme::getCurrent()->loadBlock('sidebar');
}

function get_footer(){
	return Theme::getCurrent()->loadBlock('footer');
}

function get_theme_file($name){
	return Theme::getCurrent()->getURLFilePath(Theme::getCurrent()->getInfo($name));
}

function get_style($name = 'style'){
	return get_theme_file($name);
}

function get_title(){
	return (UCMS_DEBUG ? tr("[DEBUG] ") : "").Theme::getCurrent()->getTitle();
}

function error_404(){
	log_add(tr("Page not found at action: @s", get_current_action()), UC_LOG_WARNING);
	$theme = Settings::get('theme');
	if( empty($theme) ) $theme = DEFAULT_THEME;
	if( !Theme::isLoaded() || $theme != Theme::getCurrent()->getName() ) {
		uCMS::getInstance()->reloadTheme($theme);
	}
	Theme::getCurrent()->setTitle(tr("404 Not Found"));
	Theme::getCurrent()->setAction(ERROR_TEMPLATE_NAME);
	Theme::getCurrent()->load();
	exit;
	//include DEFAULT_ERROR_TEMPLATE;
	//exit;
}

?>