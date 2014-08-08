<?php
$event->bind_action("ucms.settings", "users_settings");
function users_settings($values){
	global $ucms, $udb;
	if(isset($values['observed_user_groups'])){
		$observed_user_groups = implode(",", $values['observed_user_groups']);
		$ucms->update_setting('observed_user_groups', $observed_user_groups);
		$ucms->updated_settings[] = 'observed_user_groups';
	}
	if(isset($values['allow_nicknames']) and $values['allow_nicknames'] == 0){
		$udb->query("DELETE FROM `".UC_PREFIX."usersinfo` WHERE `name` = 'nickname'");
	}

	$ints = array("login_attempts_num", "login_min_size", "login_max_size", "password_min_size", "password_max_size", "avatar_width", "avatar_height");
	foreach ($ints as $int) {
		if(isset($values[$int])){
			$values[$int] = (int) $values[$int];
			$ucms->update_setting($int, $values[$int]);
			$ucms->updated_settings[] = $int;
		}
	}
}
?>