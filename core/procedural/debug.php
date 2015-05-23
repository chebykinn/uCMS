<?php
function begin_debug_block(){
	echo '<pre style="
	text-align: left; 
	background: #fff; 
	color: #000; 
	padding: 5px;
	border: 1px #aa1111 solid; 
	margin: 20px; 
	z-index: 9999;">';
}

function end_debug_block(){
	echo '</pre>';
}

function varDump($var){
	begin_debug_block();
	var_dump($var);
	end_debug_block();
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
		$host = Session::getCurrent()->getHost();
		$outMessage = strftime("%Y-%m-%d %H:%M:%S", time())." [Host: $host] $type $message\n";
		$logFile = fopen(LOG_FILE, 'a');
		if($logFile){
			fwrite($logFile, $outMessage);
			fclose($logFile);
		}
		if($level === UC_LOG_CRITICAL){
			echo "<pre>";
			p($outMessage);
			echo "</pre>";
			die;
		}
	}
}
?>