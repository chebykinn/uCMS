<?php
use uCMS\Core\Localization\Language;
function p($string){
	$args = func_get_args();
	echo call_user_func_array(array(Language::GetCurrent(), 'get'), $args);
}

function tr($string){
	$args = func_get_args();
	return call_user_func_array(array(Language::GetCurrent(), 'get'), $args);
}
?>