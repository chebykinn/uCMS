<?php
namespace uCMS\Core\Admin;
use uCMS\Core\Extensions\Theme;
use uCMS\Core\Extensions\Extension;
use uCMS\Core\Extensions\Users\User;
use uCMS\Core\Page;
use uCMS\Core\Debug;
class ControlPanel{
	private static $sidebar;
	private static $action = Page::OTHER_ACTION;
	const TITLE = "μCMS Control Panel";
	const ACTION = "admin";
	const SETTINGS_ACTION = "settings";
	const THEME = "admin";
	private static $defaultItems = array();

	public static function Init(){
		self::LoadSidebar();
		if( self::IsActive() ){
			self::$action = Page::GetCurrent()->getActionData();
			if( empty(self::$action) ) self::$action = 'home'; 
		}
	}

	public static function GetDefaultActions(){
		return array_keys(self::$defaultItems);
	}

	public static function CheckAction($extensionActions){
		$result = array("isUsed" => false, "default" => false, "action" => "");

		if( User::Current()->can('access control panel') ){
			$currentAction = self::GetAction();
			$baseAction = self::GetBaseAction();
			$settingsAction = self::GetSettingsAction(); 
			if( (empty($extensionActions) || !is_array($extensionActions)) ) {
				$extensionActions = array();
			}
			
			if( !in_array($currentAction, $extensionActions) && empty($settingsAction) ){
				$currentAction = $baseAction;
			}
			if( in_array($currentAction, $extensionActions) ){
				$result['action'] = $currentAction;
				$result['isUsed'] = true;
				self::$action = $result['action'];
			}

		}else{
			$result['isUsed'] = true;
			$result['default'] = true;
			if( !User::Current()->isLoggedIn() ){
				Theme::GetCurrent()->setThemeTemplate('login');
			}else{
				Theme::GetCurrent()->setThemeTemplate('access_denied');
			}
		}
		return $result;
	}

	private static function LoadSidebar(){
		$prevAction = 'home';
		$position = 'home';
		$waitingItems = array();
		$loadedExtensions = Extension::GetLoaded();
		$lastParent = "";
		if( !empty($loadedExtensions) ){
			foreach ($loadedExtensions as $name) {
				$extensionItems = Extension::Get($name)->getAdminSidebarItems();
				$positions = Extension::Get($name)->getAdminSidebarPositions();
				if( !empty($extensionItems) && is_array($extensionItems) ){
					foreach ($extensionItems as $name => $action) {
						if ( $action == "" && strpos($action, "separator") === false ) continue;
						$position = !empty($positions[$action]) ? $positions[$action] : $prevAction;
						if( mb_substr($name, 0, 1) === '#' ){
							$parent = 'settings';
							$name = mb_substr($name, 1, mb_strlen($name));
							$position = "settings";
						}elseif( mb_substr($name, 0, 1) === '@' ){
							$name = mb_substr($name, 1, mb_strlen($name));
							$parent = $lastParent;
						}else{
							$parent = 0;
							$lastParent = $action;
						}
						$item = array('name' => tr($name), 
							'action' => $action, 'parent' => $parent, 'after' => $position);
						if( $prevAction == $position && !isset($waitingItems[$position]) ){
							self::$sidebar[] = $item;
						}else{
							$waitingItems[$action] = $item;
						}
						if( strpos($action, "settings/") === false && strpos($action, "separator") === false){
							$prevAction = $action;
						}
					}
				}
			}
		}
		$offset = array();
		$count = 0;
		$limit = 32;
		while( !empty($waitingItems) && $count < $limit ){
			foreach ($waitingItems as $key => $item) {
				foreach (self::$sidebar as $searchKey => $searchItem) {
					if( $item['after'] == $searchItem['action'] ){
						if( !isset($offset[$searchKey]) ){
							$offset[$searchKey] = 1;
						}else{
							$offset[$searchKey]++;
						}
						array_splice(self::$sidebar, $searchKey+$offset[$searchKey], 0, array($item));
						unset($waitingItems[$key]);
					}
				}
				if( $item['after'] == "settings" ){
					array_push(self::$sidebar, $item);
					unset($waitingItems[$key]);
				}
			}
			$count++;
		}
		/* 
		   If current item depends on item from another extension,
		   which was disabled or deleted, this will prevent it from disappearing
		*/
		if( !empty($waitingItems) ){
			foreach ($waitingItems as $key => $item) {
				array_push(self::$sidebar, $item);
			}
		}
	}

