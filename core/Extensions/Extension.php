<?php
namespace uCMS\Core\Extensions;
use uCMS\Core\Debug;
use uCMS\Core\Settings;
use uCMS\Core\Page;
use uCMS\Core\Notification;
use uCMS\Core\Admin\ControlPanel;
use uCMS\Core\Database\Query;
use uCMS\Core\Database\DatabaseConnection;
use uCMS\Core\uCMS;
class Extension extends AbstractExtension{
	const INFO = 'extension.info';
	const PATH = 'content/extensions/';
	const CORE_PATH = 'core/content/extensions/';

	private $loadAfter = NULL;
	private $includes;
	private $actions;
	private $admin;
	private $sidebarPosition;
	private $adminPages = NULL;
	private static $list = array();
	private static $usedActions;
	private static $usedAdminActions;
	private static $defaultList;

	final public function __construct($name){
		$this->name = $name;
		$this->loadInfo();

		$this->checkCoreVersion();
		if( is_array($this->includes) ){
			foreach ($this->includes as $include) {
				$this->includeFile($include);
			}
		}
	}

	protected function loadInfo(){

		$encodedInfo = @file_get_contents($this->getExtensionInfoPath());

		$decodedInfo = json_decode($encodedInfo, true);
		$checkRequiredFields = empty($decodedInfo['version']) || empty($decodedInfo['coreVersion']);
		if( $decodedInfo === NULL || $checkRequiredFields ){
			Debug::Log(tr("Can't get extension information @s", $this->name), Debug::LOG_ERROR);
			throw new \InvalidArgumentException("Can't get extension information");
		}
		$this->version = $decodedInfo['version'];
		$this->coreVersion = $decodedInfo['coreVersion'];

		$this->dependencies = !empty($decodedInfo['dependencies']) ? $decodedInfo['dependencies'] : "";
		$this->info         = !empty($decodedInfo['info'])         ? $decodedInfo['info']         : "";
		$this->loadAfter    = !empty($decodedInfo['loadAfter'])    ? $decodedInfo['loadAfter']    : "";
		$this->includes     = !empty($decodedInfo['includes'])     ? $decodedInfo['includes']     : "";
		$this->actions      = !empty($decodedInfo['actions'])      ? $decodedInfo['actions']      : "";
		$this->admin        = !empty($decodedInfo['admin'])        ? $decodedInfo['admin']        : array();
		$this->adminPages   = !empty($decodedInfo['adminPages'])   ? $decodedInfo['adminPages']   : "";
		foreach ($this->admin as $key => &$item) {
			if( is_array($item) && count($item) == 2 ){ // if sidebar position is set
				if( empty($item[0]) ){
					$item[0] = $key;
					if( strpos($item[0], "separator" ) !== false ){
						$item[0] .= rand(0, 1000);
					}
				}
				$this->sidebarPosition[$item[0]] = $item[1];
				$item = $item[0]; 
			}else{
				if( empty($item) ){
					$item = $key;
					if( strpos($item, "separator" ) !== false ){
						$item .= rand(0, 1000);
					}
				}
			}
		}
	}

	final public function getActions(){
		if( is_array($this->actions) ){
			return $this->actions;
		}
		return array();
	}

	final public function getAdminActions(){
		if( is_array($this->admin) ){
			return array_values($this->admin);
		}
		return array();
	}

	final public function getAdminSidebarItems(){
		if( is_array($this->admin) ){
			return $this->admin;
		}
		return array();
	}

	final public function getAdminSidebarPositions(){
		if( is_array($this->sidebarPosition) ){
			return $this->sidebarPosition;
		}
		return array();
	}

	final public function getAdminPageFile($action){
		if( !empty($this->adminPages[$action]) && file_exists($this->getFilePath($this->adminPages[$action])) ){
			return $this->getFilePath($this->adminPages[$action]);
		}
		return "";
	}

