<?php
class Extensions{
	private static $list;
	private static $usedActions;
	private static $usedAdminActions;

	public static function create($extensions){
		self::$list = array();
		self::$usedActions = array();
		self::$usedAdminActions = array();
		if( !is_array($extensions) ){
			$extensions = array($extensions);
		}

		foreach ($extensions as $extension) {
			if( file_exists(EXTENSIONS_PATH.$extension.'/extension.php') ){
				include EXTENSIONS_PATH.$extension.'/extension.php';
				if( class_exists($extension) ){
					try{
						self::$list[$extension] = new $extension($extension);
					}catch(InvalidArgumentException $e){
						p("[@s]: ".$e->getMessage(), $extension);
					}catch(RuntimeException $e){
						p("[@s]: ".$e->getMessage(), $extension);
					}
					
					
				}
			}
		}
	}

	public static function load(){
		if( is_array(self::$list) ){
			$extensionActions = $extensionAdminActions = array();
			foreach (self::$list as $name => $extension) {
				if( is_object($extension) ){
					$extension->load();
					
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

	public static function loadOnAction($action){
		if( is_array(self::$list) ){
			$count = 0;
			$templateData = "";
			foreach (self::$list as $name => $extension) {
				if( !in_array($action, self::$usedActions) ) $action = OTHER_ACTION;
				if( is_object($extension) && in_array($action, $extension->getActions())){
					$templateData = $extension->doAction($action);
					$count++;
				}
			}
			if($count == 0) return "";
			return $templateData;
		}
		return "";
	}

	public static function loadOnAdminAction($action){
		if( is_array(self::$list) ){
			$title = "";
			foreach (self::$list as $name => $extension) {
				if( is_object($extension) ){
					$settingsAction = URLManager::getKeyValue($action);
					if($action == ADMIN_SETTINGS_ACTION && !empty($settingsAction) ){
						$action = $action.'/'.$settingsAction;
					}
					if( in_array($action, $extension->getAdminActions()) ){
						$title = $extension->doAdminAction($action);
					}
				}
			}
			if( !empty($title) ) $title = ' :: '.$title;
		}
		return array( "template" => ADMIN_ACTION, "title" => tr("μCMS Control Panel$title") );
	}

	public static function getUsedAdminActions(){
		return self::$usedAdminActions;
	}

	public static function getUsedActions(){
		return self::$usedActions;
	}

	public static function get($name){
		if( !empty(self::$list[$name]) && is_object(self::$list[$name]) ){
			return self::$list[$name];
		}
		return '';
	}

	public static function isLoaded($name){
		return (!empty(self::$list[$name]) && is_object(self::$list[$name]));
	}

	public static function isExists($name){

	}

	public static function getLoadedExtensions(){
		$names = array();
		foreach (self::$list as $name => $extension) {
			if( is_object($extension) ){
				$names[] = $name;
			}
		}
		return $names;
	}

	public static function getExtensionByAdminAction($action){
		foreach (self::$list as $name => $extension) {
			if( is_object($extension) ){
				if( in_array($action, $extension->getAdminActions()) ){
					return $extension;
				}
			}
		}
	}

	public static function getExtensionByAction($action){
		foreach (self::$list as $name => $extension) {
			if( is_object($extension) ){
				if( in_array($action, $extension->getActions()) ){
					return $extension;
				}
			}
		}
		return NULL;
	}
}
?>