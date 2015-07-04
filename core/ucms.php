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
		Debug::Init();
		
		$this->startLoadTimer();
		Session::Start();
		@mb_internal_encoding("UTF-8");
		register_shutdown_function(array($this, 'shutdown'));
		
		/**
		* @todo check php version
		*/

		if( version_compare(phpversion(), UCMS_MIN_PHP_VERSION, '<') ){
			Debug::Log(tr("Obsolete PHP"), UC_LOG_CRITICAL);
		}

		if( empty($GLOBALS['databases']) || !is_array($GLOBALS['databases']) ){
			/**
			* @todo install
			*/
			Debug::Log(tr("install, no config"), UC_LOG_CRITICAL);
		}
		foreach ($GLOBALS['databases'] as $dbName => $dbData) {
			try{
				$fields = array('server', 'user', 'password', 'name', 'port', 'prefix');
				foreach ($fields as $field) {
					if( !isset($dbData[$field]) ){
						/**
						* @todo install
						*/
						Debug::Log(tr("install, wrong config"), UC_LOG_CRITICAL);
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
					Debug::Log(tr("install, wrong config"), UC_LOG_CRITICAL);
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

	public static function ExceptionHandler($e){ // ?
   		Debug::ErrorHandler($e->getCode(), $e->getMessage(), $e->getFile(), $e->getLine());
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