<?php
/**
* uSers fallback class (if communism will be revived)
*
* @package uCMS
* @since 1.3
* @version 1.3
*
*/
class users_min{

	function get_user_id($login = ''){
		return 1;
	}

	function get_user_login($id = ''){
		global $ucms;
		return $ucms->cout("users_min.login", true);
	}

	function get_user_password(){
		return false;
	}

	function get_user_group($id = '', $login = ''){
		return 1;
	}

	function get_user_avatar($id = '', $login = ''){
		return false;
	}

	function get_user_email($id = '', $login = ''){
		return false;
	}

	function get_user_group_name($id = '', $login = ''){
		global $ucms;
		return $ucms->cout("users_min.group", true);
	}

	function get_user_ip(){
		$ip = getenv("HTTP_X_FORWARDED_FOR");
		if (empty($ip) || $ip == 'unknown'){
			$ip = getenv("REMOTE_ADDR"); 
		}
		return $ip;
	}

	function logged(){
		return true;
	}

	function admin(){
		return true;
	}

	function is_admin($id = '', $login = ''){
		return true;
	}

	function profile_menu(){
		global $ucms;
		$ucms->cout("users_min.module_disabled.message");
		return false;
	}

	function list_users(){
		global $ucms;
		$ucms->cout("users_min.module_disabled.message");
		return false;
	}

	function has_access($accessID = 0, $accessLVL = 2){
		return true;
	}

	function get_profile_link($id = ''){
		return false;
	}

	function is_online($id = ''){
		return true;
	}

	function get_guests_count(){
		return 0;
	}

	function update_visitors_count(){
		return 0;
	}
}
?>