<?php
// The God Object of uCMS
class uCMS{
	private static $instance;
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

		Debug::Init();
		Session::Start();

		@mb_internal_encoding("UTF-8");
		register_shutdown_function(array($this, 'shutdown'));
		
		/**
		* @todo check php version
		*/

		if( version_compare(phpversion(), UCMS_MIN_PHP_VERSION, '<') ){
			Debug::Log(tr("Obsolete PHP"), UC_LOG_CRITICAL);
		}
		DatabaseConnection::Init();
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
		$siteTitle = Settings::Get('site_title');
		if( empty($siteTitle) ) $siteTitle = tr("Untitled");
		if( Page::GetCurrent()->getAction() != ADMIN_ACTION ){
			$themeName = Settings::Get('theme');
			if( empty($themeName) ) $themeName = DEFAULT_THEME;
		}else{
		 	// load control panel
			ControlPanel::Init();
			$siteTitle = ControlPanel::TITLE;
			$themeName = ADMIN_THEME;
		}

		try{
			Theme::SetCurrent($themeName);
		}catch(InvalidArgumentException $e){
			p("[@s]: ".$e->getMessage(), $themeName);
		}catch(RuntimeException $e){
			p("[@s]: ".$e->getMessage(), $themeName);
		}

		$isUsed = Extensions::LoadOnAction( Page::GetCurrent()->getAction() );
		// Debug::PrintVar($isUsed);
		// exit;
		if( !$isUsed ){
			Theme::LoadErrorPage(404);
		}else{
			if( !Theme::GetCurrent()->IsTitleSet() ){
				Theme::GetCurrent()->setTitle($siteTitle);
			}
		}
		
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

}
?>