<?php

if(NICE_LINKS){	
	$sef_value = urldecode($udb->parse_value($url));
	$sources = explode("/", PAGE_SEF_LINK);
	$matches = explode("/", substr($sef_value, 1));
	$i = 0;
	foreach($matches as $match){ 
		if(!empty($match) and isset($sources[$i])){
			if($match != $sources[$i]){
				if(preg_match("/@/", $sources[$i])){
					$match = $udb->parse_value($match);
					if(!$user->has_access("pages", 4)){
						$where = "AND `publish` > 0";
					}else{
						$where = "";
					}
					if(in_array("@parent_alias@", $sources) or in_array("@parent_title@", $sources) or in_array("@parent_id@", $sources)){
						if(!empty($page_page['id'])){
							$page_parent = $udb->get_row("SELECT `id`,`title` FROM `".UC_PREFIX."pages` WHERE (`alias` = '$match' or `id` = '$match' or `title` = '$match')
								AND `parent` = '$page_page[id]' $where LIMIT 1");
							if(!empty($page_parent) and !empty($page_parent['id'])){
								$page_page = $page_parent;
							}
						}else{
							if(isset($matches[$i+1])){
								$page_page = $udb->get_row("SELECT `id`,`title` FROM `".UC_PREFIX."pages` WHERE (`alias` = '$match' or `id` = '$match' or `title` = '$match') 
								$where LIMIT 1");
							}else{
								$page_page = $udb->get_row("SELECT `id`,`title` FROM `".UC_PREFIX."pages` WHERE (`alias` = '$match' or `id` = '$match' or `title` = '$match') 
								AND `parent` = '0' $where LIMIT 1");
							}
						}
					}else{
						$page_page = $udb->get_row("SELECT `id`,`title` FROM `".UC_PREFIX."pages` WHERE (`alias` = '$match' or `id` = '$match' or `title` = '$match') 
						$where LIMIT 1");
					}
					if(isset($sources[$i+1])){
						if(!preg_match("/@/", $sources[$i+1])) break;
					}else break;
				}else break;
			}
		}
		$i++;
	}
	if (!empty($page_page['id'])) {
		$page_id = $page_page['id'];
	}else{
		$page_id = 0;
	}	
}else{
	$page_id = (int) $_GET['p'];
	if($page_id > 0){
		if(!$user->has_access("pages", 4))
			$page_page = $udb->get_row("SELECT `title` FROM `".UC_PREFIX."pages` WHERE `id` = '$page_id' AND `publish` > 0 LIMIT 1");
		else $page_page = $udb->get_row("SELECT `title` FROM `".UC_PREFIX."pages` WHERE `id` = '$page_id' LIMIT 1");
	}
}
if(isset($pageid)) $page_id = (int) $pageid;

if(!$user->has_access("pages", 4))
	$page_sql = "SELECT * FROM `".UC_PREFIX."pages` WHERE `id` = '$page_id' and `publish` > 0 LIMIT 1";
else
	$page_sql = "SELECT * FROM `".UC_PREFIX."pages` WHERE `id` = '$page_id' LIMIT 1";
$id_page = $udb->get_row($page_sql);
if(!$id_page){
	$ucms->panic(404);
}else{
	$action = 'pages';
	add_title($action, $page_page['title']);
}

require_once ABSPATH.PAGES_MODULE_PATH.'pages.php';
if(file_exists($theme->get_path().'page.php'))
	require $theme->get_path().'page.php';
else require UC_DEFAULT_THEMEPATH.'page.php';
?>