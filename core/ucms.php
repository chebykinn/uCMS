<?php
// The God Object of uCMS
class uCMS{
	private static $instance;
	private $database;
	private $settings;
	private $language;
	private $tables;
	private $theme;
	private $extentions;
	private $startTime = 0;
	private $stopTime = 0;

	public static function getInstance(){
		if ( is_null( self::$instance ) ){
			self::$instance = new self();
		}
		return self::$instance;
	}

	public function init(){
		$this->startLoadTimer();
		Session::start();
		register_shutdown_function( "uCMS::errorHandler" );
		set_error_handler('uCMS::errorHandler');
		ini_set('display_errors', 0);
		@mb_internal_encoding("UTF-8");

		if(UCMS_DEBUG){ // Debug mode preparation
			error_reporting(E_ALL);
			ini_set('log_errors', 1);
			ini_set('error_log', CONTENT_PATH.'debug.log');
		}else{
			error_reporting(E_ALL ^ (E_DEPRECATED | E_NOTICE | E_STRICT));
		}
		// TODO: check php version
		$this->setTables();
		
		try{
			$this->database = new DatabaseConnection(DB_SERVER, DB_USER, DB_PASSWORD, DB_NAME, UC_PREFIX);
			
			// TODO: check mysql version
		}catch(Exception $e){
			if($e->getCode() == 1045){
				// TODO: install
				echo "install";
			}else{
				uCMS::exceptionHandler($e);
			}
		}
		Session::getCurrent()->load();
		$this->settings = new Settings();
		$this->settings->load();
		$lang = $this->settings->get('language');

		// TODO: language
		$this->language = new Language($lang);

		$enabledExtentions = $this->settings->get('extentions');
		$enabledExtentions = explode(',', $enabledExtentions);

		$this->extentions = new ExtentionController();
		$this->extentions->create($enabledExtentions);
		$this->extentions->load();
		
	}

	public function runSite(){
		$url = new URLManager();
		//parse url, get page and get extention responsible for current page
		$templateData = false;
		if( $url->getCurrentAction() != ADMIN_ACTION ){
			$themeName = $this->settings->get('theme');
			if( empty($themeName) ) $themeName = DEFAULT_THEME;
			$templateData = $this->extentions->loadOnAction( $url->getCurrentAction() );
		}else{
			$themeName = ADMIN_THEME;
			$templateData = $this->extentions->loadOnAdminAction( $url->getCurrentAdminAction() );
		} // load admin panel

		try{
			$this->theme = new Theme($themeName);
		}catch(InvalidArgumentException $e){
			p("[@s]: ".$e->getMessage(), $themeName);
		}catch(RuntimeException $e){
			p("[@s]: ".$e->getMessage(), $themeName);
		}
		
		if( empty($templateData) || !is_array($templateData) ){
			$title    = tr("404 Not Found");
			$template = ERROR_TEMPLATE_NAME;
		}else{
			$title    = empty($templateData['title'])    ? tr("Untitled")      : $templateData['title'];
			$template = empty($templateData['template']) ? ERROR_TEMPLATE_NAME : $templateData['template'];

		}

		$this->theme->setTitle($title);
		$this->theme->loadTemplate($template);
		$this->shutdown();
	}

	public function shutdown(){
		$this->stopLoadTimer();
		Session::getCurrent()->save();
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
	public static function errorHandler($errno = "", $errstr = "", $errfile = "", $errline = ""){
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
		switch ($errno) {
			case E_RECOVERABLE_ERROR:
				echo "<h3>PHP Catchable Fatal Error</h3>";
			break;
			
			case E_NOTICE:
				echo "<h3>PHP Notice</h3>";
			break;

			case E_WARNING:
				echo "<h3>PHP Warning</h3>";
			break;

			case E_ERROR:
				echo "<h3>PHP Fatal Error</h3>";
				$die = true;
			break;

			case E_PARSE:
				echo "<h3>PHP Parse Error</h3>";
				$die = true;
			break;

			case E_COMPILE_ERROR:
				echo "<h3>PHP Compile Fatal Error</h3>";
				$die = true;
			break;

			case E_DEPRECATED:
				echo "<h3>PHP Deprecated Message</h3>";
			break;

			case E_STRICT:
				echo "<h3>PHP Strict Standars</h3>";
			break;

			default:
				echo "<h3>PHP Error $errno</h3>";
			break;
		}
		if(!UCMS_DEBUG){
			echo "<pre>";
			echo "$errstr in <b>$errfile</b> on line <b>$errline</b>";
			echo "</pre>";
		}else{
			echo "<pre>";
			echo "$errstr in <b>$errfile</b> on line <b>$errline</b><br>";
			echo '<p style="font-size: 8pt; padding: 10px;">';
			echo debug_print_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
			echo '</p>';
			echo "</pre>";
		}
		echo "<br>";
		if($die) die;
	}

	public static function exceptionHandler($e){
   		uCMS::errorHandler($e->getCode(), $e->getMessage(), $e->getFile(), $e->getLine());
	}

	public function getDatabase(){
		return $this->database;
	}

	public function getTable($name){
		foreach ($this->tables as $table) {
			if($table === $name){
				return $this->database->getPrefix().$table;
			}
		}
	}

	public function setTables(){
		$this->tables = array('settings');
	}


	// ?
	public function getSettings(){
		return $this->settings;
	}

	private function startLoadTimer(){
		$currentTime = microtime();
		$currentTime = explode(" ", $currentTime);
		$this->startTime = $currentTime[1] + $currentTime[0];
	}

	private function stopLoadTimer(){
		$currentTime = microtime();
		$currentTime = explode(" ",$currentTime);
		$this->stopTime = $currentTime[1] + $currentTime[0];
	}

	public function getLoadTime(){
		$currentTime = microtime();
		$currentTime = explode(" ",$currentTime);
		$stopTime = $this->stopTime > 0 ? $this->stopTime : $currentTime[1] + $currentTime[0];
		return number_format( ($stopTime - $this->startTime), 3 );
	}

	public function getLanguage(){
		return $this->language;
	}

	public function getCurrentTheme(){
		return $this->theme;
	}

	public function getExtentions(){
		return $this->extentions;
	}

	public function reloadTheme($newTheme){
		try{
			$this->theme = new Theme($newTheme);
		}catch(InvalidArgumentException $e){
			p("[@s]: ".$e->getMessage(), $themeName);
		}catch(RuntimeException $e){
			p("[@s]: ".$e->getMessage(), $themeName);
		}
	}
}
?>

