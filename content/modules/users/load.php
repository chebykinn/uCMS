<?php
require ABSPATH.USERS_MODULE_PATH.'users.php'; 
require_once ABSPATH.USERS_MODULE_PATH.'login.php';

$user = new uSers(); // Запуск системы управления пользователями :)
$user->autologin();

$login = new login($user);
//var_dump($user->logged());
if(!defined("USERS_ONLINE_COUNT")){
	$users_count = $udb->num_rows("SELECT `id` FROM `".UC_PREFIX."users` WHERE `online` = '1' AND `activation` = '1'");
	define("USERS_ONLINE_COUNT", $users_count);
}

function update_visitors_count(){
	global $ucms, $user;
	$visitors_count = $user->get_site_visitors();
	$ucms->update_setting("visitors_count", $visitors_count);
}

if(!is_scheduled_cron_event('update_visitors_count'))
	schedule_cron_event('update_visitors_count', time(), MINUTE_IN_SECONDS * 10, 'update_visitors_count');

$user->guest();

require ABSPATH.USERS_MODULE_PATH.'groups.php'; 
$group = new group($user); // Запуск группирования пользователей


$usr_grp = $user->get_user_group();

$permissions = $udb->get_rows("SELECT * FROM `".UC_PREFIX."groups`");
if($permissions and count($permissions) > 0){
	for($i = 0; $i < count($permissions); $i++){
		if(!defined(mb_strtoupper($permissions[$i]['alias'], "UTF-8")."_GROUP_PERMISSIONS")){
			define(mb_strtoupper($permissions[$i]['alias'], "UTF-8")."_GROUP_PERMISSIONS", $permissions[$i]['permissions']);
		}
		if($usr_grp == $permissions[$i]['id']){
			if(!defined("CURRENT_USER_GROUP_PERMISSIONS")){
				define("CURRENT_USER_GROUP_PERMISSIONS", $permissions[$i]['permissions']);
			}
		}
	}
	
}

require ABSPATH.USERS_MODULE_PATH.'pm.php'; 
$pm = new Messaging($user); // Запуск личных сообщений пользователей

?>