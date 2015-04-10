<?php
if(file_exists('config.php')){
	require 'config.php';
	
}else{
	// TODO: install
}
uCMS::getInstance()->init();

uCMS::getInstance()->runSite();
?>