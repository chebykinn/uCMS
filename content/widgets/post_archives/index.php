<?php
$dates = $udb->get_rows("SELECT month(`date`) AS M, year(`date`) AS Y FROM `".UC_PREFIX."posts` AS `p` 
	LEFT JOIN `".UC_PREFIX."categories` AS `c` ON `c`.`id` = `p`.`category` 
	WHERE `publish` > 0  AND `hidden` != 1 GROUP BY M,Y ORDER BY Y DESC, M ASC LIMIT 6");
$ucms->template($this->get("path", 'post_archives')."post-archives.php", true, $dates);
?>