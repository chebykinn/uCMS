<?php
namespace uCMS\Core;

use uCMS\Core\Database\Query;
use uCMS\Core\ORM\Model;
class Session extends Model{
	private static $instance;
	private $sid;
	private $uid;
	private static $currentIP;
	private $updateTime;
	private $isLoaded;
	private $_authorized = false;
	private static $hasCookies = false;
	private static $idleLifetime = 2 * 3600;
	private static $currentID = '';
	private static $newSession = false;

	public function init(){
		$this->tableName('sessions');
		$this->primaryKey('sid');
		$this->belongsTo('\\uCMS\\Core\\Extensions\\Users\\User', array('bind' => 'user'));	
	}

	public static function GetCurrent(){
		if ( !is_null( self::$instance ) ){
			return self::$instance;
		}
		return NULL;
	}

	public static function GetIdFromCookie(){
		foreach ($_COOKIE as $key => $value) {
			if( strpos($key, 'USID') !== false ){
				return $value;
			}
		}
	}

	public static function PrepareSession(){
		ini_set('session.cookie_lifetime', 0); // Set session life until the browser closes
		ini_set('session.cookie_httponly', 1); // Ensure that session is http only
		ini_set("session.gc_maxlifetime", self::$idleLifetime);
		if( !empty(self::$currentID) ){
			session_id(self::$currentID);
		}
		if ( !isset($_SESSION) ){
			session_start();
		}
		self::$currentID = session_id();
		self::$currentIP = self::GetIPAddress();
	}

	public static function Start(){
		$id = self::GetIdFromCookie();
		if( !empty($id) ){
			self::$currentID = $id;
			self::$hasCookies = true;
		}
		self::PrepareSession();
	}

	public static function Load(){
		self::$instance = (new self())->find(self::$currentID);
		if( self::$instance == NULL ){
			if( self::$hasCookies ) self::Deauthorize();
			self::$newSession = true;
		}else{

		}
	}

	public static function Save(){
		if( self::$instance != NULL ){
			if( self::$instance->updated > 0 ){
				self::$instance->updated = time();
			}
			$data = @session_encode();
			self::$instance->sessiondata = $data;
			self::$instance->update();
			$clear = new Query("{sessions}");
			//$clear->delete()->condition($clear->datediff(time(),'updated'), '>', self::$idleTime)->execute();
			// TODO: Clear old sessions
			// TODO: Update user visit time
		}
	}

	public static function GetIPAddress(){
		if( !empty(self::$currentIP) ) return self::$currentIP;
		if( isset($_SERVER['HTTP_X_CLIENT_IP']) ){
			$ip = $_SERVER['HTTP_X_CLIENT_IP'];
		}
		else if( isset($_SERVER['HTTP_X_FORWARDED_FOR']) ){
			$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
		}
		if (!empty($ip)) {
			$ip = explode(",", $ip);
			$ip = $ip[0];
		}
		if (empty($ip)) $ip = @$_SERVER['REMOTE_ADDR'];
		return $ip;
	}

	public static function Have($name){
		return isset($_SESSION[$name]);
	}

	public static function Get($name){
		if( isset($_SESSION[$name]) ){
			return $_SESSION[$name];
		}
		return "";
	}

	public static function Set($name, $value){
		$_SESSION[$name] = $value;
	}

	public static function Push($arrayName, $value){
		$_SESSION[$arrayName][] = $value;
	}

	public static function Pop($arrayName){
		if( isset($_SESSION[$arrayName]) && is_array($_SESSION[$arrayName]) ){
			$value = array_pop($arrayName);
			return $value;
		}
		return "";
	}

	public static function DeleteKey($name){
		if( isset($_SESSION[$name]) ){
			unset($_SESSION[$name]);
		}
	}

	public static function GetCookie($name){
		if( isset($_COOKIE[$name]) ){
			return $_COOKIE[$name];
		}
		return "";
	}

	public static function SetCookie($name, $value, $time = 0, $httpOnly = true, $secure = false){
		if(!$time) $time = time() + 60 * 60 * 24 * 30;

		// If server name is at first domain level, cookie domain should be empty
		$serverName = (mb_strpos($_SERVER['SERVER_NAME'], ".") !== false) ? $_SERVER['SERVER_NAME'] : "";
		return setcookie($name, $value, $time, '/', $serverName, $secure, $httpOnly);
	}

	public static function DeleteCookie($name){
		if( isset($_COOKIE[$name]) ){
			self::SetCookie($name, "", time() - 60 * 60 * 24 * 30);
			unset($_COOKIE[$name]);
		}
	}

	public static function Destroy(){
		if( session_id() ){
			self::$instance->delete();
			session_unset();
			session_destroy();
		}
	}

	public static function SaveID($hash){
		self::SetCookie("USID".session_id(), $hash);
	}

	public static function IsAuthorized(){
		return (self::$instance != NULL);
	}

	public static function Authorize($hash){
		self::SetCookie("tmpUSID".session_id(), $hash, time() + 5);
	}

	public static function Deauthorize(){
		session_regenerate_id();
		self::$currentID = session_id();
		foreach ($_COOKIE as $key => $value) {
			if( strpos($key, 'USID') !== false ){
				self::DeleteCookie($key);
			}
		}
	}
}
?>