	final public function getIncludes(){
		if( is_array($this->includes) ){
			return $this->includes;
		}
		return array();
	}

	final public function getTables(){
		if( is_array($this->getInfo('tables')) ){
			return $this->getInfo('tables');
		}
		return array();
	}

	final public function getDatabase(){
		$database = $this->getInfo('database');
		return $database;
	}
	
	final public static function Init(){
		
		self::$list = array();
		self::$usedActions = array();
		self::$usedAdminActions = array();
		self::$defaultList = array('admin', 'filemanager', 'users', 'entries');
		$externalList = is_array(unserialize(Settings::Get('extensions'))) ? unserialize(Settings::Get('extensions')) : array();
		$extensions = array_merge(self::$defaultList, $externalList);
		$extensionActions = $extensionAdminActions = array();
		foreach ($extensions as $extension) {
			if( self::IsExtention($extension) ){
				try{  
					$namespace = self::IsDefault($extension) ? __NAMESPACE__ : "uCMS\\Extensions";
					$extensionClass = "$namespace\\$extension\\$extension";
					if( class_exists($extensionClass) ){
						self::$list[$extension] = new $extensionClass($extension);
						$e = self::$list[$extension];
						$e->onLoad();
						$extensionActions = is_array($e->getActions()) ? $e->getActions() : array();
						$extensionAdminActions = is_array($e->getAdminActions()) ? $e->getAdminActions() : array();
						self::$usedActions = array_merge(self::$usedActions, $extensionActions);
						self::$usedAdminActions = array_merge(self::$usedAdminActions, $extensionAdminActions);
					}else{
						// error
					}
				}catch(\Exception $e){
					Debug::Log(tr("Can't load extension: @s, error: @s", $extension, $e->getMessage()), Debug::LOG_ERROR);
				}
					
			}
		}
		self::$usedActions = array_unique(self::$usedActions);
		self::$usedAdminActions = array_unique(self::$usedAdminActions);
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
		foreach (self::$list as $name => $extension) {
			$tables = $extension->getTables();
			foreach ($tables as $table) {
				// TODO: SQL Preparation
				$database = $extension->getDatabase();
				$table = DatabaseConnection::GetDatabase($database)->getPrefix().stripslashes($table);
				$checkQuery = new Query("SELECT 1 FROM $table LIMIT 1");
				$result = $checkQuery->execute();
				if( $result === false ){
					// If some of the tables are missing we signal the installer of the need to start installation.
					return true;
				}
			}
		}
		return false;
	}

	final public static function Install($stage){
		foreach (self::$list as $name => $extension) {
			$extension->onInstall($stage);
		}
	}

	final protected function getRelativePath(){
		return self::IsDefault($this->name) ? self::CORE_PATH : self::PATH;
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
		if( !empty(self::$list[$name]) && is_object(self::$list[$name]) ){
			return self::$list[$name];
		}
		if( self::isExists($name) ){
			try{
				$extension = new Extension($name);
				return $extension;
			}catch(\Exception $e){
				return "";
			}
		}
		return '';
	}

	public static function IsLoaded($name){
		return (!empty(self::$list[$name]) && is_object(self::$list[$name]));
	}

	final public static function IsExists($name){
		return in_array($name, self::GetAll());
	}

	final public static function IsExtention($name){
		if( !is_object($name) ){
			if( in_array($name, self::$defaultList) ){
				return require_once(ABSPATH.self::CORE_PATH."$name/extension.php");
			}
			$dataExists = ( file_exists(ABSPATH.self::PATH.$name.'/extension.php') && file_exists(ABSPATH.self::PATH.$name.'/'.self::INFO) );
	
			if ( $dataExists ){
				include_once(ABSPATH.self::PATH.$name.'/extension.php');
				return ( class_exists($name) && is_subclass_of($name, "Extension") 
					&& in_array("IExtension", class_implements($name)) );
	
			}
			return false;
		}else{
			return is_subclass_of($name, "Extension");
		}
	}

