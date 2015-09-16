<?php
namespace uCMS\Core\Extensions\Users;
use uCMS\Core\Session;
use uCMS\Core\Settings;
use uCMS\Core\Database\Query;
use uCMS\Core\Object;
use uCMS\Core\Tools;
use uCMS\Core\Form;
use uCMS\Core\Page;
use uCMS\Core\Notification;
class User extends Object{
	const AVATARS_PATH = 'content/uploads/avatars';
	const LOGIN_ACTION = 'login';
	const LOGOUT_ACTION = 'logout';
	protected $uid;
	protected $name;
	protected $password;
	protected $email;
	protected $status;
	protected $group;
	protected $theme;
	protected $avatar;
	protected $language;
	protected $timezone;
	protected $created;
	protected $lastlogin;
	protected $visited;
	protected $hash;
	protected $info;
	protected static $currentUser;
	
	public static function Current($id = 0){
		if ( is_null( self::$currentUser ) ){
			self::$currentUser = new self($id);
		}
		return self::$currentUser;
	}

	public function __construct($id = 0){
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
				if( $field == 'password' && $fromArray ){ 
					// If user object was created from array we should encrypt given password 
					$value = User::EncryptPassword($value);
				}
				
				$this->$field = $value;
			}else{
				if($field == 'gid'){
					// If we got group id from base we should create group object
					$this->group = new Group($value);
				}
			}
		}
	}

	public static function FromArray($data, $prefixes = array(), $namespaces = array(), $returnClass = "\\uCMS\Core\Extensions\Users\\User"){
		$prefixes = array("group" => 'Group');
		$namespaces = array("Group" => __NAMESPACE__);
		$user = parent::FromArray($data, $prefixes, $namespaces, $returnClass);
		// $user->password = User::EncryptPassword($user->password);
		return $user;
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
		// Return Guest group
		return new Group();
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
		$enabled = (bool) Settings::Get('user_avatars');
		if( $enabled && !empty($this->avatar) ){
			// Should get File object's path and build the img tag
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

	public function getTimezone(){
		if( !empty($this->timezone) ){
			return $this->timezone;
		}
		return "";
	}

	public function getInfo($field){
		if( !empty($this->info) ){
			return $this->info;
		}
		return "";
	}

	public function getDisplayName(){
		// print name or nickname if set
		$allows = (bool)Settings::Get('allow_nicknames');
		$nickname = $this->getInfo('nickname');
		if( $allows && !empty($nickname) ){
			return $nickname;
		}
		return $this->getName();
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

	public static function Add($user){
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
			$amount = $query->countRows()->execute();
			Settings::Update("users_amount", $amount);
		}
	}

	public static function Update($user){

	}

	public static function Delete($userID){

	}

	public static function Authorize($userID, $saveCookies = false){ //private
		if( !self::isExists($userID) ) return false; // fail if user doesn't exists
		if( Session::getCurrent()->isAuthorized() ){
			if( Session::getCurrent()->getUID() === intval($userID) ) return false; //fail if user already logged in
			else{
				Session::getCurrent()->Deauthorize(); // user got wrong cache saved
			}
		}
		$hash = Tools::GenerateHash();
		$updateSession = new Query("{sessions}");
		$updated = $saveCookies ? 0 : time();
		$updateSession->insert( array('sid' => $hash, 'uid' => $userID, 'ip' => Session::getCurrent()->getIPAddress(), 'updated' => $updated) )->execute();
		$lastlogin = new Query("{users}");
		$lastlogin->update(array('lastlogin' => time()))->where()->condition("uid", '=', $userID)->execute();
		Session::getCurrent()->Authorize($hash);
		//save cookies if needed
		if( $saveCookies ){
			Session::getCurrent()->setCookie('usid_saved', $hash); //save cookie for year
		}
		return true;
	}

	public static function Deauthorize($userID = 0){
		if( $userID == User::Current()->getID() || $userID === 0 ){
			Session::GetCurrent()->deleteCookie('usid_saved');
			Session::GetCurrent()->destroy();
		}
	}

	public static function ActivateUser($userID){
		//?
	}

	public static function EncryptPassword($password){
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


	public static function IsExists($uid){
		$check = new Query('{users}');
		$user = $check->select('uid')->where()->condition('uid', '=', $uid)->execute();
		return !empty($user);
	}

	public static function Authenticate($login, $password, $saveCookies = false){
		// /[^a-zA-Z0-9-_@]/
		$result = false;
		$password = self::EncryptPassword($password);
		$saveCookies = (bool) $saveCookies;
		$query = new Query("{users}");
		$check = $query->select("uid")->where()->condition("name", "=", $login)->_or()->condition("email", "=", $login)->limit(1)->execute();
		if( !empty($check) ){
			$id = $check[0]['uid'];
			$result = self::Authorize($id, $saveCookies);
		}else{
			// TODO: login attempts
		}

		if( !$result ){
			$error = new Notification(tr("Wrong username or password"), Notification::ERROR);
			$error->add();
		}
		return $result;
	}

	public static function IsAuthenticationRequested(){
		return ( isset($_POST['login-form']) && isset($_POST['login']) && isset($_POST['password']) && isset($_POST['save_cookies']) );
	}

	public static function GetLoginForm(){
		$form = new Form("login-form", Page::FromAction(self::LOGIN_ACTION), tr("Log In"));
		$form->addField("login", "text", tr("Username:"), "", "", tr("username or email"));
		$form->addField("password", "password", tr("Password:"), "", "", tr("password"));
		$form->addFlag("save_cookies", tr("Remember Me"));
		return $form;
	}
}
?>