<?php
$tags = $args[0];
if($tags){
	$id = 0;
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
}
if(!isset($keywords)){
	echo '<br><b>'.$ucms->cout("widget.post_tags.no_tags", true).'</b><br><br>';
}else{
	echo '<p style="padding: 5px">';
	foreach ($keywords as $keyword) {
		$keyalias = preg_replace('/\s/', '%20', $keyword);
		$rand = rand(9, 20);
		if(NICE_LINKS){
			echo '<a style="font-size: '.$rand.'px;" href="'.UCMS_DIR.'/'.TAG_SEF_PREFIX.'/'.$keyalias.'" >'.$keyword.'</a> ';
		}else{
			echo '<a style="font-size: '.$rand.'px;" href="'.UCMS_DIR.'/?action='.TAG_SEF_PREFIX.'&amp;key='.$keyalias.'" >'.$keyword.'</a> ';
		}
	}
	echo '</p>';
}
?>