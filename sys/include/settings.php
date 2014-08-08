<?php
/*Настройки*/
$update_user = $udb->get_row("SELECT `value` FROM `".UC_PREFIX."stats` WHERE `id` = '2'");
if(!defined(UPDATE_USER))
	define(UPDATE_USER, $update_user['value']);
$setting = $udb->get_rows("SELECT * FROM `".UC_PREFIX."settings`");

if($setting and count($setting) > 1){
	/* Константы с @uCMS 1.2 */
	define_settings();
	date_default_timezone_set(UCMS_TIMEZONE);
}else{
	header("Location: /sys/install/index.php");
	exit;
}

?>