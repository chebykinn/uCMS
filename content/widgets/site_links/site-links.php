<?php
$links = $args[0];
if($links){
	echo "<ul>";
	for ($i = 0; $i < count($links); $i++) { 
		if(!preg_match("#(".SITE_DOMAIN.")#", $links[$i]['url'])){
			$link = NICE_LINKS ? SITE_DOMAIN.UCMS_DIR.'/redirect/'.$links[$i]['url'] : SITE_DOMAIN.UCMS_DIR.'/?action=redirect&url='.$links[$i]['url'];
			$rel = 'nofollow';
		}else{
			$link = $links[$i]['url'];
			$rel = 'next';
		}
		echo "<li><a title=\"".$links[$i]['description']."\" href=\"$link\" target=\"".$links[$i]['target']."\" rel=\"$rel\">".$links[$i]['name']."</a></li>";
	}
	echo "</ul>";
}else{
	echo "<br><b>".$ucms->cout("widget.site_links.no_links", true)."</b><br><br>";
}
?>