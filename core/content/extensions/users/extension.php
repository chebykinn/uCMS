<?php
namespace uCMS\Core\Extensions\Users;
use uCMS\Core\Extensions\Theme;
use uCMS\Core\Admin\ControlPanel;
use uCMS\Core\Block;
use uCMS\Core\Page;
use uCMS\Core\Settings;
class Users extends \uCMS\Core\Extensions\Extension implements \uCMS\Core\Extensions\IExtension {

	public function onLoad(){
		User::CheckAuthorization();
	}

	public function onInstall($stage){
		Block::Add("login-form", "content", "", -1, Block::SHOW_MANUAL);
		Block::Add("login-form");
		Block::Add("user-card");
		Settings::Add("groups_amount", 1);
	}

	public function onUninstall(){

	}

	public function onShutdown(){

	}

	public function onAction($action){
		switch ($action) {
			case 'login':
				if( !User::Current()->isLoggedIn() ){
					if( User::IsAuthenticationRequested() ){
						$success = User::Authenticate($_POST['login'], $_POST['password'], $_POST['save_cookies']);
						if( $success ){
							Page::GoBack();
						}
						Page::Refresh();
					}else{
						Theme::GetCurrent()->setPageTitle(tr("Login"));
					}
				}else{
					Page::GoBack();
				}
			break;

			case 'logout':
				User::Deauthorize();
				Page::GoBack();
			break;
			
			case 'profile':
				Theme::GetCurrent()->setTitle(tr("Profile of @s", User::Current()->getName()));
			break;
		}
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