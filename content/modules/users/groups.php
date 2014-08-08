<?php
/**
 *
 * uCMS User Groups
 * @package uCMS Users Groups
 * @since uCMS 1.1
 * @version uCMS 1.3
 *
*/
class group extends uSers{

	function __construct($user){
		$this->id 			= $user->id;
		$this->login 		= $user->login;
		$this->password 	= $user->password;
		$this->group 		= $user->group;
		$this->avatar 		= $user->avatar;
		$this->email 		= $user->email;
		$this->activation 	= $user->activation;
		$this->date 		= $user->date;
		$this->session_hash = $user->session_hash;
		$this->regip 		= $user->regip;
		$this->logip 		= $user->logip;
		$this->online 		= $user->online;
		$this->lastlogin 	= $user->lastlogin;
	}

	/**
	 *
	 * Get user permissions for given $user_id or for current user
	 * @package uCMS
	 * @since uCMS 1.2
	 * @version uCMS 1.3
	 * @return array
	 *
	*/
	function get_user_permissions($user_id = 0){
		global $udb;
		if($user_id <= 0){
			$user_id = $this->get_user_id();
			$permissions = CURRENT_USER_GROUP_PERMISSIONS;
		}else{
			$group = $this->get_user_group($user_id);
			$permissions = $this->get_group_permissions($group, true);
		}
		$parray = explode(",", $permissions);
		for ($i = 0; $i < count($parray); $i++) { 
			$module = explode(":", $parray[$i]);
			if( !empty($module[0]) and isset($module[1]) and $module[1] != ""){
				$permissions_array[$module[0]] = $module[1];
			}
		}
		return $permissions_array;
	}

	/**
	 *
	 * Get user permission by module $id for given $user_id or for current user
	 * @package uCMS
	 * @since uCMS 1.2
	 * @version uCMS 1.3
	 * @return string
	 *
	*/
	function get_permission($id, $user_id = 0){
		$permissions = $this->get_user_permissions($user_id);
		if(!is_module($id)) return false;
		if(isset($permissions[$id])){
			return $permissions[$id];
		}
	}

	/**
	 *
	 * Update permission by module $id to new $level for given $user_id or for current user
	 * @package uCMS
	 * @since uCMS 1.3
	 * @version uCMS 1.3
	 * @return bool
	 *
	*/
	function update_permission($id, $level, $user_id = 0){
		global $udb;
		if($user_id <= 0) {
			$user_id = $this->get_user_id();
			$group = $this->get_user_group();
		}else{
			$group = $this->get_user_group($user_id);
		}
		$permissions = $this->get_user_permissions($user_id);
		if(isset($permissions[$id])){
			$permissions[$id] = (int) $level;
			$perms = $this->get_permissions_string($permissions);
			$upd = $udb->query("UPDATE `".UC_PREFIX."groups` SET `permissions` = '$perms' WHERE `id` = '$group'");
			return $upd;
		}
		return false; 
	}

	/**
	 *
	 * Add permission by module $id with $level
	 * @package uCMS
	 * @since uCMS 1.3
	 * @version uCMS 1.3
	 * @return bool
	 *
	*/
	function add_permission($id, $level){
		global $udb;
		if(!is_module($id)) return false;
		$group = $udb->get_rows("SELECT * FROM `".UC_PREFIX."groups`");
		if($group and count($group) > 0){
			for ($i = 0; $i < count($group); $i++) { 
				$permissions = $this->get_permissions_array($group[$i]['permissions']);
				if(!isset($permissions[$id])){
					$permissions[$id] = $level;
					$perms = $this->get_permissions_string($permissions);
					$udb->query("UPDATE `".UC_PREFIX."groups` SET `permissions` = '$perms' WHERE `id` = '".$group[$i]['id']."'");
				}
			}
			return true;
		}
		return false;
	}

	/**
	 *
	 * Delete permission by module $id
	 * @package uCMS
	 * @since uCMS 1.3
	 * @version uCMS 1.3
	 * @return bool
	 *
	*/
	function delete_permission($id){
		global $udb;
		$group = $udb->get_rows("SELECT * FROM `".UC_PREFIX."groups`");
		if($group and count($group) > 0){
			for ($i = 0; $i < count($group); $i++) { 
				$permissions = $this->get_permissions_array($group[$i]['permissions']);
				if(isset($permissions[$id])){
					$permissions[$id] = "";
					$perms = $this->get_permissions_string($permissions);
					$udb->query("UPDATE `".UC_PREFIX."groups` SET `permissions` = '$perms' WHERE `id` = '".$group[$i]['id']."'");
				}else{
					return false;
				}
			}
			return true;
		}
		return false;
	}

	/**
	 *
	 * Get permissions of group by its $id in a form of array or $plain string
	 * @package uCMS
	 * @since uCMS 1.3
	 * @version uCMS 1.3
	 * @return array or string
	 *
	*/
	function get_group_permissions($group_id, $plain = false){
		global $udb;
		$group_id = (int) $group_id;
		$permissions = $udb->get_row("SELECT `permissions` FROM `".UC_PREFIX."groups` WHERE `id` = '$group_id' LIMIT 1");
		if($permissions){
			if($plain)
				return $permissions['permissions'];
			else{
				return $this->get_permissions_array($permissions['permissions']);
			}
		}
		return false;
	}

	/**
	 *
	 * Get array of permissions from given $permissions string
	 * @package uCMS
	 * @since uCMS 1.3
	 * @version uCMS 1.3
	 * @return array
	 *
	*/
	function get_permissions_array($permissions){
		$parray = explode(",", $permissions);
		for ($i = 0; $i < count($parray); $i++) { 
			$module = explode(":", $parray[$i]);
			if(!empty($module[0]) and $module[1] != "")
				$permissions_array[$module[0]] = $module[1];
		}
		return $permissions_array;
	}

	/**
	 *
	 * Get string of permissions from given $permissions_array
	 * @package uCMS
	 * @since uCMS 1.3
	 * @version uCMS 1.3
	 * @return array
	 *
	*/
	function get_permissions_string($permissions_array){
		$modules = array_keys($permissions_array);
		$permissions = "";
		for($i = 0; $i < count($modules); $i++){
			if( !empty($permissions_array[$modules[$i]]) ){
				$permissions .= $modules[$i].':'.$permissions_array[$modules[$i]];
				if($i+1 != count($modules)) $permissions .= ",";
			}
		}
		return $permissions;
	}
}
?>