<?php
class ControlPanel{
	private static $sidebar;
	private static $action = OTHER_ACTION;
	const TITLE = "μCMS Control Panel";
	private static $defaultItems = array();

	public static function Init(){
		self::LoadSidebar();
		self::SetDefaultItems();
		if( self::IsActive() ){
			self::$action = Page::GetCurrent()->getActionData();
			if( empty(self::$action) ) self::$action = 'home'; 
		}
	}

	private static function SetDefaultItems(){
		self::$defaultItems = array(
		 'home'       => tr('Home'),
		 'settings'   => tr("Settings"), 
		 'extensions' => tr("Extensions"), 
		 'themes'     => tr("Themes"), 
		 'widgets'    => tr("Widgets"), 
		 'tools'      => tr("Tools"),
		 'phpinfo'    => tr("PHP Information"),
		 'journal'    => tr("System Journal")
		);
	}

	public static function GetDefaultActions(){
		return array_keys(self::$defaultItems);
	}

	public static function CheckAction($extensionActions){
		$result = array("isUsed" => false, "default" => false, "action" => "");

		if( User::Current()->can('access control panel') ){
			$defaultActions = self::GetDefaultActions();
			$currentAction = self::GetAction();
			$baseAction = self::GetBaseAction();
			if( (empty($extensionActions) || !is_array($extensionActions)) ) {
				$extensionActions = array();
			}

			if( !in_array($currentAction, $defaultActions) 
				&& !in_array($currentAction, $extensionActions) ){
				$currentAction = $baseAction;
			}

			$result['action'] = $currentAction;
			if( in_array($currentAction, $defaultActions) ){
				self::SetTitle(self::$defaultItems[$currentAction]);
				$result['isUsed'] = true;
				$result['default'] = true;
			}else if( in_array($currentAction, $extensionActions) ){
				$result['isUsed'] = true;
			}

			self::$action = $result['action'];
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
		self::$sidebar[] = array('name' => tr('Home'), 'action' => 'home', 'parent' => 0, 'after' => '');
		$prevAction = 'home';
		$position = 'home';
		$waitingItems = array();
		$loadedExtensions = Extensions::GetLoaded();
		if( !empty($loadedExtensions) ){
			foreach ($loadedExtensions as $name) {
				$extensionItems = Extensions::Get($name)->getAdminSidebarItems();
				$positions = Extensions::Get($name)->getAdminSidebarPositions();
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
							$parent = $prevAction;
						}else{
							$parent = 0;
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

		self::$sidebar[] = array('name' => "separator",
			'action' => 'separator', 'parent' => 0, 'after' => 'home');
		self::$sidebar[] = array('name' => tr('Tools'),      'action' => 'tools',      'parent' => 0,            'after' => 'home');
		self::$sidebar[] = array('name' => tr('Journal'),    'action' => 'journal',    'parent' => 'tools',      'after' => 'tools');
		self::$sidebar[] = array('name' => tr('PHP Info'),   'action' => 'phpinfo',    'parent' => 'tools',      'after' => 'journal');
		self::$sidebar[] = array('name' => "separator",      'action' => 'separator',  'parent' => 0,            'after' => 'phpinfo');
		self::$sidebar[] = array('name' => tr('Extensions'), 'action' => 'extensions', 'parent' => 0,            'after' => 'phpinfo');
		self::$sidebar[] = array('name' => tr('Themes'),     'action' => 'themes',     'parent' => 'extensions', 'after' => 'extensions');
		self::$sidebar[] = array('name' => tr('Widgets'),    'action' => 'widgets',    'parent' => 'extensions', 'after' => 'themes');
		self::$sidebar[] = array('name' => "separator",      'action' => 'separator',  'parent' => 0,            'after' => 'themes');
		self::$sidebar[] = array('name' => tr('Settings'),   'action' => 'settings',   'parent' => 0,            'after' => 'widgets');
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
		$settingsTitle = self::IsSettingsPage() ? tr('Settings').$delimeter : "";
		$newTitle = self::TITLE.$delimeter.$settingsTitle.$title;
		Theme::GetCurrent()->setTitle($newTitle);
	}

	public static function GetSidebar(){
		return self::$sidebar;
	}

	public static function IsActive(){
		return Page::GetCurrent()->getAction() == ADMIN_ACTION;
	}

	public static function GetAction(){
		return self::$action;
	}

	public static function GetBaseAction(){
		if( strpos(self::$action, "/") === false ){
			return self::$action;
		}else{
			return Page::GetCurrent()->getKeyValue(ADMIN_ACTION);
		}
	}

	public static function IsSettingsPage(){
		return self::GetBaseAction() === ADMIN_SETTINGS_ACTION;
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
						$link = Page::FromAction(ADMIN_ACTION, $key['action'])->getURL();
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
}
?>