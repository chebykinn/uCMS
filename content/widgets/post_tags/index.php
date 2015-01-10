<?php
$tags = $udb->get_rows("SELECT `keywords` FROM `".UC_PREFIX."posts` AS `p` 
	LEFT JOIN `".UC_PREFIX."categories` AS `c` ON `c`.`id` = `p`.`category` WHERE `publish` > 0 AND `c`.`hidden` != 1 
	GROUP BY `keywords` ORDER BY `p`.`id` DESC LIMIT 0, 50");
$ucms->template($this->get("path", 'post_tags')."post-tags.php", true, $tags);
?>