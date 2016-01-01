<?php
namespace uCMS\Core\Admin;
use uCMS\Core\Extensions\Theme;
use uCMS\Core\Extensions\ExtensionHandler;
use uCMS\Core\Extensions\Users\User;
use uCMS\Core\Page;
use uCMS\Core\Debug;
use uCMS\Core\Setting;
use uCMS\Core\Tools;
use uCMS\Core\Notification;
use uCMS\Core\Object;
class ControlPanel extends Object{
	private static $sidebar = array();
	private static $action = Page::OTHER_ACTION;
	const TITLE = "μCMS Control Panel";
	const HOME_PAGE = 'Home';
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
			if( self::GetAction() !== Page::INDEX_ACTION ){
				$homePage = Page::ControlPanel();
				$homePage->go();
			}
			$result['isUsed'] = true;
			$result['default'] = true;
			if( !User::Current()->isLoggedIn() ){
				$title = self::Translate('Login');
				$template = 'login';
			}else{
				$title = self::Translate('Access Denied');
				$template = 'access_denied';
			}
			ControlPanel::SetTitle($title);
			Theme::GetCurrent()->setPageTitle(self::TITLE.' :: '.$title);
			Theme::GetCurrent()->setThemeTemplate($template);
		}
		return $result;
	}

	private static function SortSidebar($items){
		$amount = count($items);
		$added = array();
		$limit = 10;
		$c = 0;
		while ( $amount > 0 && $c < $limit ) {
			foreach ($items as $name => $item) {
				if( !isset($added[$item['action']]) ){
					foreach (self::$sidebar as $searchKey => $searchItem) {
						if( $item['after'] === $searchItem['action'] ){
							if( mb_substr($name, 0, 1) === '#' ){
								$parent = 'settings';
								$name = mb_substr($name, 1, mb_strlen($name));
							}elseif( mb_substr($name, 0, 1) === '@' ){
								$name = mb_substr($name, 1, mb_strlen($name));
								$parent = $lastParent;
							}else{
								$parent = 0;
								$lastParent = $item['action'];
							}
							self::$sidebar[] = array('name' => self::Translate($name), 'action' => $item['action'], 'parent' => $parent, 'after' => $item['after'] );
							$amount--;
							$added[$item['action']] = true;
							break;
						}
					}
				}
			}
			$c++;
		}
	}

	private static function GetWeight(&$allItems, $action = '', $maxWeight = 0){
		foreach ($allItems as &$item) {
			if( $item['action'] === $action || empty($action) ){

				if ( $item['weight'] < 0 ){

						$selfWeight = self::GetWeight($allItems, $item['after'], $maxWeight)+1;
						if( $selfWeight <= $maxWeight ){
							$selfWeight = ++$maxWeight;
						}
						$item['weight'] = $selfWeight;
						return $item['weight'];
				}else{
					if( $item['weight'] > $maxWeight ){
						$maxWeight = $item['weight'];
					}
					return $item['weight'];
				}
			}
		}
	}

	private static function SetWeight(&$allItems, $action = ''){
		$maxWeight = 0;
		foreach ($allItems as &$item) {
			if ( $item['weight'] < 0 ){
				$item['weight'] = self::GetWeight($allItems, $item['after'])+1;
			}
		}
	}

	private static function sort($a, $b){
		if( $a['weight'] == $b['weight'] ) return 0;
		return $a['weight'] > $b['weight'] ? 1 : -1;
	}


	private static function LoadSidebar(){
		$loadedExtensions = ExtensionHandler::GetLoaded();
		$allItems = array();
		$allPositions = array();
		foreach ($loadedExtensions as $name) {
			$items = ExtensionHandler::Get($name)->getAdminSidebarItems();
			$allItems = array_merge($allItems, $items);
		}
		$prevWeight = 0;
		$prevAfter = 'home';
		
		self::SetWeight($allItems);
		uasort($allItems, __NAMESPACE__.'\\ControlPanel::sort');

		foreach ($allItems as $name => $item) {
			if( mb_substr($name, 0, 1) === '#' ){
				$parent = 'settings';
				$name = mb_substr($name, 1, mb_strlen($name));
			}elseif( mb_substr($name, 0, 1) === '@' ){
				$name = mb_substr($name, 1, mb_strlen($name));
				$parent = $lastParent;
			}else{
				$parent = 0;
				if( strpos($item['action'], "separator" ) === false ){
					$lastParent = $item['action'];
				}
			}
			self::$sidebar[] = array('name' => self::Translate($name), 'action' => $item['action'], 'parent' => $parent, 'after' => $item['after'] );
		}
	}

	public static function AddMenuItem($name, $action, $parent = "", $after = "home"){
		if( empty($name) || empty($action) ) return false;
		if( empty($after) ) $after = 'home';
		$item = array('name' => self::Translate($name), 'action' => $action, 'parent' => $parent, 'after' => $after);
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
		? self::Translate('Settings').$delimeter : "";
		$newTitle = $settingsTitle.$title;
		$siteName = Setting::Get('site_name');
		Theme::GetCurrent()->setTitle($newTitle.$delimeter.self::TITLE.$delimeter.$siteName);
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
		return Page::GetCurrent()->getKeyValue(self::SETTINGS_ACTION);
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
							$selected = (mb_strpos( (string)Page::GetCurrent(), $link ) !== false);
							if( empty($key['action']) ) { // Select home page button
								$selected = ((string)Page::GetCurrent() == $link || (string)Page::GetCurrent().'/' == $link);
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
		$extension = ExtensionHandler::GetExtensionByAdminAction($currentAction);
		if( is_object($extension) ){
			$pageFile = $extension->getAdminPageFile($currentAction);
		}
		if( !empty($extension) && !empty($pageFile) ){
			return $pageFile;
		}else{
			Debug::Log(self::Translate("Unable to load admin page for action: @s", $currentAction), Debug::LOG_ERROR);
			$homePage = Page::FromAction(self::ACTION);
			$homePage->go();
		}
	}

	public static function UpdateSettings(){
		// TODO: Consider use some method for multiple changes in one query
		if( User::Current()->can("update core settings") ){
			$failures = [];
			unset($_POST['settings']);
			foreach ($_POST as $name => $value) {
				if( !Setting::IsExists($name) ){
					$failures[] = $name;
					continue;
				}

				$setting = Setting::GetRow($name, new self());
				if( !$setting ){
					$failures[] = $name;
				}

				$setting->value = $value;
				$setting->update();
			}
			if( !empty($failures) ){
				$list = implode("<br>", $failures);
				$fail = new Notification(self::Translate("Error: Some settings weren't updated: @s", $list), Notification::ERROR);
				$fail->add();
			}else{
				$success = new Notification(self::Translate("Settings have been successfully updated."), Notification::SUCCESS);
				$success->add();
			}
		}
		$page = Page::ControlPanel(ControlPanel::GetAction());
		$page->go();
	}
}
?>