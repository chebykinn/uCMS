<?php
namespace uCMS\Core\Extensions\Users;
use uCMS\Core\Session;
use uCMS\Core\Settings;
use uCMS\Core\Database\Query;
use uCMS\Core\ORM\Model;
use uCMS\Core\Tools;
use uCMS\Core\Form;
use uCMS\Core\Page;
use uCMS\Core\Notification;
class User extends Model{
	const AVATARS_PATH = 'content/uploads/avatars';
	const LOGIN_ACTION = 'login';
	const LOGOUT_ACTION = 'logout';
	const LIST_ACTION = 'users';
	const PROFILE_ACTION = 'user';
	protected $info;
	protected static $currentUser;

	public function init(){
		$this->primaryKey('uid');
		$this->tableName('users');
		$this->belongsTo('\\uCMS\\Core\\Extensions\\Users\\Group', array('bind' => 'group'));
		$this->hasMany('\\uCMS\\Core\\Extensions\\Entries\\Entry', array('bind' => 'entries'));
		$this->hasMany('\\uCMS\\Core\\Extensions\\Users\\UserInfo', array('bind' => 'info'));
		$this->hasMany('\\uCMS\\Core\\Extensions\\FileManager\\File', array('bind' => 'files', 'key' => 'uid'));
		$this->hasMany('\\uCMS\\Core\\Extensions\\Comments\\Comment', array('bind' => 'comments', 'key' => 'uid'));
		$this->hasMany('\\uCMS\\Core\\Session', array('bind' => 'sessions', 'key' => 'uid'));
		$this->hasMany('\\uCMS\\Core\\Extensions\\Menus\\MenuLink', array('bind' => 'links'));
	}
	
	public static function Current(){
		if ( is_null( self::$currentUser ) ){
			self::CheckAuthorization();
		}
		return self::$currentUser;
	}

	public function getDisplayName($row){
		// print name or nickname if set
		$allows = (bool)Settings::Get('allow_nicknames');
		$nickname = $this->getInfo($row, 'nickname');
		if( $allows && !empty($nickname) ){
			return $nickname;
		}
		return $row->name;
	}

	public function getInfo($row, $name){
		if( isset($row->info[$name]) ){
			return $row->info[$name];
		}
		return "";
	}

	public function isLoggedIn($row){
		$sid = Session::GetCurrent()->getUID();
		return ( !empty($row->uid) && !empty($row->name) && !empty($sid) );
	}

	public function can($row, $permission){
		if( is_object($row->group) ){
			return $row->group->hasPermission($permission);
		}
		return false;
	}

	public static function Authorize($userID, $saveCookies = false){ //private
		if( !self::isExists($userID) ) return false; // fail if user doesn't exists
		if( Session::GetCurrent()->isAuthorized() ){
			if( Session::GetCurrent()->getUID() === intval($userID) ) return false; //fail if user already logged in
			else{
				Session::GetCurrent()->Deauthorize(); // user got wrong cache saved
			}
		}
		$hash = Tools::GenerateHash();
		$updateSession = new Query("{sessions}");
		$updated = $saveCookies ? 0 : time();
		$updateSession->insert(
			['sid', 'uid', 'ip', 'updated', 'created'],
			[[$hash, $userID, Session::getCurrent()->getIPAddress(), $updated, time()]]
		)->execute();
		$lastlogin = new Query("{users}");
		$lastlogin->update(['lastlogin' => time()])->where()->condition("uid", '=', $userID)->execute();
		//save cookies if needed
		if( $saveCookies ){
			Session::GetCurrent()->saveID($hash); //save cookie for year
		}
		Session::GetCurrent()->authorize($hash);
		return true;
	}

	public static function Deauthorize($userID = 0){
		if( $userID == User::Current()->uid || $userID === 0 ){
			Session::GetCurrent()->deauthorize();
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

	public static function CheckAuthorization(){
		$uid = Session::GetCurrent()->getUID();
		$hash = Session::GetCurrent()->getID();
		if( $uid > 0 ){
			self::$currentUser = (new User)->find($uid); //set current user to $uid
			if( is_null(self::$currentUser) || self::$currentUser->uid == 0 ){
				Session::GetCurrent()->deauthorize();
			}
		}

		if( is_null(self::$currentUser) ){
			self::$currentUser = (new User())->clean();
			self::$currentUser->uid = 0;
			self::$currentUser->gid = Group::GUEST;
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

	public function getDate($row){	
		return Tools::FormatTime($row->created);
	}
}
?>