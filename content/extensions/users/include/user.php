<?php
class User{
	protected $uid;
	protected $name;
	protected $password;
	protected $email;
	protected $status;
	protected $group;
	protected $theme;
	protected $avatar;
	protected $language;
	protected $created;
	protected $lastlogin;
	protected $visited;
	protected $hash;
	protected $info;
	protected static $currentUser;
	
	public static function current($id = 0){
		if ( is_null( self::$currentUser ) ){
			self::$currentUser = new self($id);
		}
		return self::$currentUser;
	}

	public function __construct($id){
		$data = array();
		$fromArray = false;
		if( !empty($id) ){
			if( is_array($id) ){
				$data = $id;
				$fromArray = true;
			}else{
				if($id == 0){
					return; //it is guest user
				}
				$query = new Query('{users}');
				$rows = $query->select('*', true)->where()->condition('uid', '=', $id)->_or()
				                                          ->condition('name', '=', $id)->limit(1)->execute();
				if(count($rows) == 1){
					$data = $rows[0];
				}

				/**
				* @todo load user info
				*/
			}
		}
		$fields = array_keys( get_object_vars($this) );
		foreach ($data as $field => $value) {
			if(  in_array($field, $fields) ){
				if( $field == 'password' && $fromArray ){ // if user object was created from array we should encrypt given password 
					$value = UserManagement::encryptPassword($value);
				}
				
				$this->$field = $value;
			}else{
				if($field == 'gid'){ // if we got group id from base we should create group object
					$this->group = new Group($value);
				}
			}
		}
	}

	public function getID(){
		if( !empty($this->uid) ){
			return $this->uid;
		}
		return 0;
	}

	public function getName(){
		if( !empty($this->name) ){
			return $this->name;
		}
		return "";
	}

	public function getPassword(){
		if( !empty($this->password) ){
			return $this->password;
		}
		return "";
	}

	public function getEmail(){
		if( !empty($this->email) ){
			return $this->email;
		}
		return "";
	}

	public function getGroup(){
		if( !empty($this->group) ){
			return $this->group;
		}
		return new Group(0); // should be guest group?
	}

	public function getStatus(){
		if( !empty($this->status) ){
			return $this->status;
		}
		return "";
	}

	public function getTheme(){
		if( !empty($this->theme) ){
			return $this->theme;
		}
		return "";
	}

	public function getAvatar(){
		if( !empty($this->avatar) ){
			return $this->avatar;
		}
		return "";
	}

	public function getLanguage(){
		if( !empty($this->language) ){
			return $this->language;
		}
		return "";
	}

	public function getInfo($field){
		if( !empty($this->info) ){
			return $this->info;
		}
		return "";
	}

	public function isLoggedIn(){
		$sid = Session::getCurrent()->getUID();
		return ( !empty($this->uid) && !empty($this->name) && !empty($sid) );
	}

	public function can($permission){
		if( is_object($this->group) ){
			return $this->group->hasPermission($permission);
		}
		return false;
	}

	public static function add($user){
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

	public static function Authorize($userID, $saveCookies = false){ //private
		if( !self::isExists($userID) ) return false; // fail if user doesn't exists
		if( Session::getCurrent()->isAuthorized() ){
			if( Session::getCurrent()->getUID() === intval($userID) ) return false; //fail if user already logged in
			else{
				Session::getCurrent()->Deauthorize(); // user got wrong cache saved
			}
		}
		$hash = generate_hash();
		$updateSession = new Query("{sessions}");
		$updated = $saveCookies ? 0 : time();
		$updateSession->insert( array('sid' => $hash, 'uid' => $userID, 'ip' => Session::getCurrent()->getIPAddress(), 'updated' => $updated) )->execute();
		$lastlogin = new Query("{users}");
		$lastlogin->update(array('lastlogin' => time()))->where()->condition("uid", '=', $userID)->execute();
		Session::getCurrent()->Authorize($hash);
		//save cookies if needed
		if($saveCookies){
			Session::getCurrent()->setCookie('usid_saved', $hash); //save cookie for year
		}
	}

	public static function Deauthorize($userID){
		Session::getCurrent()->deleteCookie('usid_saved');
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

	public function load(){
		$uid = Session::getCurrent()->getUID();
		$hash = Session::getCurrent()->getID();
		if( $uid > 0 && $hash != session_id() ){
			self::$currentUser = new User($uid); //set current user to $uid
			if(self::$currentUser->uid == 0){
				Session::getCurrent()->Deauthorize();
			}
		}
	}


	public static function isExists($uid){
		$check = new Query('{users}');
		$user = $check->select('uid')->where()->condition('uid', '=', $uid)->execute();
		return !empty($user);
	}
}
?>