<?php
namespace uCMS\Core;
use uCMS\Core\Extensions\ThemeHandler;

class Debug{
	const LOG_OFF = 0;
	const LOG_CRITICAL = 1;
	const LOG_ERROR = 2;
	const LOG_WARNING = 3;
	const LOG_INFO = 4;
	const LOG_DEBUG = 5;
	private static $logFile;
	private static $logLevel = LOG_INFO;
	private static $blocksAmount = 0;

	public static function Init(){
		register_shutdown_function('uCMS\\Core\\Debug::ErrorHandler');
		set_error_handler('uCMS\\Core\\Debug::ErrorHandler');
		ini_set('display_errors', 0);
		self::$logFile = ABSPATH.'content/ucms.log';
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
		$css = ThemeHandler::GetTemplate('ucms.css', false, true);
		$style = file_get_contents($css);
		$size = 200;
		$position = $size*self::$blocksAmount;
		echo '<style type="text/css">'.$style.'
		.ucms-debug'.self::$blocksAmount.'{ top: '.$position.'; }</style>
		<pre class="ucms-debug ucms-debug'.self::$blocksAmount.'">';
		self::$blocksAmount++;
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

	public static function Log($message, $level = self::LOG_INFO, $logFile = ""){
		if( empty($logFile) ){
			$logFile = self::$logFile;
		}
		$hasFile = file_exists($logFile);
		if( !file_exists($logFile) && file_exists(dirname($logFile)) && is_writable(dirname($logFile)) ){
			$hasFile = touch($logFile);
		}

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

			// If error is repeating rapidly this will prevent logs from bloating
			$lastMessage = self::GetLastMessage();
			if( !empty($lastMessage) && $lastMessage['text'] !== $message && $hasFile ){
				$logHandle = @fopen($logFile, 'a');
				if($logHandle){
					fwrite($logHandle, $outMessage);
					fclose($logHandle);
				}
			}
			if( $level === self::LOG_CRITICAL && UCMS_DEBUG ){
				echo "<pre>";
				p($outMessage);
				echo "</pre>";
				die;
			}
		}
	}

	public static function GetLogMessage($rawLine){
		$dateOffset = 0;
		$hostOffset = 3;
		$typeOffset = 4;
		$ownerOffset = 5;
		$messageOffset = 6;
		$headerLimit = 7;
		$data = explode(" ", $rawLine, $headerLimit);
		$type = !empty($data[$typeOffset]) ? preg_replace("/\[|\]/", "", $data[$typeOffset]) : 'ERROR';
		$text = !empty($data[$messageOffset]) ? htmlspecialchars($data[$messageOffset]) : tr('Unknown error');
		$host = !empty($data[$hostOffset]) ? substr($data[$hostOffset], 0, -1) : tr('Unknown host');
		$owner = !empty($data[$ownerOffset]) ? htmlspecialchars(preg_replace("/\[|\]/", "", $data[$ownerOffset])) : 'core';
		$date = (!empty($data[$dateOffset]) && !empty($data[$dateOffset+1]))
		? $data[$dateOffset].' '.$data[$dateOffset+1] : Tools::FormatTime(time(), "Y-m-d H:i:s");
		$message = [
			"type" => $type,
			"text" => $text,
			"host" => $host,
			"owner" => $owner,
			"date" => $date
		];
		return $message;
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
		$count = count($journalLines);
		$i = 0;
		$messages = [];
		foreach ($journalLines as $line) {
			$id = $count-$i;
			$message = self::GetLogMessage($line);
			$message['id'] = $id;
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
		$errorMsg = "<p>$errstr in <b>$errfile</b> on line <b>$errline</b></p>";
		if(!UCMS_DEBUG){
			echo $errorMsg;
		}else{
			echo "$errorMsg<br>";
			echo '<p class="ucms-debug-trace">';
			echo debug_print_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
			echo '</p>';
		}
		self::Log(tr($errTitle.': '.strip_tags($errorMsg)), self::LOG_ERROR);
		self::EndBlock();
		echo "<br>";
		if($die) die;
	}

	public static function GetLastMessage($raw = false, $logFile = ""){
		$line = '';

		if( empty($logFile) ){
			$logFile = self::$logFile;
		}

		if( !file_exists($logFile) ) return "";

		$f = fopen(self::$logFile, 'r');
		$cursor = -1;
		
		fseek($f, $cursor, SEEK_END);
		$char = fgetc($f);
		
		// Trim trailing newline chars of the file
		while ($char === "\n" || $char === "\r") {
			fseek($f, $cursor--, SEEK_END);
			$char = fgetc($f);
		}
		
		// Read until the start of file or first newline char
		while ($char !== false && $char !== "\n" && $char !== "\r") {
			// Prepend the new char
			$line = $char . $line;
			fseek($f, $cursor--, SEEK_END);
			$char = fgetc($f);
		}
		if( !$raw ){
			return self::GetLogMessage($line);
		}
		return $line;
	}

	public static function ClearLog($logFile = ""){
		if( empty($logFile) ){
			$logFile = self::$logFile;
		}
		if( file_exists($logFile) ){
			unlink($logFile);
			return true;
		}
		return false;
	}
}
?>