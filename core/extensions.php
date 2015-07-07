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
			if( self::IsExtention($extension) ){
				try{
					self::$list[$extension] = new $extension($extension);
				}catch(Exception $e){
					Debug::Log(tr("Can't load extension: @s, error: @s", $extension, $e->getMessage()), UC_LOG_ERROR);
				}
					
			}
		}
	}

	public static function Load(){
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

	public static function LoadOnAction($action){
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
					$action = OTHER_ACTION;
				}
				if( in_array($action, $extension->getActions()) ){
					$extension->onAction($action);
					$isUsed = true;
				}
			}
		}
		return $isUsed;
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
		if( !is_object($name) ){
			$dataExists = ( file_exists(EXTENSIONS_PATH.$name.'/extension.php') && file_exists(	EXTENSIONS_PATH.$name.'/'.EXTENSION_INFO) );
	
			if ( $dataExists ){
				include_once(EXTENSIONS_PATH.$name.'/extension.php');
				return ( class_exists($name) && is_subclass_of($name, "Extension") );
	
			}
			return false;
		}else{
			return is_subclass_of($name, "Extension");
		}
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

	public static function Enable($name){
		if( self::IsLoaded($name) ){
			$message = new Notification(tr("Extension \"@s\" is already enabled", $name), Notification::ERROR);
			$message->add();
			return false;
		}
		$exists = false;

		if( file_exists(EXTENSIONS_PATH.$name.'/extension.php') ){
			include EXTENSIONS_PATH.$name.'/extension.php';

			if( class_exists($name) ){
				try{
					self::$list[$name] = new $name($name);
					$exists = true;
				}catch(Exception $e){
					Debug::Log(tr("Can't load extension: @s, error: @s", $extension, $e->getMessage()), UC_LOG_ERROR);
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

	public static function Disable($name){
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
		Settings::Set('extensions', $extensions);
		$message = new Notification(tr("Extension \"@s\" was successfully disabled", $name), Notification::SUCCESS);
		$message->add();
		return true;
	}

	public static function Add($name){
		var_dump($name);
		$message = new Notification(tr("Extension \"@s\" was successfully added", $name), Notification::SUCCESS);
		$message->add();
		return true;
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