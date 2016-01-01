<?php
use uCMS\Core\Extensions\ExtensionHandler;
use uCMS\Core\Extensions\Theme;
use uCMS\Core\Extensions\ThemeHandler;
use uCMS\Core\Setting;
use uCMS\Core\uCMS;
use uCMS\Core\Tools;
use uCMS\Core\Block;
$users = (int) Setting::Get('users_amount');
$groups = (int) Setting::Get('groups_amount');
$entries = (int) Setting::Get('entries_amount');
$categories = (int) Setting::Get('categories_amount');
$comments = (int) Setting::Get('comments_amount');
$blocks = (int) Setting::Get('blocks_amount');
$extensions = count(ExtensionHandler::GetList());
$themes = count(ThemeHandler::GetList());
$coreVersion = uCMS::CORE_VERSION;
$siteName = Setting::Get('site_name');
$currentTime = Tools::FormatTime();
$currentTheme = (new Theme(Setting::Get('theme')))->getInfo('displayname');
$domain = uCMS::GetDomain();
$directory = uCMS::GetDirectory();
$stats = [
	"Users" => $users,
	"Groups" => $groups,
	"Categories" => $categories,
	"Entries" => $entries,
	"Comments" => $comments,
	"Extensions" => $extensions,
	"Themes" => $themes,
	"Blocks" => $blocks
];
?>