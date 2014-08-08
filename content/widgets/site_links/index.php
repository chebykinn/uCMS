<?php
$links = $udb->get_rows("SELECT * FROM `".UC_PREFIX."links` WHERE `publish` = 1");
if($links){
	echo "<ul>";
	for ($i = 0; $i < count($links); $i++) { 
		$link = NICE_LINKS ? SITE_DOMAIN.UCMS_DIR.'/redirect/'.$links[$i]['url'] : SITE_DOMAIN.UCMS_DIR.'/?action=redirect&url='.$links[$i]['url'];
		echo "<li><a title=\"".$links[$i]['description']."\" href=\"$link\" target=\"".$links[$i]['target']."\" rel=\"external\">".$links[$i]['name']."</a></li>";
	}
	echo "</ul>";
}else{
	echo "<br><b>Ссылок пока нет.</b><br><br>";
}
?>