<?php
class AdminPanel{
	private static $sidebar;

	public static function init(){
		self::loadSidebar();
	}

	private static function loadSidebar(){
		self::$sidebar[] = array('name' => tr('Home'), 'action' => '', 'parent' => 0);
		$prevAction = '';
		$loadedExtentions = uCMS::getInstance()->getExtentions()->getLoadedExtentions();
		if( !empty($loadedExtentions) ){
			foreach ($loadedExtentions as $name) {
				$extentionItems = uCMS::getInstance()->getExtentions()->get($name)->getAdminSidebarItems();
				if( !empty($extentionItems) && is_array($extentionItems) ){
					foreach ($extentionItems as $name => $action) {
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
		self::$sidebar[] = array('name' => tr('Extentions'), 'action' => 'extentions', 'parent' => 0);
		self::$sidebar[] = array('name' => tr('Settings'), 'action' => 'settings', 'parent' => 0);
	}

	public static function getSidebar(){
		return self::$sidebar;
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
						$url = new URLManager();
						$selected = (htmlspecialchars_decode($url->getRaw()) == htmlspecialchars_decode($link));
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