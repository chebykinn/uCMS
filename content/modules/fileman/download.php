<?php
include get_module('path', 'fileman').'manage-files.php';
if(NICE_LINKS){
	$file = str_replace(UCMS_DIR.'/download/', "", $_SERVER['REQUEST_URI']);
}else
	$file = isset($_GET['file']) ? $_GET['file'] : false;
$id = $file ? ABSPATH.UPLOADS_PATH.$file : false;

download_file(urldecode($id));
exit;
?>