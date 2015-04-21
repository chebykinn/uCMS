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

function get_style(){
	return Theme::getCurrent()->getURLFilePath(Theme::getCurrent()->getInfo('style'));
}

function get_title(){
	return Theme::getCurrent()->getTitle();
}

function error_404(){
	$theme = Settings::get('theme');
	if(empty($theme)) $theme = DEFAULT_THEME;
	if( file_exists(get_theme_path($theme).ERROR_TEMPLATE) ){
		if( $theme != Theme::getCurrent()->getName() ) uCMS::getInstance()->reloadTheme($theme);
		Theme::getCurrent()->setTitle(tr("404 Not Found"));
		include get_theme_path($theme).ERROR_TEMPLATE;
		exit;
	}
	include DEFAULT_ERROR_TEMPLATE;
	exit;
}

?>