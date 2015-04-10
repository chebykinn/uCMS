<?php
class UserManagement{

	public static function addUser($user){
		//add user
		if( is_object($user) ){
			$name = $user->getName();
			$password = $user->getPassword();
			$email = $user->getEmail();
			$groupID = $user->getGroup()->getID();
			if( empty($name) || empty($password) || empty($email) || empty($groupID) ){
				return;
			}
			$query = new Query('{users}');
			$query->insert( array("uid" => "NULL",
								  "name" => $user->getName(),
								  "password" => $user->getPassword(),
								  "email" => $user->getEmail(),
								  "status" => $user->getStatus(),
								  "gid" => $user->getGroup()->getID(),
								  "theme" => $user->getTheme(),
								  "avatar" => $user->getAvatar(),
								  "language" => $user->getLanguage(),
								  "ip" => Session::getCurrent()->getIPAddress()) )->execute();
		}
	}

	public static function updateUser($user){

	}

	public static function deleteUser($userID){

	}

	public static function loginUser($userID, $saveCookies = false){ //private
		if( !self::userExists($userID) ) return false; // fail if user doesn't exists
		if( Session::getCurrent()->getUID() === intval($userID) ) return false; //fail if user already logged in
		if( Session::getCurrent()->getID() != Session::getCurrent()->get('hash') ) Session::getCurrent()->delete('hash');
		$sid = Session::getCurrent()->getID();
		$hash = md5(self::generateSessionHash(10));
		$updateSession = new Query('{sessions}');
		$updateSession->update( array('sid' => $hash, 'uid' => $userID) )->where()->condition('sid', '=', $sid)->execute();
		Session::getCurrent()->set('hash', $hash);
		//save cookies if needed
		if($saveCookies){
			Session::getCurrent()->setCookie('hash', $hash, time() + 60 * 60 * 24 * 30); //save cookie for year
			Session::getCurrent()->setCookie('id', $userID, time() + 60 * 60 * 24 * 30); //save cookie for year
		}
	}

	public static function logoutUser($userID){
		Session::getCurrent()->deleteCookie('hash');
		Session::getCurrent()->destroy();
	}

	public static function activateUser($userID){
		//?
	}

	public static function addGroup($group){
		$groupName = $group->getName();
		if( is_object($group) && !empty($groupName) ){
			$query = new Query('{groups}');
			$query->insert( array("gid" => "NULL",
								  "name" => $group->getName(),
								  "position" => $group->getPosition()) )->execute();
			/** 
			* @todo add permissions
			*/
		}
	}

	public static function updateGroup($group){

	}

	public static function deleteGroup($groupID){

	}

	public static function encryptPassword($password){
		$salt = substr(sha1($password), 0, 22);
		$password = crypt($password, '$2a$10$'.$salt);
		return $password;
	}

	public static function loadUser(){
		$uid = Session::getCurrent()->getUID();
		$hash = Session::getCurrent()->getID();
		$currentHash =  Session::getCurrent()->get('hash');
		$savedHash = Session::getCurrent()->getCookie('hash');
		$savedID = Session::getCurrent()->getCookie('id');
		if( $uid > 0 && $hash === $currentHash ){
			User::current($uid); //set current user to $uid
			return;
		}else{
			if( $hash !== $savedHash ){
				Session::getCurrent()->delete('hash');
			}
		}
		if( $savedID > 0 && !empty($savedHash) ){
			User::current($savedID);
			$uid = User::current()->getID();
			if( empty($uid) ){ // we got wrong cookies
				Session::getCurrent()->deleteCookie('hash');
				Session::getCurrent()->deleteCookie('id');
				return;
			}
			$updateSession = new Query('{sessions}');
			$updateSession->update( array('sid' => $hash, 'uid' => $savedID) )->where()->condition('sid', '=', $hash)->execute();
			Session::getCurrent()->set('hash', $hash); // if we restored hash from cookie we should create session with this hash
		}
	}

	private static function generateSessionHash($length = 10){
		$chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHI JKLMNOPRQSTUVWXYZ0123456789";
		$code = "";
		$clen = strlen($chars) - 1;  
		while (strlen($code) < $length) {
			$code .= $chars[mt_rand(0,$clen)];  
		}
		return $code;
	}

	public static function userExists($uid){
		$check = new Query('{users}');
		$user = $check->select('uid')->where()->condition('uid', '=', $uid)->execute();
		return !empty($user);
	}

	public static function grantPermission($name, $group){
		if( !is_object($group) ) return false;
		if( !$group->hasPermission($name) ){
			$check = new Query('{group_permissions}');
			$data = $check->select('owner')->where()->condition('name', '=', $name)->limit(1)->execute(); //add query method to check
			if(count($data) > 0){
				$add = new Query('{group_permissions}');
				$add->insert(array('gid' => $group->getID(), 'name' => $name, 'owner' => $data[0]['owner']))->execute();
			}
		}
	}

	public static function denyPermission($name, $group){
		if( !is_object($group) ) return false;
		if( $group->hasPermission($name) ){
			$query = new Query('{group_permissions}');
			$query->delete()->where()->condition('gid', '=', $group->getID())->execute();
		}
	}
}
?>