	final public static function GetLoaded(){
		$names = array();
		foreach (self::$list as $name => $extension) {
			if( is_object($extension) ){
				$names[] = $name;
			}
		}
		return $names;
	}

	final public static function GetAll(){
		$names = array();
		if( UCMS_DEBUG ){
			$dirs = scandir(self::CORE_PATH);// array_filter(scandir(self::PATH), 'is_dir');
			if ( $dh = @opendir(self::CORE_PATH) ) {
				while ( ($extension = readdir($dh)) !== false ) {
					if( self::IsExtention($extension) ){
						/**
						* @todo check .. ?
						*/
						$names[] = $extension;
					}
				}
				closedir($dh);
			}
		}
		$dirs = scandir(self::PATH);// array_filter(scandir(self::PATH), 'is_dir');
		if ( $dh = @opendir(self::PATH) ) {
			while ( ($extension = readdir($dh)) !== false ) {
				if( self::IsExtention($extension) ){
					/**
					* @todo check .. ?
					*/
					$names[] = $extension;
				}
			}
			closedir($dh);
		}
		return $names;
	}

	final public static function GetExtensionByAdminAction($action){
		foreach (self::$list as $name => $extension) {
			if( is_object($extension) ){
				if( in_array($action, $extension->getAdminActions()) ){
					return $extension;
				}
			}
		}
	}

	final public static function GetExtensionByAction($action){
		foreach (self::$list as $name => $extension) {
			if( is_object($extension) ){
				if( in_array($action, $extension->getActions()) ){
					return $extension;
				}
			}
		}
		return NULL;
	}

	final public static function Delete($name){
		if( self::IsDefault($name) || !self::IsExtention($name) ){
			$message = new Notification(tr("Unable to delete extension \"@s\"", $name), Notification::ERROR);
			$message->add();
			return false;
		}
		self::Disable($name);
		//remove dir

		Notification::ClearPending();
		$message = new Notification(tr("Extension \"@s\" was successfully deleted", $name), Notification::SUCCESS);
		$message->add();
		return true;
	}

	final public static function Enable($name){
		if( self::IsLoaded($name) ){
			$message = new Notification(tr("Extension \"@s\" is already enabled", $name), Notification::ERROR);
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
					Debug::Log(tr("Can't load extension: @s, error: @s", $extension, $e->getMessage()), Debug::LOG_ERROR);
				}
			}
		}
		if($exists){
			$extensions = array();
			foreach (self::$list as $name => $extension) {
				$extensions[] = $name;
			}
			$extensions = implode(",", $extensions);
			Settings::Update('extensions', $extensions);
			$message = new Notification(tr("Extension \"@s\" was successfully enabled", $name), Notification::SUCCESS);
			$message->add();
			return true;
		}
		$message = new Notification(tr("Extension \"@s\" doesn't exists", $name), Notification::ERROR);
		$message->add();
		return false;
		/**
		* @todo event or something
		*/


	}

	final public static function Disable($name){
		if( !self::IsLoaded($name) ){
			$message = new Notification(tr("Extension \"@s\" is already disabled", $name), Notification::ERROR);
			$message->add();
			return false;
		}
		if( self::IsDefault($name) ){
			$message = new Notification(tr("Extension \"@s\" can't be disabled", $name), Notification::ERROR);
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
		Settings::Update('extensions', $extensions);
		$message = new Notification(tr("Extension \"@s\" was successfully disabled", $name), Notification::SUCCESS);
		$message->add();
		return true;
	}

	final public static function Add($name){
		var_dump($name);
		$message = new Notification(tr("Extension \"@s\" was successfully added", $name), Notification::SUCCESS);
		$message->add();
		return true;
	}

	final public static function Shutdown(){
		foreach (self::$list as $name => $extension) {
			$extension->onShutdown();
		}
	}
}
?>