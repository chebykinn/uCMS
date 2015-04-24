<?php
class AdminPanel{
	private static $sidebar;
	private static $action;

	public static function init(){
		self::loadSidebar();
		self::$action = URLManager::getCurrentAdminAction();
	}

	private static function loadSidebar(){
		self::$sidebar[] = array('name' => tr('Home'), 'action' => '', 'parent' => 0);
		$prevAction = '';
		$loadedExtensions = Extensions::getLoadedExtensions();
		if( !empty($loadedExtensions) ){
			foreach ($loadedExtensions as $name) {
				$extensionItems = Extensions::get($name)->getAdminSidebarItems();
				if( !empty($extensionItems) && is_array($extensionItems) ){
					foreach ($extensionItems as $name => $action) {
						if( !isset(self::$sidebar[$name]) ){
							if( mb_substr($name, 0, 1) === '#' ){
								self::$sidebar[] = array('name' => tr(mb_substr($name, 1, mb_strlen($name))), 
									'action' => $action, 'parent' => 'settings');
							}elseif( mb_substr($name, 0, 1) === '@' ){
								self::$sidebar[] = array('name' => tr(mb_substr($name, 1, mb_strlen($name))), 
									'action' => $action, 'parent' => $prevAction);
							}else{
								self::$sidebar[] = array('name' => tr($name), 
									'action' => $action, 'parent' => 0);
							}
						}
						$prevAction = $action;
					}
				}
			}
		}
		self::$sidebar[] = array('name' => tr('Extensions'), 'action' => 'extensions', 'parent' => 0);
		self::$sidebar[] = array('name' => tr('Settings'), 'action' => 'settings', 'parent' => 0);
	}

	public static function getSidebar(){
		return self::$sidebar;
	}

	public static function getAction(){
		return self::$action;
	}

	public static function printSidebar($root = 0, $checkSelection = false){
		$selected = false;
		if( is_array(self::$sidebar) ){
			foreach (self::$sidebar as $key){
				if( $key['parent'] === $root ){
					$childrenMenu = self::printSidebar($key['action']);
					$link = URLManager::makeLink(ADMIN_ACTION, $key['action']);
					$selected = self::printSidebar($key['action'], true);
					if( !$selected ){
						$selected = (htmlspecialchars_decode(URLManager::getRaw()) == htmlspecialchars_decode($link));
					}
					if( $selected and $checkSelection ){
						return true;
					}
					$tree[] = '<li><a '.($selected ? 'class="selected"' : '').' href="'.$link.'">'.$key['name'].'</a>'.$childrenMenu.'</li>';
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