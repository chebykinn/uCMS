<?php
require get_module('path', 'themes').'themes.php';
$theme = new uThemes();
$ucms->set_language($theme->get_path().'languages/'.SYSTEM_LANGUAGE.'.lang');
?>