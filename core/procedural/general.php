<?php
/**
* @file Transition to procedural style
*/

function p($string){
	$args = func_get_args();
	echo call_user_func_array(array(Language::getCurrent(), 'get'), $args);
}

function tr($string){
	$args = func_get_args();
	return call_user_func_array(array(Language::getCurrent(), 'get'), $args);
}

function get_setting($name){
	return Settings::get($name);
}

function get_theme_path($name){
	return THEMES_PATH.$name.'/';
}

function get_current_url(){
	$url = new URLManager();
	return $url->getRaw();
}

function get_current_action(){
	$url = new URLManager();
	return $url->getCurrentAction();
}

function get_current_page(){
	$url = new URLManager();
	return $url->getCurrentPage();
}
?>