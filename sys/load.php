<?php
$current_time = microtime();
$current_time = explode(" ",$current_time);
$time_start = $current_time[1] + $current_time[0];

/*Загрузка всего необходимого*/
if(!defined("ABSPATH")){
	session_start();
	header("Location: sys/install/index.php");
	$_SESSION['update-config'] = true;
}
require ABSPATH.'sys/include/defines.php';
init_constants();

require ABSPATH.UC_SYS_PATH.'ucms.php'; 
$ucms = new uCMS(); // ТА-ДА-ДАМ!!!

$ucms->check_php_version();

require ABSPATH.UC_INCLUDES_PATH.'udb.php';
$udb = new uDB();
$con = $udb->db_connect(DB_SERVER, DB_USER, DB_PASSWORD, DB_NAME);

if(!isset($install)){
	require ABSPATH.UC_INCLUDES_PATH.'settings.php';

	require ABSPATH.UC_USERS_PATH.'users.php'; 
	$user = new users(); // Запуск системы управления пользователями :)
	if(USERS_MODULE){
		session_start();
		$user->autologin();
		$user->guest();
		
		require ABSPATH.UC_USERS_PATH.'groups.php'; 
		$group = new group(); // Запуск группирования пользователей
	
		$permissions = $group->set_user_permissions();
		$posts_access = $permissions[0];
		$comments_access = $permissions[1];
		$pages_access = $permissions[2];
		$users_access = $permissions[3];
	
		require ABSPATH.UC_USERS_PATH.'pm.php'; 
		$pm = new pm(); // Запуск личных сообщений пользователей
	}else{
		$posts_access = 7;
		$comments_access = 7;
		$pages_access = 7;
		$users_access = 7;
	}

	if(WIDGETS_MODULE){
		require ABSPATH.UC_INCLUDES_PATH.'widgets.php';
		$widget = new uWidgets(); //Запуск виджетов
	}
	require ABSPATH.UC_INCLUDES_PATH.'paging.php';
	if(UCMS_DEBUG){
		error_reporting(E_ALL);
	}else{
		error_reporting(0);
	}
}
?>