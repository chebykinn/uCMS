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
		register_shutdown_function('uCMS\\Core\\Debug::ErrorHandler');
		set_error_handler('uCMS\\Core\\Debug::ErrorHandler');
		ini_set('display_errors', 0);
		self::$logFile = ABSPATH.'content/ucms.log';
		if( !file_exists(self::$logFile) ){
			touch(self::$logFile);
		}
		if(UCMS_DEBUG){ // Debug mode preparation
			$debugFile = ABSPATH.'content/debug.log';
			error_reporting(E_ALL);
			ini_set('log_errors', 1);
			ini_set('error_log', $debugFile);
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

	public static function PrintVar($var, $raw = false){
		self::BeginBlock();
		// debug_print_backtrace();
		if( !$raw )
			var_dump($var);
		else
			echo $var;
		self::EndBlock();
	}

	public static function Log($message, $level = self::LOG_INFO){
		if(self::$logLevel > $level){
			switch ($level) {
				case self::LOG_DEBUG:
					$type = '[DEBUG]';
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
			$owner = Tools::GetCurrentOwner();
			// strftime("%Y-%m-%d %H:%M:%S", time())
			$outMessage = Tools::FormatTime(time(), "Y-m-d H:i:s")." [Host: $host] $type [$owner] $message\n";
			$logFile = @fopen(self::$logFile, 'a');
			if($logFile){
				fwrite($logFile, $outMessage);
				fclose($logFile);
			}
			if( $level === self::LOG_CRITICAL && UCMS_DEBUG ){
				echo "<pre>";
				p($outMessage);
				echo "</pre>";
				die;
			}
		}
	}

	/**
	* Get prepared array of log messages.
	*
	* This method returns an array of structured log messages, parsed from log file.
	*
	* @since 2.0
	* @param none
	* @return array An array of log messages.
	*/
	public static function GetLogMessages(){
		// TODO: offset and limit
		$journalLines = @file(self::GetLogFile());
		if(!empty($journalLines)){
			$journalLines = array_reverse($journalLines);
		}else{
			$journalLines = array();
		}
		$dateOffset = 0;
		$hostOffset = 3;
		$typeOffset = 4;
		$ownerOffset = 5;
		$messageOffset = 6;
		$headerLimit = 7;
		$messages = array();
		$count = count($journalLines);
		$i = 0;
		foreach ($journalLines as $line) {
			$id = $count-$i;
			$data = explode(" ", $line, $headerLimit);
			$message = array(
				"id" => $id,
				"type" => preg_replace("/\[|\]/", "", $data[$typeOffset]),
				"text" => htmlspecialchars($data[$messageOffset]),
				"host" => substr($data[$hostOffset], 0, -1),
				"owner" => htmlspecialchars(preg_replace("/\[|\]/", "", $data[$ownerOffset])),
				"date" => $data[$dateOffset].' '.$data[$dateOffset+1]
			);
			$messages[] = $message;
			$i++;
		}
		return $messages;
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
	* @version 2.0
	* @return void
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
				$errTitle = "PHP Catchable Fatal Error";
			break;
			
			case E_NOTICE:
				$errTitle = "PHP Notice";
			break;

			case E_WARNING:
				$errTitle = "PHP Warning";
			break;

			case E_ERROR:
				$errTitle = "PHP Fatal Error";
				$die = true;
			break;

			case E_PARSE:
				$errTitle = "PHP Parse Error";
				$die = true;
			break;

			case E_COMPILE_ERROR:
				$errTitle = "PHP Compile Fatal Error";
				$die = true;
			break;

			case E_DEPRECATED:
				$errTitle = "PHP Deprecated Message";
			break;

			case E_STRICT:
				$errTitle = "PHP Strict Standars";
			break;

			default:
				$errTitle = "PHP Error $errno";
			break;
		}
		echo $errTitle;
   		echo '</h2>';
   		$errorMsg = "$errstr in <b>$errfile</b> on line <b>$errline</b>";
		if(!UCMS_DEBUG){
			echo $errorMsg;
		}else{
			echo "$errorMsg<br>";
			echo '<p style="font-size: 8pt; padding: 10px;">';
			echo debug_print_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
			echo '</p>';
		}
		self::Log(tr($errTitle.': '.strip_tags($errorMsg)), self::LOG_ERROR);
		self::EndBlock();
		echo "<br>";
		if($die) die;
	}
}
?>