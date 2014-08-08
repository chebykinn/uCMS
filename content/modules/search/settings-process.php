<?php
$event->bind_action("ucms.settings", "parse_search_in");

function parse_search_in($values){
	global $ucms;
	if(isset($_POST['searchin'])){
		if(is_array($_POST['searchin'])){
			$value = implode(",", $_POST['searchin']);
		}
		else{
			$value = $_POST['searchin'];
		}
		$upd = $ucms->update_setting("search_in", $value);
		$ucms->updated_settings[] = 'search_in';
	}else{
		$upd = $ucms->update_setting("search_in", '');
		$upd = $ucms->update_setting("default_search_module", '');
		$ucms->updated_settings[] = 'search_in';
		$ucms->updated_settings[] = 'default_search_module';
	}
	$ucms->update_setting("results_on_page", intval($values['results_on_page']));
	$ucms->updated_settings[] = 'results_on_page';
}
?>