<?php
/**
* @file Transition to procedural style
*/

function p($string){
	$args = func_get_args();
	echo call_user_func_array(array(Language::GetCurrent(), 'get'), $args);
}

function tr($string){
	$args = func_get_args();
	return call_user_func_array(array(Language::GetCurrent(), 'get'), $args);
}

function get_setting($name){ // ?
	return Settings::get($name);
}

function get_theme_path($name){  // ?
	return THEMES_PATH.$name.'/';
}

function get_current_url(){  // ?
	return (string)Page::GetCurrent();
}

function get_current_action(){  // ?
	return Page::GetCurrent()->getAction();
}

function get_current_page(){  // ?
	return Page::GetCurrent()->getPageNumber();
}

function generate_hash($length = 32){  // ?
	$chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHI JKLMNOPRQSTUVWXYZ0123456789";
	$code = "";
	$clen = strlen($chars) - 1;  
	while (strlen($code) < $length) {
		$code .= $chars[mt_rand(0,$clen)];  
	}
	return $code;
}
?>