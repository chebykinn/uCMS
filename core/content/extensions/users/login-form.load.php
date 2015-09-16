<?php
use uCMS\Core\Extensions\Users\User;
use uCMS\Core\Page;
$show = false;
if( !User::Current()->isLoggedIn() && Page::GetCurrent()->getAction() == User::LOGIN_ACTION ){
	$form = User::GetLoginForm();
	$show = true;
}
?>