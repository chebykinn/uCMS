<?php
if($user->logged()){
   header("Location: ".UCMS_DIR."/");
}

if(file_exists(THEMEPATH.'reset.php'))
	require THEMEPATH.'reset.php';
else require GENERAL_TEMPLATES_PATH.'reset.php';
$udb->db_disconnect($con);
?>