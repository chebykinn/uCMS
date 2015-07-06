<?php
class Users extends Extension{

	public function onInstall(){

	}

	public function onUninstall(){

	}

	public function onLoad(){
		parent::onLoad();
		User::current()->load();
		
	}

	public function onAction($action){
		Theme::GetCurrent()->setTitle(tr("Profile of @s", User::current()->getName()));
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