<?php
class ManagePage{
	private $adminAction;
	private $actions = array();
	private $notifications = array();
	private $notificationTypes = array();

	public function __construct(){
		$this->adminAction = URLManager::getCurrentAdminAction();
		$this->notificationTypes = array('success', 'error', 'warning');
	}

	public function addNotification($name, $message, $type = ''){
		if( !in_array($type, $this->notificationTypes) ) {
			$type = $this->notificationTypes[SUCCESS_STATUS];
		}
		$this->notifications[$name][$type] = array('message' => $message);
	}

	public function showNotifications(){
		$name = URLManager::getKeyValue('alert');
		$exitLink = URLManager::makeLink(ADMIN_ACTION, $this->adminAction);

		if( !empty($this->notifications[$name]) ){
			$alert = URLManager::getKeyValue($name);
			if( !in_array($alert, $this->notificationTypes) ) {
				URLManager::redirect($exitLink);
				return false;
			}
			if( !isset($this->notifications[$name][$alert]) ) {
				URLManager::redirect($exitLink);
				return false;
			}
			echo '<div class="'.$alert.'">'.$this->notifications[$name][$alert]['message'].'</div>';
			return true;
		}
		if( !empty($name) ){
			URLManager::redirect($exitLink);
		}
	}

	public function addAction($name, $permission, $callback, $args = array()){
		$this->actions[$name] = array('callback' => $callback, 'args' => $args, 'permission' => $permission);
	}

	public function doActions(){
		$name = URLManager::getKeyValue($this->adminAction);
		$exitLink = URLManager::makeLink(ADMIN_ACTION, $this->adminAction);
		if( !empty($this->actions[$name]) ){
			$key = URLManager::getKeyValue($name);
			$user = User::current();
			if( empty($key) || !$user->can($this->actions[$name]['permission']) || !is_callable($this->actions[$name]['callback']) ){
				URLManager::redirect($exitLink);
				return false;
			}
			$status = (int)call_user_func_array($this->actions[$name]['callback'], array_merge(array($key), $this->actions[$name]['args']));
			$alert = ( isset($this->notificationTypes[$status]) ) ? $this->notificationTypes[$status] : $this->notificationTypes[SUCCESS_STATUS];
			$alertLink = URLManager::makeLink(ADMIN_ACTION, $this->adminAction."/alert/$name/$alert");
			URLManager::redirect($alertLink);
		}
		$alertValue = URLManager::getKeyValue('alert');
		if( !empty($name) && ($name != 'alert' || empty($alertValue)) ){
			URLManager::redirect($exitLink);
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