<?php
$tags = $udb->get_rows("SELECT `keywords` FROM `".UC_PREFIX."posts` WHERE `publish` > 0 GROUP BY `keywords` ORDER BY `id` DESC LIMIT 0, 50");
$ucms->template($this->get("path", 'post_tags')."post-tags.php", true, $tags);
?>