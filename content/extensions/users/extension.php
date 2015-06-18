<?php
class Users extends Extension{

	public function onInstall(){

	}

	public function onUninstall(){

	}

	public function onLoad(){
		parent::onLoad();
		User::current()->load();
		/*echo "You: $id $name $email ". User::current()->getGroup()->getName();
		echo " ".User::current()->isLoggedIn();

		echo '<br>';

		varDump(User::current()->can('access site'));*/
	}

	public function onAction($action){
		return array("template" => $action, "title" => tr("Profile of @s", User::current()->getName()));
	}

	public function onAdminAction($action){
		$title = "";
		switch ($action) {
			case 'users':
				$title = tr("Users");
			break;

			case 'groups':
				$title = tr("Groups");
			break;

			case 'settings/users':
				$title = tr("Users");
			break;
		}
		return $title;
	}
}
?>