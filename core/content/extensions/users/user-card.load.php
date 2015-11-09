<?php
use uCMS\Core\Extensions\Users\User;
use uCMS\Core\Page;
$user = User::Current();
$cpanelLink = Page::ControlPanel();
$profileLink = Page::FromAction(User::PROFILE_ACTION, $user->name);
$userlistLink = Page::FromAction(User::LIST_ACTION);		
$logoutLink = Page::FromAction(User::LOGOUT_ACTION);
$cpanelLink = Page::ControlPanel();
$loginForm = User::GetLoginForm();
?>