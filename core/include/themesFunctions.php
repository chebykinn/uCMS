<?php

function get_load_time(){
	return uCMS::getInstance()->getLoadTime();
}

function get_queries_count(){
	return uCMS::getInstance()->getDatabase()->getQueriesCount();
}

function get_header(){
	return uCMS::getInstance()->getCurrentTheme()->loadBlock('header');
}

function get_sidebar(){
	return uCMS::getInstance()->getCurrentTheme()->loadBlock('sidebar');
}

function get_footer(){
	return uCMS::getInstance()->getCurrentTheme()->loadBlock('footer');
}

function get_style(){
	return uCMS::getInstance()->getCurrentTheme()->getURLFilePath(uCMS::getInstance()->getCurrentTheme()->getInfo('style'));
}

function get_title(){
	return uCMS::getInstance()->getCurrentTheme()->getTitle();
}

function error_404(){
	$theme = get_setting('theme');
	if(empty($theme)) $theme = DEFAULT_THEME;
	if( file_exists(get_theme_path($theme).ERROR_TEMPLATE) ){
		if( !is_object( uCMS::getInstance()->getCurrentTheme() ) || $theme != uCMS::getInstance()->getCurrentTheme()->getName() ) uCMS::getInstance()->reloadTheme($theme);
		uCMS::getInstance()->getCurrentTheme()->setTitle(tr("404 Not Found"));
		include get_theme_path($theme).ERROR_TEMPLATE;
		exit;
	}
	include DEFAULT_ERROR_TEMPLATE;
	exit;
}

?>