<?php
class Extentions{
	private static $list;
	private static $usedActions;
	private static $usedAdminActions;

	public static function create($extentions){
		self::$list = array();
		self::$usedActions = array();
		self::$usedAdminActions = array();
		if( !is_array($extentions) ){
			$extentions = array($extentions);
		}
		foreach ($extentions as $extention) {
			if( file_exists(EXTENTIONS_PATH.$extention.'/extention.php') ){
				include EXTENTIONS_PATH.$extention.'/extention.php';
				if( class_exists($extention) ){
					try{
						self::$list[$extention] = new $extention($extention);
					}catch(InvalidArgumentException $e){
						p("[@s]: ".$e->getMessage(), $extention);
					}catch(RuntimeException $e){
						p("[@s]: ".$e->getMessage(), $extention);
					}
					
					
				}
			}
		}
	}

	public static function load(){
		if( is_array(self::$list) ){
			$extentionActions = $extentionAdminActions = array();
			foreach (self::$list as $name => $extention) {
				if( is_object($extention) ){
					$extention->load();
					
					$extentionActions = is_array($extention->getActions()) ? $extention->getActions() : array();
					$extentionAdminActions = is_array($extention->getAdminActions()) ? $extention->getAdminActions() : array();
					self::$usedActions = array_merge(self::$usedActions, $extentionActions);
					self::$usedAdminActions = array_merge(self::$usedAdminActions, $extentionAdminActions);
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
			foreach (self::$list as $name => $extention) {
				if( !in_array($action, self::$usedActions) ) $action = OTHER_ACTION;
				if( is_object($extention) && in_array($action, $extention->getActions())){
					$templateData = $extention->doAction($action);
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
			foreach (self::$list as $name => $extention) {
				if( is_object($extention) && in_array($action, $extention->getAdminActions()) ){
					$title = $extention->doAdminAction($action);
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

	public static function getLoadedExtentions(){
		$names = array();
		foreach (self::$list as $name => $extention) {
			if( is_object($extention) ){
				$names[] = $name;
			}
		}
		return $names;
	}
}
?>