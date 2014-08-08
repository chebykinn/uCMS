<?php
require 'plugins.php';
$plugin = new uPlugins(); //Запуск плагинов
if($user->has_access("plugins", 4) and (isset($_GET['no_plugins']) or isset($_SESSION['no_plugins']))){
	$_SESSION['no_plugins'] = true;
	echo "[NO PLUGINS MODE]<br>";
	if($user->has_access("plugins", 4) and isset($_GET['plugins'])) {
		unset($_SESSION['no_plugins']);
	}
}else{
	$plugin->deploy_activated();
	$event->do_actions("plugins.loaded");
}
?>