<?php
$links = $udb->get_rows("SELECT `id`, `title`, `alias`, `date`, `author` FROM `".UC_PREFIX."pages` WHERE `publish` > 0");
echo '<li><a href="'.UCMS_DIR.'/">Главная</a></li>';
if($user->has_access(2, 1)){
	if($links){
		for($i = 0; $i < count($links); $i++){
			if(NICE_LINKS){
				$link = page_sef_links($links[$i]);
			}else
				$link = UCMS_DIR."/?p=".$links[$i]['id'];
			echo '<li><a href="'.$link.'">'.$links[$i]['title'].'</a></li>';	
		}
	}
}
?>