<?php
use uCMS\Core\Extensions\Extension;
use uCMS\Core\Extensions\Theme;
use uCMS\Core\Settings;
use uCMS\Core\uCMS;
use uCMS\Core\Block;
$users = (int) Settings::Get('users_amount');
$groups = (int) Settings::Get('groups_amount');
$entries = (int) Settings::Get('entries_amount');
$categories = (int) Settings::Get('categories_amount');
$blocks = (int) Settings::Get('blocks_amount');
$extensions = count(Extension::GetAll());
$themes = count(Theme::GetAll());
$coreVersion = uCMS::CORE_VERSION;
$stats = array(
	"Users" => $users,
	"Groups" => $groups,
	"Categories" => $categories,
	"Entries" => $entries,
	"Comments" => 0,
	"Extensions" => $extensions,
	"Themes" => $themes,
	"Blocks" => $blocks
);
?>