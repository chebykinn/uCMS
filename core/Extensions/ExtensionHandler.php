<?php
namespace uCMS\Core\Extensions;
use uCMS\Core\Debug;
use uCMS\Core\Setting;
use uCMS\Core\Page;
use uCMS\Core\Session;
use uCMS\Core\Installer;
use uCMS\Core\Notification;
use uCMS\Core\Admin\ControlPanel;
use uCMS\Core\Database\Query;
use uCMS\Core\Database\DatabaseConnection;
use uCMS\Core\uCMS;
use uCMS\Core\Object;

class ExtensionHandler extends Object{

	const INFO = 'extension.info';
	const PATH = 'content/extensions/';
	const CORE_PATH = 'core/content/extensions/';
	const NEED_USER_INPUT = 10;
	const DONE_INSTALL = 20;

	private static $list = [];
	private static $usedActions = [];
	private static $usedAdminActions = [];
	private static $defaultList = ['users', 'admin', 'filemanager', 'menus', 'entries', 'comments', 'search'];
	
	final public static function Init(){
		$setting = Setting::Get(Setting::EXTENSIONS);
		$externalList = unserialize($setting);
		if( !$externalList ) $externalList = [];
		$extensions = array_merge(self::$defaultList, $externalList);
		$extensionActions = $extensionAdminActions = [];
		foreach ($extensions as $extension) {
			try{
				$extensionClass = self::GetExtensionClass($extension);
				if( !empty($extensionClass) ){
					// If we found extension, we are trying to create it
					$e = self::$list[$extension] = new $extensionClass($extension);
					// After that we're invoking onLoad event
					$e->onLoad();

					// If extension has actions, we will save them
					$extensionActions = $e->getActions();
					$extensionAdminActions = $e->getAdminActions();
					self::$usedActions = array_merge(self::$usedActions, $extensionActions);
					self::$usedAdminActions = array_merge(self::$usedAdminActions, $extensionAdminActions);
				}else{
					Debug::Log(self::Translate("Unable to find extension: @s", $extension), Debug::LOG_ERROR, new self());
				}
			}catch(\Exception $e){
				Debug::Log(self::Translate("Can't load extension: @s, error: @s", $extension, $e->getMessage()), Debug::LOG_ERROR, new self());
			}
		}
		self::$usedActions = array_unique(self::$usedActions);
		self::$usedAdminActions = array_unique(self::$usedAdminActions);
	}

	final private static function BuildExtensionClassName($name){
		$namespace = self::IsDefault($name) ? __NAMESPACE__ : "uCMS\\Extensions";
		$extensionClass = "$namespace\\$name\\$name";
		return $extensionClass;
	}

	final public static function GetExtensionClass($name){
		if( !self::IsExtension($name) ) return "";
		$extensionClass = self::BuildExtensionClassName($name);
		if( class_exists($extensionClass) ){
			return $extensionClass;
		}
		return "";
	}

	final public static function LoadOnAction($action){
		$isUsed = false;
		$cPanelResult = array('isUsed' => false);
		foreach (self::$list as $name => $extension) {
			if( ControlPanel::IsActive() ){
				if( !$cPanelResult['isUsed'] ){
					$cPanelResult = ControlPanel::CheckAction($extension->getAdminActions());
					$isUsed = $cPanelResult['isUsed'];
					if( $isUsed && !$cPanelResult['default'] ){
						$extension->onAdminAction($cPanelResult['action']);
					}
				}
			}else{
				if( !in_array($action, self::$usedActions) ){
					$action = Page::OTHER_ACTION;
				}
				if( in_array($action, $extension->getActions()) ){
					$extension->onAction($action);
					$isUsed = true;
				}
			}
		}
		return $isUsed;
	}

	final public static function CheckInstall(){
		$doStage = false;
		$installList = [];
		foreach (self::$list as $name => $extension) {
			$isNeeded = $extension->onInstall(Installer::CHECK_STAGE);
			if( $isNeeded ){ // If extension signalled us that it needs install, we will add it to queue.
				$installList[] = $name;
				$doStage = true;
			}
		}
		Session::GetCurrent()->set('install_list', serialize($installList));

		return $doStage;
	}

	final public static function Install($stage){
		$installList = [];
		$savedList = unserialize(Session::GetCurrent()->get('install_list'));
		if( is_array($savedList) ) $installList = $savedList;
		$i = 0;

		foreach ($installList as $name) {
			$extension = self::$list[$name];
			$result = $extension->onInstall($stage);
			if( $stage === Installer::PREPARE_STAGE ){
				if( $result === ExtensionHandler::NEED_USER_INPUT ){
					// Extension can request for user input, if so we should exit prepare stage and move to print stage.
					break;
				}

				if( $result === ExtensionHandler::DONE_INSTALL ){
					unset($installList[$i]);
					Session::GetCurrent()->set('install_list', serialize($installList));
				}
			}

			if( $stage == Installer::PRINT_STAGE ){
				break;
			}
			$i++;
		}
		if( empty($installList) ){
			Session::GetCurrent()->delete('install_list');
			Installer::GetInstance()->switchStage(Installer::CHECK_STAGE);
		}
	}

	/**
	* Check if extension $name is default.
	*
	* This method allows you to check if given extension is default.
	*
	* @since 2.0
	* @param $name The name of given extension.
	* @return bool True if extension is default, false if not.
	*/
	final public static function IsDefault($name){
		return in_array($name, self::$defaultList);
	}

	final public static function GetUsedAdminActions(){
		return self::$usedAdminActions;
	}

	final public static function GetUsedActions(){
		return self::$usedActions;
	}

