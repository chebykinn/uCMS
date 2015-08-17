<?php
namespace uCMS\Core\Admin;
use uCMS\Core\Page;
use uCMS\Core\Extensions\Users\User;
use uCMS\Core\Extensions\Extension;
class ManagePage{
	private $adminAction;
	private $actions = array();

	public function __construct(){
		$this->adminAction = ControlPanel::GetBaseAction();
	}


	public function addAction($name, $permission, $callback, $args = array()){
		$this->actions[$name] = array('callback' => $callback, 'args' => $args, 'permission' => $permission);
	}

	public function doActions(){
		$name = Page::GetCurrent()->getKeyValue($this->adminAction);
		$exitPage = Page::ControlPanel($this->adminAction);
		if( !empty($this->actions[$name]) ){
			$key = htmlspecialchars(Page::GetCurrent()->getKeyValue($name));
			$user = User::Current();
			if( empty($key) || !$user->can($this->actions[$name]['permission']) || !is_callable($this->actions[$name]['callback']) ){
				$exitPage->go();
				return false;
			}
			$notification = (int)call_user_func_array($this->actions[$name]['callback'], array_merge(array($key), $this->actions[$name]['args']));
			
			$exitPage->go();
		}
	}

	public function editForm($data){

	}

	public function add($p){

	}

	public function update($p){

	}

	public function delete($p){

	}
}
?>