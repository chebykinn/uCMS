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
* Take cron shedule from settings table
* @version 1.3
* @since 1.3
* @return array
*
*/
function get_cron_shedule(){
	global $ucms;
	$cron = $ucms->get_setting_value('cron_shedule');
	if(!empty($cron)){
		return unserialize($cron);
	}else return array();
}

/**
* Update cron shedule in settings table
* @version 1.3
* @since 1.3
* @return bool
*
*/
function update_cron_shedule($cron){
	global $ucms;
	return $ucms->update_setting('cron_shedule', serialize($cron));
}

/**
* Clear cron shedule in settings table
* @version 1.3
* @since 1.3
* @return bool
*
*/
function clear_cron_shedule(){
	global $ucms;
	return $ucms->update_setting('cron_shedule', '');
}

/**
* Check if function is in cron shedule
* @version 1.3
* @since 1.3
* @return bool
*
*/
function is_sheduled_cron_event($handler){
	$cron = get_cron_shedule();
	return !empty($cron[$handler]);
}

/**
* Shedule cron event
* @version 1.3
* @since 1.3
* @return bool
*
*/
function shedule_cron_event($timestamp, $period, $handler, $args = array()){
	$cron = get_cron_shedule();
	$periods = get_periods();

	if(!isset($periods[$period]) && (int) $period === 0)
		return false;
	
	if((int) $period > 0){
		$p = $period;
	}else{
		$p = $periods[$period];
	}

	$cron[$handler] = array('args' => $args, 'timestamp' => $timestamp, 'period' => $p);
	uasort($cron, 'time_sort');

	return update_cron_shedule($cron);
	
}

/**
* Change the time of deploying for sheduled function
* @version 1.3
* @since 1.3
* @return bool
*
*/
function reshedule_cron_event($timestamp, $handler){
	$cron = get_cron_shedule();

	$cron[$handler]['timestamp'] = $timestamp;
	uasort($cron, 'time_sort');

	return update_cron_shedule($cron);
	
}

/**
* Remove function from shedule
* @version 1.3
* @since 1.3
* @return bool
*
*/
function unshedule_cron_event($handler){
	$cron = get_cron_shedule();

	unset($cron[$handler]);

	return update_cron_shedule($cron);
	
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
* Run all the sheduled events at specified time
* @version 1.3
* @since 1.3
* @return nothing
*
*/
function run_sheduled_events(){
	global $cron_lock;
	$cron = get_cron_shedule();
	$time = time();
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
		if(!empty($cron_lock) and $cron_lock != $ucms->get_setting_value('cron_lock'))
			break;
	}
}
?>