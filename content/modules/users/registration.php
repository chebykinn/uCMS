<?php
if ($user->logged()) header("Location: ".UCMS_DIR."/");
require_once 'register.php';
$reg = new register();
add_title($action, 'module.users.site.title.registration');
if(file_exists($theme->get_path().'registration.php'))
	require $theme->get_path().'registration.php';
else require GENERAL_TEMPLATES_PATH.'registration.php';
?>