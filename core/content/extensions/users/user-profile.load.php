<?php
use uCMS\Core\Extensions\Users\User;
use uCMS\Core\Extensions\Users\UserInfoField;
use uCMS\Core\Extensions\Menus\Menu;
use uCMS\Core\Extensions\Menus\MenuLink;
use uCMS\Core\Page;
use uCMS\Core\Setting;

if( !User::Current()->can('view user profiles') ){
	Page::GoBack();
}

$user = (new User())->find(['name' => Page::GetCurrent()->getActionValue(), 'limit' => 1]);
$adminEditLink = Page::ControlPanel("users/edit/$user->uid");
$logoutLink = Page::FromAction(User::LOGOUT_ACTION);

$isOwnProfile = ($user->name == User::Current()->name);

$profileMenu = (new Menu())->emptyRow();

$links = [];
$infoPageLink = (new MenuLink())->emptyRow();
$infoPageLink->title = $this->tr('Profile');
$infoPageLink->link = Page::FromAction(User::PROFILE_ACTION, $user->name);
$links[] = $infoPageLink;

$editLink = (new MenuLink())->emptyRow();
$editLink->title = $this->tr('Edit info');
$editLink->link = Page::FromAction(User::PROFILE_ACTION, ($user->name).'/edit');
$links[] = $editLink;

if( Setting::Get('enable_user_messaging') ){
	$messagesLink = (new MenuLink())->emptyRow();
	$messagesLink->title = $this->tr('Messages');
	$messagesLink->link = Page::FromAction(User::PROFILE_ACTION, ($user->name).'/messages');
	$links[] = $messagesLink;
}

// TODO: Event or something to allow extending this menu

$profileMenu->links = $links;
?>