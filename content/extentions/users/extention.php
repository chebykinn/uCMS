<?php
class Users extends Extention{

	public function install(){

	}

	public function uninstall(){

	}

	public function load(){
		parent::load();
		UserManagement::loadUser();
		$id = User::current()->getID();
		$name = User::current()->getName();
		$email = User::current()->getEmail();
		/*echo "You: $id $name $email ". User::current()->getGroup()->getName();
		echo " ".User::current()->isLoggedIn();

		echo '<br>';

		varDump(User::current()->can('access site'));*/
	}

	public function doAction($action){
		return array("template" => 'profile', "title" => tr("Profile of @s", User::current()->getName()));
	}

	public function doAdminAction($action){
		return $action;
	}

}
?>