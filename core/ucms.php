<?php
// The God Object of uCMS
class uCMS{
	private static $instance;
	private $databases;
	private $startTime = 0;
	private $stopTime = 0;

	public static function GetInstance(){
		if ( is_null( self::$instance ) ){
			self::$instance = new self();
		}
		return self::$instance;
	}

	public function init(){
		$this->startLoadTimer();
		Session::Start();
		register_shutdown_function( "uCMS::errorHandler" );
		register_shutdown_function(array($this, 'shutdown'));
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

		if( version_compare(phpversion(), UCMS_MIN_PHP_VERSION, '<') ){
			log_add(tr("Obsolete PHP"), UC_LOG_CRITICAL);
		}

		if( empty($GLOBALS['databases']) || !is_array($GLOBALS['databases']) ){
			/**
			* @todo install
			*/
			log_add(tr("install, no config"), UC_LOG_CRITICAL);
		}
		foreach ($GLOBALS['databases'] as $dbName => $dbData) {
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
				$database = new DatabaseConnection(
					$dbData["server"], 
					$dbData["user"], 
					$dbData["password"], 
					$dbData["name"], 
					$dbData["port"], 
					$dbData["prefix"],
					$dbName
				);
				
				/**
				* @todo check mysql version
				*/
				$this->databases[$dbName] = $database;
			}catch(Exception $e){
				if( $e->getCode() == 1045 || $e->getCode() == 1049 ){
					/**
					* @todo install
					*/
					log_add(tr("install, wrong config"), UC_LOG_CRITICAL);
				}else{
					uCMS::ExceptionHandler($e);
				}
			}
		}
		unset($GLOBALS['databases']); // We don't want to have global variables, so we delete this

		Cache::Init();
		Session::GetCurrent()->load();
		Settings::Load();
		$lang = Settings::Get('language');

		// TODO: language
		Language::GetCurrent()->load($lang);

		$enabledExtensions = Settings::Get('extensions');
		$enabledExtensions = explode(',', $enabledExtensions);

		Extensions::Create($enabledExtensions);
		Extensions::Load();
		
	}

	public function runSite(){
		//parse url, get page and get extension responsible for current page
		$templateData = false;

		if( empty($siteTitle) ) $siteTitle = tr("Untitled");
		if( Page::GetCurrent()->getAction() != ADMIN_ACTION ){
			$themeName = Settings::Get('theme');
			if( empty($themeName) ) $themeName = DEFAULT_THEME;
		}else{
		 	// load control panel
			ControlPanel::Init();
			$themeName = ADMIN_THEME;
		}
		$templateData = Extensions::LoadOnAction( Page::GetCurrent()->getAction() );

		try{
			Theme::SetCurrent($themeName);
		}catch(InvalidArgumentException $e){
			p("[@s]: ".$e->getMessage(), $themeName);
		}catch(RuntimeException $e){
			p("[@s]: ".$e->getMessage(), $themeName);
		}

		Theme::GetCurrent()->setTitle($templateData['title']);
		Theme::GetCurrent()->setAction($templateData['template']);
		Theme::GetCurrent()->load();
	}

	public function shutdown(){
		Extensions::Shutdown();
		$this->stopLoadTimer();
		Session::GetCurrent()->save();
		DatabaseConnection::GetDefault()->shutdown(); //multiple
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
		begin_debug_block();
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
		end_debug_block();
		echo "<br>";
		if($die) die;
	}

	public static function ExceptionHandler($e){
   		uCMS::ErrorHandler($e->getCode(), $e->getMessage(), $e->getFile(), $e->getLine());
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
			Theme::SetCurrent($newTheme);
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