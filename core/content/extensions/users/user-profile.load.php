<?php
use uCMS\Core\Extensions\Users\User;
use uCMS\Core\Extensions\Users\UserInfoField;
use uCMS\Core\Extensions\Menus\Menu;
use uCMS\Core\Extensions\Menus\MenuLink;
use uCMS\Core\Page;
use uCMS\Core\Settings;
$user = (new User())->find(['name' => Page::GetCurrent()->getActionValue(), 'limit' => 1]);
$adminEditLink = Page::ControlPanel("users/edit/$user->uid");
$logoutLink = Page::FromAction(User::LOGOUT_ACTION);

$profileMenu = (new Menu())->clean();

$links = [];
$infoPageLink = (new MenuLink())->clean();
$infoPageLink->title = tr('Profile');
$infoPageLink->link = Page::FromAction(User::PROFILE_ACTION, $user->name);
$links[] = $infoPageLink;

$editLink = (new MenuLink())->clean();
$editLink->title = tr('Edit info');
$editLink->link = Page::FromAction(User::PROFILE_ACTION, ($user->name).'/edit');
$links[] = $editLink;

if( Settings::Get('enable_user_messaging') ){
	$messagesLink = (new MenuLink())->clean();
	$messagesLink->title = tr('Messages');
	$messagesLink->link = Page::FromAction(User::PROFILE_ACTION, ($user->name).'/messages');
	$links[] = $messagesLink;
}

// TODO: Event or something to allow extending this menu

$profileMenu->links = $links;
?>