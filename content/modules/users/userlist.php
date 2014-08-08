<?php
$count = $udb->num_rows("SELECT `id` FROM `".UC_PREFIX."users`");
$perpage = 20;
$page = get_current_page();
if($page <= 0) $page = 1;
	if($count != 0){ 
		$pages_count = ceil($count / $perpage); 
		if ($page > $pages_count):
			$page = $pages_count;
		endif; 
		$start_pos = ($page - 1) * $perpage;
		$users = $udb->get_rows("SELECT `u`.*, `uf`.`value` AS `nickname`, `g`.`name` AS `group_name`
		FROM `".UC_PREFIX."users` AS `u` FORCE INDEX (PRIMARY)
		INNER JOIN `".UC_PREFIX."groups` AS `g` ON `g`.`id` = `u`.`group`
		LEFT JOIN `".UC_PREFIX."usersinfo` AS `uf` ON `uf`.`user_id` = `u`.`id` AND `uf`.`name` = 'nickname'
		WHERE `activation` > 0 ORDER BY `id` DESC LIMIT $start_pos, $perpage");
	}
add_title($action, 'module.users.site.title.userlist');
if(file_exists($theme->get_path().'userlist.php'))
	require_once $theme->get_path().'userlist.php';
else require UC_DEFAULT_THEMEPATH.'userlist.php';
?>
