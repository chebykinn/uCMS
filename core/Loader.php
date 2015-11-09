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
use uCMS\Core\Events\Event;
use uCMS\Core\Events\CoreEvents;
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
	* Method to load Database configuration
	*
	* This method finds and includes uCMS config file and prepares system for further loading. 
	*
	* @since 2.0
	* @param none
	* @return bool True if config file is found and loaded, false if not
	*/
	public static function GetConfiguration(){
		if( file_exists(uCMS::CONFIG_FILE) ){
			require_once uCMS::CONFIG_FILE;
			return true;
		}else if( file_exists('../'.uCMS::CONFIG_FILE) ){
			define("ABSPATH", dirname(__DIR__)."/");
			require_once '../'.uCMS::CONFIG_FILE;
			return true;
		}
		
		if( !defined("ABSPATH") ){
			define("ABSPATH", dirname(__DIR__)."/");
			define('UCMS_DEBUG', false);
			
			require_once 'core/autoload.php';
		}
		return false;
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
		@mb_internal_encoding("UTF-8");
		date_default_timezone_set("UTC");
		register_shutdown_function(array($this, 'shutdown'));
		$this->startLoadTimer();

		Debug::Init();
		Session::Start();

		// If we have saved language preference in session we are able to load language at this stage
		if( Language::IsSaved() ){
			Language::Init();
		}

		Form::Init();

		if( version_compare(phpversion(), uCMS::MIN_PHP_VERSION, '<') ){
			Theme::LoadTemplate('php-version');
			Debug::Log("Server's PHP is obsolete", Debug::LOG_CRITICAL);
			exit;
		}

		DatabaseConnection::Init();
		Settings::Load();
		
		// Load site language from database preference
		if( !Language::IsLoaded() ){
			Language::Init();
		}

		Cache::Init();
		Session::GetCurrent()->load();
		Extension::Init();
		$loadedEvent = new Event(CoreEvents::LOADED);
		$loadedEvent->fire();

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
		$currentAction =  Page::GetCurrent()->getAction();
		if ($currentAction === Page::INSTALL_ACTION ){
			$this->install();
		}
		$siteTitle = Settings::Get('site_title');
		if( empty($siteTitle) ) $siteTitle = tr("Untitled");
		if( $currentAction != ControlPanel::ACTION ){
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
		}catch(\InvalidArgumentException $e){
			p("[@s]: ".$e->getMessage(), $themeName);
		}catch(\RuntimeException $e){
			p("[@s]: ".$e->getMessage(), $themeName);
		}

		$isUsed = Extension::LoadOnAction( $currentAction );
		// Debug::PrintVar($isUsed);
		// exit;
		if( !$isUsed ){
			Theme::LoadErrorPage(uCMS::ERR_NOT_FOUND);
		}else{
			if( !Theme::GetCurrent()->IsTitleSet() ){
				Theme::GetCurrent()->setTitle($siteTitle);
			}
		}

		Block::Init();
		
		Theme::GetCurrent()->load();
	}

	/**
	* Method to start installation.
	*
	* This method is used to start installation if something is wrong with tables or configuration data.
	*
	* @since 2.0
	* @param none
	* @return void
	*/
	public function install(){
		$installer = new Installer();
		$installer->run();
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
		// If stage mark was left after installation we have to remove it
		if( Session::GetCurrent()->have('install-stage')
			&& Page::GetCurrent()->getAction() != Page::INSTALL_ACTION ){
			Session::GetCurrent()->delete('install-stage');
		}
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