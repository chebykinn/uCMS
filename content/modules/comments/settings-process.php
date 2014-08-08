<?php
$event->bind_action("ucms.settings", "comments_settings");
function comments_settings($values){
	global $ucms;
	if(isset($values['comments_observed_user_groups'])){
		$observed_user_groups = implode(",", $values['comments_observed_user_groups']);
		$ucms->update_setting('comments_observed_user_groups', $observed_user_groups);
		$ucms->updated_settings[] = 'comments_observed_user_groups'; 
	}
	if(isset($values['comments_moderation'])){
		$comments_moderation = implode(",", $values['comments_moderation']);
		$ucms->update_setting('comments_moderation', $comments_moderation);
		$ucms->updated_settings[] = 'comments_moderation'; 
	}
	if($values['comments_on_page'] < 1) $values['comments_on_page'] = 1;
	$ucms->update_setting("comments_on_page", intval($values['comments_on_page']));
	$ucms->updated_settings[] = 'comments_on_page';
}
?>