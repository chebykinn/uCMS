<?php
$dates = $args[0];
if (!$dates) {
	echo '<br><b>'.$ucms->cout("widget.post_archives.no_posts", true).'</b><br><br>';
} else {
	echo '<ul>';
	for ( $i = 0; $i < count($dates); $i++ ) {
		if($dates[$i]['M'] < 10) $dates[$i]['M'] = '0'.$dates[$i]['M'];
		$link = NICE_LINKS ? UCMS_DIR.'/archive/'.$dates[$i]['Y'].'/'.$dates[$i]['M'] : UCMS_DIR.'/?action=archive&amp;y='.$dates[$i]['Y'].'&amp;m='.$dates[$i]['M'];
		echo '<li><a '.(urldecode(preg_replace("/&/","&amp;", $_SERVER['REQUEST_URI'])) == $link ? 'class="selected"' : '').' href="'.$link.'">'.$uc_months[$dates[$i]['M']].' '.$dates[$i]['Y'].'</a></li>';
	}	
	echo '</ul>';
}
?>