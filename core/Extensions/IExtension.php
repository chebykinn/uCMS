<?php
namespace uCMS\Core\Extensions;
interface IExtension{
	public function onLoad();
	public function onInstall();
	public function onUninstall();
	public function onShutdown();
	public function onAction($action);
	public function onAdminAction($action);
}
?>