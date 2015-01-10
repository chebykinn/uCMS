<?php
$categories = $udb->get_rows("SELECT * FROM `".UC_PREFIX."categories` WHERE `posts` > 0 AND `hidden` != 1 ORDER BY `parent` ASC, `sort` ASC");
$ucms->template($this->get("path", 'post_categories')."post-categories.php", true, $categories);
?>