	public static function AddMenuItem($name, $action, $parent = "", $after = "home"){
		if( empty($name) || empty($action) ) return false;
		if( empty($after) ) $after = 'home';
		$item = array('name' => tr($name), 'action' => $action, 'parent' => $parent, 'after' => $after);
		foreach (self::$sidebar as $searchKey => $searchItem) {
			if( $after == $searchItem['action'] ){
				array_splice(self::$sidebar, $searchKey+1, 0, array($item));
				return true;
			}
		}
		return false;
	}

	public static function SetTitle($title){
		$delimeter = " :: ";
		$settingsAction = self::GetSettingsAction();
		$settingsTitle = (self::IsSettingsPage() && !empty($settingsAction) )
		? tr('Settings').$delimeter : "";
		$newTitle = $settingsTitle.$title;
		Theme::GetCurrent()->setTitle(self::TITLE.$delimeter.$newTitle);
		Theme::GetCurrent()->setPageTitle($newTitle);
	}

	public static function GetSidebar(){
		return self::$sidebar;
	}

	public static function IsActive(){
		return Page::GetCurrent()->getAction() == self::ACTION;
	}

	public static function GetAction(){
		return self::$action;
	}

	public static function GetBaseAction(){
		if( strpos(self::$action, "/") === false ){
			return self::$action;
		}else{
			return Page::GetCurrent()->getKeyValue(self::ACTION);
		}
	}

	public static function IsSettingsPage(){
		return self::GetBaseAction() === self::SETTINGS_ACTION;
	}

	public static function GetSettingsAction(){
		return Page::GetCurrent()->getKeyValue('settings');
	}

	public static function PrintSidebar($root = 0, $checkSelection = false){
		$selected = false;
		if( is_array(self::$sidebar) ){
			foreach (self::$sidebar as $key){
				if( $key['parent'] === $root ){
					if( strpos($key['action'], "separator") === false ){
						$childrenMenu = self::PrintSidebar($key['action']);
						if($key['action'] == 'home') $key['action'] = "";
						$link = Page::FromAction(self::ACTION, $key['action'])->getURL();
						$selected = self::PrintSidebar($key['action'], true);
						if( !$selected ){
							$selected = (mb_strpos( htmlspecialchars_decode((string)Page::GetCurrent()), htmlspecialchars_decode($link) ) !== false);
							if( empty($key['action']) ) { // Select home page button
								$selected = (htmlspecialchars_decode((string)Page::GetCurrent()) == htmlspecialchars_decode($link)
											|| htmlspecialchars_decode((string)Page::GetCurrent()).'/' == htmlspecialchars_decode($link));
							}
						}
						if( $selected and $checkSelection ){
							return true;
						}
						$tree[] = '<li><a '.($selected ? 'class="selected"' : '').' href="'.$link.'">'.$key['name'].'</a>'.$childrenMenu.'</li>';
					}else{
						$tree[] = '<li><div class="separator"></div></li>';
					}
				}
			}
			if( $checkSelection ) return $selected;
			if( isset($tree) ){
				return '<ul>'.implode('', $tree).'</ul>';
			}else{
				return '';
			}
		}else{
			return false;
		}
	}

	public static function LoadTemplate(){
		$currentAction = self::GetAction();
		$extension = Extension::getExtensionByAdminAction($currentAction);
		if( is_object($extension) ){
			$pageFile = $extension->getAdminPageFile($currentAction);
		}
		if( !empty($extension) && !empty($pageFile) ){
			include_once($pageFile);
		}else{
			Debug::Log(tr("Unable to load admin page for action: @s", $currentAction), Debug::LOG_ERROR);
			$homePage = Page::FromAction(self::ACTION);
			$homePage->go();
		}
	}
}
?>