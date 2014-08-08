<?php
$links = $udb->get_rows("SELECT `p`.`id`, `p`.`title`, `p`.`alias`, `p`.`date`, `p`.`author`, `p`.`parent`,
`pa`.`id` AS `parent_id`, `pa`.`alias` AS `parent_alias`, `pa`.`title` AS `parent_title`, `u`.`login` AS `author_login` FROM `".UC_PREFIX."pages` AS `p` FORCE INDEX (PRIMARY)
LEFT JOIN `".UC_PREFIX."pages` AS `pa` ON `pa`.`id` = `p`.`parent`
LEFT JOIN `".UC_PREFIX."users` AS `u` ON `u`.`id` = `p`.`author` WHERE `p`.`publish` > 0 ORDER BY `p`.`parent` ASC, `p`.`sort` ASC");
$ucms->template($this->get("path", 'menu_links')."menu-links.php", true, $links);
?>