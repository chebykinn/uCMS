<?php
$event->bind_action("ucms.settings", "pages_settings");
function pages_settings($values){
	global $ucms;
	if(isset($values['posts_observed_user_groups'])){
		$observed_user_groups = implode(",", $values['posts_observed_user_groups']);
		$ucms->update_setting('posts_observed_user_groups', $observed_user_groups);
		$ucms->updated_settings[] = 'posts_observed_user_groups';
	}
	$ucms->update_setting("posts_on_page", intval($values['posts_on_page']));
	$ucms->updated_settings[] = 'posts_on_page';
}
?>