<?php
/**
* This file contains main loader class.
*
* @author Ivan Chebykin
* @author ivan4b69@gmail.com
* @since 2.0
*/
namespace uCMS\Core;
use uCMS\Core\Database\DatabaseConnection;
use uCMS\Core\Language\Language;
use uCMS\Core\Extensions\Extension;
use uCMS\Core\Extensions\Theme;
use uCMS\Core\Admin\ControlPanel;
/**
* This class handles loading process.
* 
* Through this class all uCMS classes perform their initialization.
*/
class Loader{
	/**
	* @var Loader $instance Contains current instance of uCMS.
	*/
	private static $instance;
	/**
	* @var integer $startTime Contains main loading timer start time.
	*/
	private $startTime = 0;
	/**
	* @var integer $stopTime Contains main loading timer stop time.
	*/
	private $stopTime = 0;

	/**
	* Singleton method that provides access to the current instance of uCMS.
	*
	* @param none
	* @return Loader Current instance of uCMS.
	*/
	public static function GetInstance(){
		if ( is_null( self::$instance ) ){
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	* Main initialization method.
	*
	* This method initializes all uCMS classes, sets up a connection to database, settings, cache
	* and extensions. It is also starts the installation process if something is wrong 
	* with the settings.
	*
	* @param none
	* @return void
	*/
	public function init(){
		$this->startLoadTimer();

		Debug::Init();
		Session::Start();

		@mb_internal_encoding("UTF-8");
		register_shutdown_function(array($this, 'shutdown'));
		// TODO: Installation process
		if( version_compare(phpversion(), uCMS::MIN_PHP_VERSION, '<') ){
			Debug::Log(tr("Obsolete PHP"), Debug::LOG_CRITICAL);
		}
		DatabaseConnection::Init();
		Cache::Init();
		Session::GetCurrent()->load();
		Settings::Load();
		// TODO: Language
		Language::Init();

		Extension::Init();
		Block::Init();
	}

	/**
	* A method for running site.
	*
	* This method parses current action, initialiazes current theme or control panel, 
	* loads extensions that use this action and displays appropriate theme template.
	*
	* @param none
	* @return void
	*/
	public function runSite(){
		//parse url, get page and get extension responsible for current page
		$templateData = false;
		$siteTitle = Settings::Get('site_title');
		if( empty($siteTitle) ) $siteTitle = tr("Untitled");
		if( Page::GetCurrent()->getAction() != ControlPanel::ACTION ){
			$themeName = Settings::Get('theme');
			if( empty($themeName) ) $themeName = Theme::DEFAULT_THEME;
		}else{
		 	// load control panel
			ControlPanel::Init();
			$siteTitle = ControlPanel::TITLE;
			$themeName = ControlPanel::THEME;
		}

		try{
			Theme::SetCurrent($themeName);
		}catch(InvalidArgumentException $e){
			p("[@s]: ".$e->getMessage(), $themeName);
		}catch(RuntimeException $e){
			p("[@s]: ".$e->getMessage(), $themeName);
		}

		$isUsed = Extension::LoadOnAction( Page::GetCurrent()->getAction() );
		// Debug::PrintVar($isUsed);
		// exit;
		if( !$isUsed ){
			Theme::LoadErrorPage(uCMS::ERR_NOT_FOUND);
		}else{
			if( !Theme::GetCurrent()->IsTitleSet() ){
				Theme::GetCurrent()->setTitle($siteTitle);
			}
		}
		
		Theme::GetCurrent()->load();
	}

	/**
	* Shutdown method.
	*
	* This method is used for uCMS classes to do their shutting down stuff.
	* At this time session data stores to database, extensions do their shutdown processes.
	* After all of that uCMS closes a connection to database.
	*
	* @param none
	* @return void
	*/
	public function shutdown(){
		Extension::Shutdown();
		$this->stopLoadTimer();
		Session::GetCurrent()->save();
		DatabaseConnection::Shutdown();
	}

	/**
	* Starts loading timer.
	*
	* This method is used in the beginning of initialization process.
	*
	* @param none
	* @return void
	*/
	private function startLoadTimer(){
		$currentTime = microtime();
		$currentTime = explode(" ", $currentTime);
		$this->startTime = $currentTime[1] + $currentTime[0];
	}

	/**
	* Stops loading timer.
	*
	* This method is used in the end of initialization process.
	*
	* @param none
	* @return void
	*/
	private function stopLoadTimer(){
		$currentTime = microtime();
		$currentTime = explode(" ",$currentTime);
		$this->stopTime = $currentTime[1] + $currentTime[0];
	}

	/**
	* Takes the value of loading timer.
	*
	* This method takes the value of loading timer when it was called.
	*
	* @api
	* @since 2.0
	* @param none
	* @return integer Loading time at the moment of calling this method
	*/
	public function getLoadTime(){
		$currentTime = microtime();
		$currentTime = explode(" ",$currentTime);
		$stopTime = $this->stopTime > 0 ? $this->stopTime : $currentTime[1] + $currentTime[0];
		return number_format( ($stopTime - $this->startTime), 3 );
	}

}
?>