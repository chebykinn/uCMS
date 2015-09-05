<?php
use uCMS\Core\Page;
use uCMS\Core\Settings;
use uCMS\Core\uCMS;
use uCMS\Core\Database\DatabaseConnection;
use uCMS\Core\Loader;
use uCMS\Core\Extensions\Users\User;
use uCMS\Core\Admin\ControlPanel;

$action = Page::GetCurrent()->getAction();
$adminAction = ControlPanel::GetAction();
$siteName = Settings::Get("site_name");
$siteDescription = Settings::Get("site_description");
$queriesCount = DatabaseConnection::GetDefault()->getQueriesCount(); //?
$loadTime = Loader::GetInstance()->getLoadTime(); //?
$coreVersion = uCMS::CORE_VERSION; //?
$currentUser = User::Current(); //?
$homePage = Page::Home()->getURL();
?>