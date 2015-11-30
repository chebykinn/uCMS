<?php
namespace uCMS\Core\Extensions;
interface ExtensionInterface{
	public function onLoad();
	public function onInstall($stage);
	public function onUninstall();
	public function onShutdown();
	public function onAction($action);
	public function onAdminAction($action);
}
?>