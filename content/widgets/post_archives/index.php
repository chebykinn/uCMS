<?php
$dates = $udb->get_rows("SELECT month(`date`) AS M, year(`date`) AS Y FROM `".UC_PREFIX."posts` WHERE `publish` > 0 GROUP BY M,Y ORDER BY Y DESC, M ASC LIMIT 6");
$ucms->template($this->get("path", 'post_archives')."post-archives.php", true, $dates);
?>