<?php
namespace uCMS\Core\Extensions\Users;
use uCMS\Core\Extensions\Theme;
use uCMS\Core\Admin\ControlPanel;
use uCMS\Core\Block;
use uCMS\Core\Settings;
class Users extends \uCMS\Core\Extensions\Extension implements \uCMS\Core\Extensions\IExtension {

	public function onLoad(){
		User::Current()->load();
	}

	public function onInstall($stage){
		Block::Add("user-card");
		Settings::Add("groups_amount", 1);
	}

	public function onUninstall(){

	}

	public function onShutdown(){

	}

	public function onAction($action){
		Theme::GetCurrent()->setTitle(tr("Profile of @s", User::Current()->getName()));
	}

	public function onAdminAction($action){
		$title = tr("Users");
		switch ($action) {
			case 'users/groups':
				$title = tr("Groups");
			break;
		}
		ControlPanel::SetTitle($title);
	}
}
?>