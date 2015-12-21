<?php
use uCMS\Core\Extensions\Users\User;
use uCMS\Core\Extensions\Menus\Menu;
use uCMS\Core\Page;
$user = User::Current();
$userMenu = (new Menu())->find('user-menu');
$loginForm = User::GetLoginForm();
?>