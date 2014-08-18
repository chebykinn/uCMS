<?php
/**
* uCMS Cron
*
* @package uCMS
* @since 1.0
* @version 1.3
*
*/

/**
* Time sort func for usort
*/
function time_sort($a, $b){
	if($a['timestamp'] == $b['timestamp'])
		return 0;
	return ($a['timestamp'] < $b['timestamp']) ? -1 : 1;
}

/**
* Take cron schedule from settings table
* @version 1.3
* @since 1.3
* @return array
*
*/
function get_cron_schedule(){
	global $ucms;
	$cron = $ucms->get_setting_value('cron_schedule');
	if(!empty($cron)){
		return unserialize($cron);
	}else return array();
}

/**
* Update cron schedule in settings table
* @version 1.3
* @since 1.3
* @return bool
*
*/
function update_cron_schedule($cron){
	global $ucms;
	return $ucms->update_setting('cron_schedule', serialize($cron));
}

/**
* Clear cron schedule in settings table
* @version 1.3
* @since 1.3
* @return bool
*
*/
function clear_cron_schedule(){
	global $ucms;
	return $ucms->update_setting('cron_schedule', '');
}

/**
* Check if function is in cron schedule
* @version 1.3
* @since 1.3
* @return bool
*
*/
function is_scheduled_cron_event($key){
	$cron = get_cron_schedule();
	return !empty($cron[$key]);
}

/**
* schedule cron event
* @version 1.3
* @since 1.3
* @return bool
*
*/
function schedule_cron_event($key, $timestamp, $period, $handler, $args = array()){
	$cron = get_cron_schedule();
	$periods = get_periods();

	if(!isset($periods[$period]) && (int) $period === 0)
		return false;
	
	if((int) $period > 0){
		$p = $period;
	}else{
		$p = $periods[$period];
	}

	$cron[$key] = array('handler' => $handler, 'args' => $args, 'timestamp' => $timestamp, 'period' => $p);
	uasort($cron, 'time_sort');

	return update_cron_schedule($cron);
	
}

/**
* Change the time of deploying for scheduled function
* @version 1.3
* @since 1.3
* @return bool
*
*/
function reschedule_cron_event($timestamp, $key){
	$cron = get_cron_schedule();

	$cron[$key]['timestamp'] = $timestamp;
	uasort($cron, 'time_sort');

	return update_cron_schedule($cron);
	
}

/**
* Remove function from schedule
* @version 1.3
* @since 1.3
* @return bool
*
*/
function unschedule_cron_event($key){
	$cron = get_cron_schedule();

	unset($cron[$key]);

	return update_cron_schedule($cron);
	
}

/**
* Default periods array
* @version 1.3
* @since 1.3
* @return array
*
*/
function get_periods(){
	return array(
	'once'     	  	=> 0,
	'hourly'      	=> HOUR_IN_SECONDS,
	'daily'       	=> DAY_IN_SECONDS,
	'twicedaily'  	=> DAY_IN_SECONDS / 2,
	'weekly'      	=> WEEK_IN_SECONDS,
	'twiceweekly'  	=> WEEK_IN_SECONDS / 2);
}

/**
* Run all the scheduled events at specified time
* @version 1.3
* @since 1.3
* @return nothing
*
*/
function run_scheduled_events(){
	global $cron_lock;
	$cron = get_cron_schedule();
	$time = time();
	foreach ($cron as $key => $data) {
		if($data["timestamp"] > $time)
			break;

		call_user_func_array($data["handler"] , $data["args"]);

		if($data['period'] != 0){
			reschedule_cron_event(time()+$data['period'], $key);
		}
		else{
			unschedule_cron_event($key);
		}
		if(!empty($cron_lock) and $cron_lock != $ucms->get_setting_value('cron_lock'))
			break;
	}
}
?>