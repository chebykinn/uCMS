<?php
use uCMS\Core\Extensions\Extension;
use uCMS\Core\Settings;
use uCMS\Core\uCMS;
$users = (int) Settings::Get('users_amount');
$extensions = count(Extension::GetAll());
$coreVersion = uCMS::CORE_VERSION;
$stats = array(
	"Users" => $users,
	"Groups" => 0,
	"Categories" => 0,
	"Entries" => 0,
	"Pages" => 0,
	"Comments" => 0,
	"Links" => 0,
	"Extensions" => $extensions,
	"Themes" => 0,
	"Widgets" => 0
);
?>