<?php
function varDump($var){
	echo '<pre>';
	var_dump($var);
	echo '</pre>';
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
		if($logFile){
			fwrite($logFile, $outMessage);
			fclose($logFile);
		}
		if($level === UC_LOG_CRITICAL){
			p($outMessage);
			die;
		}
	}
}
?>