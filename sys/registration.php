<?php
if ($user->logged()) header("Location: ".UCMS_DIR."/");
require_once 'sys/users/register.php';
$reg = new register();
if(file_exists(THEMEPATH.'registration.php'))
	require THEMEPATH.'registration.php';
else require GENERAL_TEMPLATES_PATH.'registration.php';

$udb->db_disconnect($con);
?>
