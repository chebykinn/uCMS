<?php
// Transition to procedural style

function p($string){
	$args = func_get_args();
	echo call_user_func_array(array(uCMS::getInstance()->getLanguage(), 'get'), $args);
}

function tr($string){
	$args = func_get_args();
	return call_user_func_array(array(uCMS::getInstance()->getLanguage(), 'get'), $args);
}

function get_setting($name){
	return uCMS::getInstance()->getSettings()->get($name);
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


function log_add($message, $level = UC_LOG_INFO){
	if(LOG_LEVEL > $level){
		switch ($level) {
			case UC_LOG_INFO:
				$type = '[INFO]';
			break;

			case UC_LOG_NOTICE:
				$type = '[NOTICE]';
			break;

			case UC_LOG_WARNING:
				$type = '[WARNING]';
			break;

			case UC_LOG_ERROR:
				$type = '[ERROR]';
			break;

			case UC_LOG_CRITICAL:
				$type = '[CRITICAL]';
			break;
			
			default:
				$type = '[INFO]';
			break;
		}
		$outMessage = strftime("%Y-%m-%d %H:%M", time())." $type $message\n";
		$logFile = fopen(LOG_FILE, 'a');
		fwrite($logFile, $outMessage);
		fclose($logFile);
		if($level === UC_LOG_CRITICAL){
			p($outMessage);
			die;
		}
	}
}
?>