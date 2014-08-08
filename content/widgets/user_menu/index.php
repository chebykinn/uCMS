<?php
global $url_all;
if(!class_exists("uSers")){
	echo "<br>".$ucms->cout("widget.user_menu.users_module_off", true)."<br><br>";
	return false;
}else{
	$ucms->template($this->get("path", 'user_menu')."user-menu.php");
}
?>