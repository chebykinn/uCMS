<?php
class Debug{

	public static function Init(){
		register_shutdown_function( "Debug::ErrorHandler" );
		set_error_handler('Debug::ErrorHandler');
		ini_set('display_errors', 0);
		if(UCMS_DEBUG){ // Debug mode preparation
			error_reporting(E_ALL);
			ini_set('log_errors', 1);
			ini_set('error_log', CONTENT_PATH.'debug.log');
		}else{
			error_reporting(E_ALL ^ (E_DEPRECATED | E_NOTICE | E_STRICT));
		}
	}

	public static function BeginBlock(){
		echo '<pre style="
		text-align: left; 
		background: #fff; 
		color: #000; 
		padding: 5px;
		border: 1px #aa1111 solid; 
		margin: 20px; 
		z-index: 9999;">';
	}

	public static function EndBlock(){
		echo '</pre>';
	}

	public static function PrintVar($var){
		self::BeginBlock();
		var_dump($var);
		self::EndBlock();
	}

	public static function Log($message, $level = UC_LOG_INFO){
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
			$host = Session::GetCurrent()->getHost();
			$outMessage = strftime("%Y-%m-%d %H:%M:%S", time())." [Host: $host] $type $message\n";
			$logFile = @fopen(LOG_FILE, 'a');
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

	
	/**
	* Handler for PHP errors
	*
	* @package uCMS
	* @since 1.3
	* @version 1.3
	* @return nothing
	*
	*/
	public static function ErrorHandler($errno = "", $errstr = "", $errfile = "", $errline = ""){
		if(empty($errno) && empty($errstr) && empty($errfile) && empty($errline)){
			$error = error_get_last();
			$errno = $error["type"];
			$errstr = $error["message"];
			$errfile = $error["file"];
			$errline = $error["line"];
		}

		if (!(error_reporting() & $errno) || error_reporting() === 0) {
   		    return;
   		}
   		$die = false;
   		echo "<br>";
		Debug::BeginBlock();
   		echo '<h2>';
		switch ($errno) {
			case E_RECOVERABLE_ERROR:
				echo "PHP Catchable Fatal Error";
			break;
			
			case E_NOTICE:
				echo "PHP Notice";
			break;

			case E_WARNING:
				echo "PHP Warning";
			break;

			case E_ERROR:
				echo "PHP Fatal Error";
				$die = true;
			break;

			case E_PARSE:
				echo "PHP Parse Error";
				$die = true;
			break;

			case E_COMPILE_ERROR:
				echo "PHP Compile Fatal Error";
				$die = true;
			break;

			case E_DEPRECATED:
				echo "PHP Deprecated Message";
			break;

			case E_STRICT:
				echo "PHP Strict Standars";
			break;

			default:
				echo "PHP Error $errno";
			break;
		}
   		echo '</h2>';
		if(!UCMS_DEBUG){
			echo "$errstr in <b>$errfile</b> on line <b>$errline</b>";
		}else{
			echo "$errstr in <b>$errfile</b> on line <b>$errline</b><br>";
			echo '<p style="font-size: 8pt; padding: 10px;">';
			echo debug_print_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
			echo '</p>';
		}
		Debug::EndBlock();
		echo "<br>";
		if($die) die;
	}
}
function begin_debug_block(){
	Debug::BeginBlock();
	debug_print_backtrace();
	Debug::EndBlock();
}

function end_debug_block(){
	Debug::BeginBlock();
	debug_print_backtrace();
	Debug::EndBlock();
}

function varDump($var){
	Debug::BeginBlock();
	debug_print_backtrace();
	Debug::EndBlock();
}

function log_add($message, $level = UC_LOG_INFO){
	Debug::BeginBlock();
	debug_print_backtrace();
	Debug::EndBlock();
}
?>