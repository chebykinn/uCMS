<?php
use uCMS\Core\Settings;
use uCMS\Core\Extensions\Menus\MenuLink;
$selectedMenu = Settings::Get('selected_menu');
$links = (new MenuLink())->find(['menu' => $selectedMenu, 'orders' => ['lid' => "ASC"]]);
?>