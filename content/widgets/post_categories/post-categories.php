<?php
$categories = $args[0];

if($categories){
	echo category_tree($categories, 0);
}

function category_tree($menu, $root){
	if(is_array($menu)){
		foreach ($menu as $key){
			if($key['parent'] == $root){
				$children_menu = category_tree($menu, $key['id']);
				$link = NICE_LINKS ? UCMS_DIR.'/'.CATEGORY_SEF_PREFIX.'/'.$key['alias'] : UCMS_DIR.'/?category='.$key['id'];
				$tree[] = '<li><a '.(urldecode($_SERVER['REQUEST_URI']) == $link ? 'class="selected"' : '').' href="'.$link.'">'.($root > 0 ? " — " : "").$key['name'].'</a>'.$children_menu.'</li>';
			}
		}
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