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
	return URLManager::getRaw();
}

function get_current_action(){
	return URLManager::getCurrentAction();
}

function get_current_page(){
	return URLManager::getCurrentPage();
}

function generate_hash($length = 32){
	$chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHI JKLMNOPRQSTUVWXYZ0123456789";
	$code = "";
	$clen = strlen($chars) - 1;  
	while (strlen($code) < $length) {
		$code .= $chars[mt_rand(0,$clen)];  
	}
	return $code;
}
?>