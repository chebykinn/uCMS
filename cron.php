<?php
/**
* @package uCMS 
* @version 1.3
* uCMS cron file
*/
ignore_user_abort(true);

if(defined("CRON_STARTED") || !empty($_POST))
	exit;

define("CRON_STARTED", true);


if(!defined(ABSPATH)){
	if(!file_exists("config.php")){ 
		if(!file_exists("../config.php")){
			header("Location: sys/install/index.php");
			exit;
		}else{ 
			define(ABSPATH, dirname(__FILE__)."/");
			require '../config.php';
		}
	}else{
		require 'config.php';
	}
}

$time = time();

if(empty(get_cron_shedule()))
	exit;

$temp_cron_lock = $ucms->get_setting_value('cron_lock');

if(empty($cron_lock)){
	if(empty($_GET['run'])){
		if($temp_cron_lock && ($temp_cron_lock + UC_CRON_LOCK_TIMEOUT > $time))
			exit;
		$ucms->update_setting('cron_lock', $time);
		$cron_lock = $time;
	}else{
		$cron_lock = (int) $_GET['run'];
	}
}

$cron = get_cron_shedule();
foreach ($cron as $handler => $data) {

	if($data["timestamp"] > $time)
		break;

	call_user_func_array($handler, $data["args"]);

	if($data['period'] != 0){
		reshedule_cron_event(time()+$data['period'], $handler);
	}
	else{
		unshedule_cron_event($handler);
	}

	if($cron_lock != $ucms->get_setting_value('cron_lock'))
		break;
}
if($cron_lock == $ucms->get_setting_value('cron_lock'))
	$ucms->update_setting('cron_lock', '');
?>