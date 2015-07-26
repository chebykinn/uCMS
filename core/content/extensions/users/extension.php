<?php
namespace uCMS\Core\Extensions\Users;
use uCMS\Core\Extensions\Theme;
use uCMS\Core\Admin\ControlPanel;
use uCMS\Core\Block;
class Users extends \uCMS\Core\Extensions\Extension implements \uCMS\Core\Extensions\IExtension {

	public function onLoad(){
		User::Current()->load();
	}

	public function onInstall(){
		Block::Add("user-card");
	}

	public function onUninstall(){

	}

	public function onShutdown(){

	}

	public function onAction($action){
		Theme::GetCurrent()->setTitle(tr("Profile of @s", User::Current()->getName()));
	}

	public function onAdminAction($action){
		$title = "";
		switch ($action) {
			case 'users':
				$title = tr("Users");
			break;

			case 'users/groups':
				$title = tr("Groups");
			break;

			case 'settings/users':
				$title = tr("Users");
			break;
		}
		ControlPanel::SetTitle($title);
	}
}
?>