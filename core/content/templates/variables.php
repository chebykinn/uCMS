<?php
use uCMS\Core\Page;
use uCMS\Core\Settings;
use uCMS\Core\uCMS;
use uCMS\Core\Database\DatabaseConnection;
use uCMS\Core\Loader;
use uCMS\Core\Extensions\Users\User;
use uCMS\Core\Admin\ControlPanel;

$action = Page::GetCurrent()->getAction();
$siteName = Settings::Get("site_name");
$siteDescription = Settings::Get("site_description");
$queriesCount = array(DatabaseConnection::GetDefault(), 'getQueriesCount');//?
$loadTime = array(Loader::GetInstance(), 'getLoadTime'); //?
$coreVersion = uCMS::CORE_VERSION; //?
$currentUser = User::Current(); //?
$loginForm = User::GetLoginForm();
$homePage = Page::Home()->getURL();
$isPanel = ControlPanel::IsActive();
if( $isPanel ){
	$adminAction = ControlPanel::GetAction();
	$adminSidebar = ControlPanel::PrintSidebar();
	$adminPage = ControlPanel::LoadTemplate();
}else{
	$adminAction = "";
	$adminSidebar = "";
	$adminPage = "";
}
?>