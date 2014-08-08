<?php
$event->bind_action("ucms.settings", "pages_settings");
function pages_settings($values){
	global $ucms;
	if(isset($values['pages_observed_user_groups'])){
		$observed_user_groups = implode(",", $values['pages_observed_user_groups']);
		$ucms->update_setting('pages_observed_user_groups', $observed_user_groups);
		$ucms->updated_settings[] = 'pages_observed_user_groups';
	}
}
?>