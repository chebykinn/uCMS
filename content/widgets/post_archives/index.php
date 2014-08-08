<?php
$dates = $udb->get_rows("SELECT month(`date`) AS M, year(`date`) AS Y FROM `".UC_PREFIX."posts` WHERE `publish` > 0 GROUP BY M,Y ORDER BY Y DESC, M ASC LIMIT 6");
if (!$dates) {
	echo '<br><b>Постов пока нет</b><br><br>';
} else {
	echo '<ul>';
	for ( $i = 0; $i < count($dates); $i++ ) {
		if($dates[$i]['M'] < 10) $dates[$i]['M'] = '0'.$dates[$i]['M'];
		if(NICE_LINKS) echo '<li><a href="'.UCMS_DIR.'/archive/'.$dates[$i]['Y'].'/'.$dates[$i]['M'].'">'.$months[$dates[$i]['M']].' '.$dates[$i]['Y'].'</a></li>';
		else echo '<li><a href="'.UCMS_DIR.'/?action=archive&amp;y='.$dates[$i]['Y'].'&amp;m='.$dates[$i]['M'].'">'.$months[$dates[$i]['M']].' '.$dates[$i]['Y'].'</a></li>';
	}	
	echo '</ul>';
}
?>