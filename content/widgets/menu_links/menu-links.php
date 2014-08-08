<?php
$links = $args[0];
echo '<ul><li><a '.($action == 'index' ? 'class="selected"' : '').' href="'.UCMS_DIR.'/">'.$ucms->cout("widget.menu_links.main_page", true).'</a></li></ul>';
if($user->has_access('pages', 1)){
	if($links){
		echo menu_tree($links, 0);
	}
}
function menu_tree($menu, $root, $check_selection = false){
	$selected = false;
	if(is_array($menu)){
		foreach ($menu as $key){
			if($key['parent'] == $root){
				$children_menu = menu_tree($menu, $key['id']);
				$link = NICE_LINKS ? page_sef_links($key) : UCMS_DIR.'/?p='.$key['id'];
				$selected = menu_tree($menu, $key['id'], true);
				if(!$selected){
					$selected = (urldecode($_SERVER['REQUEST_URI']) == $link);
				}
				if($selected and $check_selection){
					return true;
				}
				$tree[] = '<li><a '.($selected ? 'class="selected"' : '').' href="'.$link.'">'.$key['title'].'</a>'.$children_menu.'</li>';
			}
		}
		if($check_selection) return $selected;
		if(isset($tree)){
			return '<ul>'.implode('', $tree).'</ul>';
		}else{
			return '';
		}
	}else{
		return false;
	}
}
?>