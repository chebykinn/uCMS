<?php
use uCMS\Core\Page;
use uCMS\Core\Setting;
use uCMS\Core\Extensions\Users\User;

if( !User::Current()->can('view user list') ){
	Page::GoBack();
}

$perPage = Setting::Get(Setting::PER_PAGE);
$users = (new User())->find(['limit' => $perPage]);
?>