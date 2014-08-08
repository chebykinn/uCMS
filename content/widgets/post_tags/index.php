<?php
$id = 0;
$tags = $udb->get_rows("SELECT `keywords` FROM `".UC_PREFIX."posts` WHERE `publish` > 0 GROUP BY `keywords` ORDER BY `id` DESC LIMIT 0, 50");
for($i = 0; $i < count($tags); $i++){
	$tag = explode(", ", $tags[$i]['keywords']);
	$tag = array_unique($tag);
	for($j = 0; $j < count($tag); $j++){
		$id += $i + $j;
		$keywords[$id] = $tag[$j];
	}
}
$keywords = array_unique($keywords);
shuffle($keywords);
if(!$keywords){
	echo '<b>Тегов пока нет</b>';
}else{
	echo '<p style="padding: 5px">';
	foreach ($keywords as $keyword) {
		$keyalias = preg_replace('/\s/', '%20', $keyword);
		$rand = rand(9, 20);
		if(NICE_LINKS){
			echo '<a style="font-size: '.$rand.'px;" href="'.UCMS_DIR.'/search/'.TAG_SEF_PREFIX.'/'.$keyalias.'" >'.$keyword.'</a> ';
		}else{
			echo '<a style="font-size: '.$rand.'px;" href="'.UCMS_DIR.'/?action=search&amp;tag='.$keyalias.'" >'.$keyword.'</a> ';
		}
	}
	echo '</p>';
}
?>