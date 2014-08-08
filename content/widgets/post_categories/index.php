<?php
$categories = $udb->get_rows("SELECT * FROM `".UC_PREFIX."categories` WHERE `posts` > 0 ");
if(!$categories){
	echo '<br><b>Категорий пока нет</b><br>';
}else{
	echo "<ul>";
	for($i = 0; $i < count($categories); $i++){
		if(NICE_LINKS){
			echo '<li><a href="'.UCMS_DIR.'/'.CATEGORY_SEF_PREFIX.'/'.$categories[$i]['alias'].'">'.$categories[$i]['name'].'</a></li>';
		}else{
			echo '<li><a href="'.UCMS_DIR.'/?category='.$categories[$i]['id'].'">'.$categories[$i]['name'].'</a></li>';
		}
	}
	echo "</ul>";
}
?>