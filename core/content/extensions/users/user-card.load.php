<?php
use uCMS\Core\Extensions\Users\User;
use uCMS\Core\Page;
$user = User::Current();
$cpanelLink = Page::ControlPanel();
$profileLink = ""; //$user->get_profileLink();
$userlistLink = ""; //$user->get_userlistLink();			
$logoutLink = ""; //$user->getLogoutLink();
$cpanelLink = Page::ControlPanel();
?>