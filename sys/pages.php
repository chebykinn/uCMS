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
					if(!$user->has_access(3, 4))
						$page_page = $udb->get_row("SELECT `id`,`title` FROM `".UC_PREFIX."pages` WHERE `alias` = '$match' or `id` = '$match' or `title` = '$match' AND `publish` > 0 LIMIT 1");
					else $page_page = $udb->get_row("SELECT `id`,`title` FROM `".UC_PREFIX."pages` WHERE `alias` = '$match' or `id` = '$match' or `title` = '$match' LIMIT 1");
					if($page_page){ 
						if(isset($sources[$i+1])){
							if(preg_match("/@/", $sources[$i+1])){
								unset($page_page);
							}else break;
						}else
							break;
					}else{
						if(isset($sources[$i+1])){
							if(!preg_match("/@/", $sources[$i+1]))
								break;
						}else break;
					}
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
		if(!$user->has_access(3, 4))
			$page_page = $udb->get_row("SELECT `title` FROM `".UC_PREFIX."pages` WHERE `id` = '$page_id' AND `publish` > 0 LIMIT 1");
		else $page_page = $udb->get_row("SELECT `title` FROM `".UC_PREFIX."pages` WHERE `id` = '$page_id' LIMIT 1");
	}
}
if(isset($pageid)) $page_id = (int) $pageid;

if(!$user->has_access(3, 4))
	$page_sql = "SELECT * FROM `".UC_PREFIX."pages` WHERE `id` = '$page_id' and `publish` > 0 LIMIT 1";
else
	$page_sql = "SELECT * FROM `".UC_PREFIX."pages` WHERE `id` = '$page_id' LIMIT 1";
$id_page = $udb->get_row($page_sql);
if(!$id_page){
	$ucms->panic(404);
}

require_once 'sys/include/pages.php';
if(file_exists(THEMEPATH.'page.php'))
	require THEMEPATH.'page.php';
else require UC_DEFAULT_THEMEPATH.'page.php';
$udb->db_disconnect($con);
?>
