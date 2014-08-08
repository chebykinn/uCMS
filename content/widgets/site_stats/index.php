<?php
$users = $udb->get_rows("SELECT `uf`.`value`, `u`.`id`, `u`.`login` FROM `".UC_PREFIX."users` AS `u` 
	LEFT JOIN `".UC_PREFIX."usersinfo` AS `uf` ON `uf`.`user_id` = `u`.`id` AND `uf`.`name` = 'nickname' WHERE `u`.`online` = 1 AND `u`.`activation` = 1");
if(!$users) $users_count = 0; else $users_count = count($users);
$guests = $user->get_guests_count();
$all_count = $users_count+$guests;
$ucms->template($this->get("path", 'site_stats')."site-stats.php", true, $all_count, $users_count, $guests, $users);
?>