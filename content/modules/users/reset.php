<?php
if($user->logged()){
   header("Location: ".UCMS_DIR."/");
}
add_title($action, 'module.users.site.title.reset');
if(file_exists($theme->get_path().'reset.php'))
	require $theme->get_path().'reset.php';
else require GENERAL_TEMPLATES_PATH.'reset.php';
?>