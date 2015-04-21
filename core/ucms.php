<?php
// The God Object of uCMS
class uCMS{
	private static $instance;
	private $databases;
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

		/**
		* @todo check php version
		*/
		global $databases;
		if( empty($databases) || !is_array($databases) ){
			/**
			* @todo install
			*/
			log_add(tr("install, no config"), UC_LOG_CRITICAL);
		}
		foreach ($databases as $dbName => $dbData) {
			try{
				$fields = array('server', 'user', 'password', 'name', 'port', 'prefix');
				foreach ($fields as $field) {
					if( !isset($dbData[$field]) ){
						/**
						* @todo install
						*/
						log_add(tr("install, wrong config"), UC_LOG_CRITICAL);
					}
				}
				$database = new DatabaseConnection($dbData["server"], 
												   $dbData["user"], 
												   $dbData["password"], 
												   $dbData["name"], 
												   $dbData["port"], 
												   $dbData["prefix"],
												   $dbName);	
				// echo "asdasdas".$database;
				/**
				* @todo check mysql version
				*/
				$this->databases[$dbName] = $database;
			}catch(Exception $e){
				if( $e->getCode() == 1045 ){
					/**
					* @todo install
					*/
					log_add(tr("install, wrong config"), UC_LOG_CRITICAL);
				}else{
					uCMS::exceptionHandler($e);
				}
			}
		}
		Session::getCurrent()->load();
		Settings::load();
		$lang = Settings::get('language');

		// TODO: language
		Language::getInstance()->load($lang);

		$enabledExtentions = Settings::get('extentions');
		$enabledExtentions = explode(',', $enabledExtentions);

		Extentions::create($enabledExtentions);
		Extentions::load();
		
	}

	public function runSite(){
		$url = new URLManager();
		//parse url, get page and get extention responsible for current page
		$templateData = false;
		if( $url->getCurrentAction() != ADMIN_ACTION ){
			$themeName = Settings::get('theme');
			if( empty($themeName) ) $themeName = DEFAULT_THEME;
			$templateData = Extentions::loadOnAction( $url->getCurrentAction() );
		}else{
			$themeName = ADMIN_THEME;
			$templateData = Extentions::loadOnAdminAction( $url->getCurrentAdminAction() );
		} // load admin panel

		try{
			Theme::setCurrent($themeName);
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

		Theme::getCurrent()->setTitle($title);
		Theme::getCurrent()->loadTemplate($template);
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

	public function reloadTheme($newTheme){
		try{
			Theme::setCurrent($newTheme);
		}catch(InvalidArgumentException $e){
			p("[@s]: ".$e->getMessage(), $newTheme);
		}catch(RuntimeException $e){
			p("[@s]: ".$e->getMessage(), $newTheme);
		}
	}

	public function getDatabase($name){
		if( isset($this->databases[$name]) ){
			return $this->databases[$name];
		}
	}
}
?>

