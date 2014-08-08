<?php
$all_count = $args[0];
$users_count = $args[1];
$guests = $args[2];
$users = $args[3];

echo "<ul><li><b>".$ucms->cout("widget.site_stats.online", true)." $all_count</b></li>";
echo "<li><b>".$ucms->cout("widget.site_stats.online.users", true)." $users_count</b></li>";
echo "<li>";
for($i = 0; $i < $users_count; $i++){
	echo "<a href=\"".$user->get_profile_link('', $users[$i]['login'])."\">".(!empty($users[$i]['value']) ? $users[$i]['value'] : $users[$i]['login'])."</a>";
	if($i+1 < $users_count) echo ", ";
	if($i > 99) break;
}
echo "</li>";
echo "<li><b>".$ucms->cout("widget.site_stats.online.guests", true)." $guests</b></li>";

echo "</ul>";
?>