<?php
class Extensions{
	private static $list = array();
	private static $usedActions;
	private static $usedAdminActions;
	private static $defaultExtentions;

	public static function Create($extensions){
		self::$list = array();
		self::$usedActions = array();
		self::$usedAdminActions = array();
		self::$defaultExtentions = array('filemanager', 'users', 'entries');
		if( !is_array($extensions) ){
			$extensions = array($extensions);
		}

		foreach ($extensions as $extension) {
			if( file_exists(EXTENSIONS_PATH.$extension.'/extension.php') ){
				include EXTENSIONS_PATH.$extension.'/extension.php';
				if( class_exists($extension) ){
					try{
						self::$list[$extension] = new $extension($extension);
					}catch(Exception $e){
						log_add(tr("Can't load extension: @s, error: @s", $extension, $e->getMessage()), UC_LOG_ERROR);
					}
					
					
				}
			}
		}
	}

	public static function Load(){
		if( is_array(self::$list) ){
			$extensionActions = $extensionAdminActions = array();
			foreach (self::$list as $name => $extension) {
				if( is_object($extension) ){
					$extension->onLoad();
					
					$extensionActions = is_array($extension->getActions()) ? $extension->getActions() : array();
					$extensionAdminActions = is_array($extension->getAdminActions()) ? $extension->getAdminActions() : array();
					self::$usedActions = array_merge(self::$usedActions, $extensionActions);
					self::$usedAdminActions = array_merge(self::$usedAdminActions, $extensionAdminActions);
				}
			}
			self::$usedActions = array_unique(self::$usedActions);
			self::$usedAdminActions = array_unique(self::$usedAdminActions);
		}
	}

	public static function LoadOnAction($action){
		if( is_array(self::$list) ){
			$count = 0;
			$templateData = "";
			foreach (self::$list as $name => $extension) {
				if( !in_array($action, self::$usedActions) ) $action = OTHER_ACTION;
				if( is_object($extension) && in_array($action, $extension->getActions())){
					$templateData = $extension->onAction($action);
					$count++;
				}
			}
			if($count == 0) return "";
			return $templateData;
		}
		return "";
	}

	public static function LoadOnAdminAction($action){
		if( is_array(self::$list) ){
			$title = "";
			foreach (self::$list as $name => $extension) {
				if( is_object($extension) ){
					$settingsAction = URLManager::GetKeyValue($action);
					if($action == ADMIN_SETTINGS_ACTION && !empty($settingsAction) ){
						$action = $action.'/'.$settingsAction;
					}
					if( in_array($action, $extension->getAdminActions()) ){
						$title = $extension->onAdminAction($action);
					}
				}
			}
			if( !empty($title) ) $title = ' :: '.$title;
		}
		return array( "template" => ADMIN_ACTION, "title" => tr("μCMS Control Panel$title") );
	}

	public static function GetUsedAdminActions(){
		return self::$usedAdminActions;
	}

	public static function GetUsedActions(){
		return self::$usedActions;
	}

	public static function Get($name){
		if( !empty(self::$list[$name]) && is_object(self::$list[$name]) ){
			return self::$list[$name];
		}
		if( self::isExists($name) ){
			try{
				$extension = new Extension($name);
				return $extension;
			}catch(Exception $e){
				return "";
			}
		}
		return '';
	}

	public static function IsLoaded($name){
		return (!empty(self::$list[$name]) && is_object(self::$list[$name]));
	}

	public static function IsExists($name){
		return in_array($name, self::GetAll());
	}

	public static function IsExtention($name){
		return ( file_exists(EXTENSIONS_PATH.$name.'/extension.php') && file_exists(EXTENSIONS_PATH.$name.'/'.EXTENSION_INFO) );
	}

	public static function GetLoaded(){
		$names = array();
		foreach (self::$list as $name => $extension) {
			if( is_object($extension) ){
				$names[] = $name;
			}
		}
		return $names;
	}

	public static function GetAll(){
		$names = array();
		$dirs = scandir(EXTENSIONS_PATH);// array_filter(scandir(EXTENSIONS_PATH), 'is_dir');
		if ( $dh = @opendir(EXTENSIONS_PATH) ) {
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

	public static function GetExtensionByAdminAction($action){
		foreach (self::$list as $name => $extension) {
			if( is_object($extension) ){
				if( in_array($action, $extension->getAdminActions()) ){
					return $extension;
				}
			}
		}
	}

	public static function GetExtensionByAction($action){
		foreach (self::$list as $name => $extension) {
			if( is_object($extension) ){
				if( in_array($action, $extension->getActions()) ){
					return $extension;
				}
			}
		}
		return NULL;
	}

	public static function Delete($name){
		if( self::IsDefault($name) || !self::IsExtention($name) ){
			return ERROR_STATUS;
		}
		self::Disable($name);
		//remove dir
		return SUCCESS_STATUS;
	}

	public static function Enable($name){
		if( self::IsLoaded($name) ){
			return ERROR_STATUS;
		}
		$exists = false;

		if( file_exists(EXTENSIONS_PATH.$name.'/extension.php') ){
			include EXTENSIONS_PATH.$name.'/extension.php';

			if( class_exists($name) ){
				try{
					self::$list[$name] = new $name($name);
					$exists = true;
				}catch(Exception $e){
					log_add(tr("Can't load extension: @s, error: @s", $extension, $e->getMessage()), UC_LOG_ERROR);
				}
			}
		}
		if($exists){
			$extensions = array();
			foreach (self::$list as $name => $extension) {
				$extensions[] = $name;
			}
			$extensions = implode(",", $extensions);
			Settings::Set('extensions', $extensions);
			return SUCCESS_STATUS;
		}
		return ERROR_STATUS;
		/**
		* @todo event or something
		*/


	}

	public static function Disable($name){
		if( !self::IsLoaded($name) || self::IsDefault($name) ){
			return ERROR_STATUS;
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
		Settings::Set('extensions', $extensions);
		return SUCCESS_STATUS;
	}

	public static function Add($name){
		var_dump($name);
		return SUCCESS_STATUS;
	}

	public static function IsDefault($name){
		return in_array($name, self::$defaultExtentions);
	}

	public static function Shutdown(){
		foreach (self::$list as $name => $extension) {
			if( is_object($extension) ){
				$extension->onShutdown();
			}
		}
	}
}
?>