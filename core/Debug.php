<?php
namespace uCMS\Core;
class Debug{
	const LOG_OFF = 0;
	const LOG_CRITICAL = 1;
	const LOG_ERROR = 2;
	const LOG_WARNING = 3;
	const LOG_INFO = 4;
	const LOG_DEBUG = 5;
	private static $logFile;
	private static $logLevel = LOG_INFO;

	public static function Init(){
		register_shutdown_function( "uCMS\Core\Debug::ErrorHandler" );
		set_error_handler('uCMS\Core\Debug::ErrorHandler');
		ini_set('display_errors', 0);
		if(UCMS_DEBUG){ // Debug mode preparation
			error_reporting(E_ALL);
			ini_set('log_errors', 1);
			ini_set('error_log', ABSPATH.'content/debug.log');
			self::$logFile = ABSPATH.'content/ucms.log';
			self::$logLevel = LOG_DEBUG;
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

	public static function Log($message, $level = self::LOG_INFO){
		if(self::$logLevel > $level){
			switch ($level) {
				case self::LOG_DEBUG:
					$type = '[INFO]';
				break;

				case self::LOG_INFO:
					$type = '[INFO]';
				break;
	
				case self::LOG_WARNING:
					$type = '[WARNING]';
				break;
	
				case self::LOG_ERROR:
					$type = '[ERROR]';
				break;
	
				case self::LOG_CRITICAL:
					$type = '[CRITICAL]';
				break;
				
				default:
					$type = '[INFO]';
				break;
			}
			$host = Session::GetCurrent()->getHost();
			$outMessage = strftime("%Y-%m-%d %H:%M:%S", time())." [Host: $host] $type $message\n";
			$logFile = @fopen(self::$logFile, 'a');
			if($logFile){
				fwrite($logFile, $outMessage);
				fclose($logFile);
			}
			if($level === self::LOG_CRITICAL){
				echo "<pre>";
				p($outMessage);
				echo "</pre>";
				die;
			}
		}
	}

	public static function GetLogFile(){
		return self::$logFile;
	}

	public static function SetLogLevel($newLogLevel){
		self::$logLevel = $newLogLevel;

		if( $newLogLevel < self::LOG_OFF ){
			self::$logLevel = self::LOG_OFF;
		}

		if( $newLogLevel > self::LOG_DEBUG ){
			self::$logLevel = self::LOG_DEBUG;
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
		self::BeginBlock();
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
		self::EndBlock();
		echo "<br>";
		if($die) die;
	}
}
?>