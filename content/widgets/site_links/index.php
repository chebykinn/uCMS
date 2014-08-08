<?php
$links = $udb->get_rows("SELECT * FROM `".UC_PREFIX."links` WHERE `publish` = '1'");
$ucms->template($this->get("path", 'site_links')."site-links.php", true, $links);
?>