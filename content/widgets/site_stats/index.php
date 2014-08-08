<?php
$users = $udb->get_rows("SELECT * FROM `".UC_PREFIX."users` WHERE `online` = 1 AND `activation` = 1");
if(!$users) $users_count = 0; else $users_count = count($users);
$guests = $user->get_guests_count();
$all_count = $users_count+$guests;
echo "<ul><li><b>Сейчас онлайн: ".$all_count."</b></li>";
echo "<li><b>Пользователей: ".$users_count." </b></li>";
for($i = 0; $i < $users_count; $i++){
	echo "<li><a href=\"".$user->get_profile_link($users[$i]['id'])."\">".$users[$i]['login']."</a></li>";
}
echo "<li><b>Гостей: ".$guests."</b></li>";
echo "</ul>";
?>