	final public static function Get($name){
		if( !empty(self::$list[$name]) ){
			return self::$list[$name];
		}
		if( self::IsExists($name) && self::IsExtension($name) ){
			$extension = new Extension($name);
			return $extension;
		}
		return NULL;
	}

	final public static function IsLoaded($name){
		return (!empty(self::$list[$name]) && is_object(self::$list[$name]));
	}

	final public static function IsExists($name){
		return in_array($name, self::GetList());
	}

	final public static function IsExtension($name){
		if( !is_object($name) ){
			// If it is default extension we are checking extension file in core directory.
			if( in_array($name, self::$defaultList) ){
				return require_once(ABSPATH.self::CORE_PATH."$name/extension.php");
			}

			$dataExists = (
				is_dir(ABSPATH.self::PATH.$name) 
				&& file_exists(ABSPATH.self::PATH.$name.'/extension.php') 
				&& file_exists(ABSPATH.self::PATH.$name.'/'.self::INFO)
			);
		
			// Otherwise, we check if extension have its class, derived from Extension and implements ExtensionInterface
			if ( $dataExists ){
				include_once(ABSPATH.self::PATH.$name.'/extension.php');
				$class = self::BuildExtensionClassName($name);
				return ( 
					class_exists($class) 
					&& is_subclass_of($class, __NAMESPACE__."\\Extension") 
					&& in_array(__NAMESPACE__."\\ExtensionInterface", class_implements($class))
				);
	
			}
			return false;
		}else{
			return is_subclass_of($name, __NAMESPACE__."\\Extension");
		}
	}

	final public static function GetLoaded(){
		$names = array_keys(self::$list);
		return $names;
	}

	final public static function GetList(){
		$names = [];
		$extdirs = file_exists(ABSPATH.self::PATH) ? scandir(ABSPATH.self::PATH) : [];
		$directories = array_merge(scandir(ABSPATH.self::CORE_PATH), $extdirs);
		foreach ($directories as $extension) {
			if( self::IsExtension($extension) ){
				$names[] = $extension;
			}
		}
		return $names;
	}

	final public static function GetExtensionByAdminAction($action){
		foreach (self::$list as $name => $extension) {
			if( in_array($action, $extension->getAdminActions()) ){
				return $extension;
			}
		}
	}

	final public static function GetExtensionByAction($action){
		foreach (self::$list as $name => $extension) {
			if( in_array($action, $extension->getActions()) ){
				return $extension;
			}
		}
		return NULL;
	}

	final public static function Delete($name){
		if( self::IsDefault($name) || !self::IsExtension($name) ){
			$message = new Notification($this->tr("Unable to delete extension \"@s\"", $name), Notification::ERROR);
			$message->add();
			return false;
		}
		self::Disable($name);
		//remove dir

		Notification::ClearPending();
		$message = new Notification($this->tr("Extension \"@s\" was successfully deleted", $name), Notification::SUCCESS);
		$message->add();
		return true;
	}

	final public static function Enable($name){
		if( self::IsLoaded($name) ){
			$message = new Notification($this->tr("Extension \"@s\" is already enabled", $name), Notification::ERROR);
			$message->add();
			return false;
		}
		$exists = false;

		if( file_exists(self::PATH.$name.'/extension.php') ){
			include self::PATH.$name.'/extension.php';

			if( class_exists($name) ){
				try{
					self::$list[$name] = new $name($name);
					$exists = true;
				}catch(Exception $e){
					Debug::Log($this->tr("Can't load extension: @s, error: @s", $extension, $e->getMessage()), Debug::LOG_ERROR, $this);
				}
			}
		}
		if($exists){
			$extensions = array();
			foreach (self::$list as $name => $extension) {
				$extensions[] = $name;
			}
			$extensions = implode(",", $extensions);
			Setting::UpdateValue(Setting::EXTENSIONS, $extensions, $this);
			$message = new Notification($this->tr("Extension \"@s\" was successfully enabled", $name), Notification::SUCCESS);
			$message->add();
			return true;
		}
		$message = new Notification($this->tr("Extension \"@s\" doesn't exists", $name), Notification::ERROR);
		$message->add();
		return false;
		/**
		* @todo event or something
		*/


	}

	final public static function Disable($name){
		if( !self::IsLoaded($name) ){
			$message = new Notification($this->tr("Extension \"@s\" is already disabled", $name), Notification::ERROR);
			$message->add();
			return false;
		}
		if( self::IsDefault($name) ){
			$message = new Notification($this->tr("Extension \"@s\" can't be disabled", $name), Notification::ERROR);
			$message->add();
			return false;
		}
		unset(self::$list[$name]);
		/**
		* @todo event or something
		*/
		$extensions = array();
		foreach (self::$list as $name => $extension) {
			$extensions[] = $name;
		}
		$extensions = implode(",", $extensions);
		Setting::UpdateValue(Setting::EXTENSIONS, $extensions, $this);
		$message = new Notification($this->tr("Extension \"@s\" was successfully disabled", $name), Notification::SUCCESS);
		$message->add();
		return true;
	}

	final public static function Add($name){
		var_dump($name);
		$message = new Notification($this->tr("Extension \"@s\" was successfully added", $name), Notification::SUCCESS);
		$message->add();
		return true;
	}

	final public static function Shutdown(){
		foreach (self::$list as $name => $extension) {
			$extension->onShutdown();
		}
	}

	final public static function GetClasses(){
		return [
			'AbstractExtension',
			'Extension',
			'ExtensionHandler',
			'ExtensionInterface',
			'Theme',
			'ThemeHandler'
		];
	}
}
?>