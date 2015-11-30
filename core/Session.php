<?php
namespace uCMS\Core;

use uCMS\Core\Database\Query;
use uCMS\Core\ORM\Model;
class Session extends Model{
	private static $instance;
	private $sid;
	private $uid;
	private $ip;
	private $updateTime;
	private $isLoaded;
	private $_authorized = false;
	private $_saved = false;
	private static $idleLifetime;

	public static function GetCurrent(){
		if ( is_null( self::$instance ) ){
			self::$instance = new self();
		}
		return self::$instance;
	}

	public static function Start(){
		self::$instance = new self();
	}

	public function init(){
		$this->tableName('sessions');
		$this->primaryKey('sid');
		$this->belongsTo('\\uCMS\\Core\\Extensions\\Users\\User', array('bind' => 'user'));
		foreach ($_COOKIE as $key => $value) {
			if( strpos($key, 'USID') !== false ){
				$this->sid = $value;
				session_id($value);
				$this->_authorized = true;
				$this->_saved = true;
				if( strpos($key, 'tmpUSID') !== false ){
					$this->deleteCookie($key);
					$this->_saved = false;
				}
			}
		}
		if ( !isset($_SESSION) ){
			session_start();
		}

		self::$idleLifetime = 2 * 3600;
		ini_set('session.cookie_lifetime', 0); // set session life until the browser closes
		ini_set('session.cookie_httponly', 1); // set session life until the browser closes
		ini_set("session.gc_maxlifetime", self::$idleLifetime);
		$this->sid = session_id();
		$this->ip = $this->getIPAddress();
		$this->updateTime = time();
		if( $this->_saved ) $this->updateTime = 0;
		$this->isLoaded = false;
	}

	public function isAuthorized(){
		return $this->_authorized;
	}

	public function authorize($hash){
		$this->sid = $hash;
		$this->setCookie("tmpUSID".session_id(), $hash, time() + 5);
		$this->_authorized = true;
	}

	public function deauthorize(){
		session_regenerate_id();
		foreach ($_COOKIE as $key => $value) {
			if( strpos($key, 'USID') !== false ){
				$this->deleteCookie($key);
			}
		}
	}

	public function load(){
		$query = new Query('{sessions}');
		$data = $query->select(array('sessiondata', 'uid', 'sid'))->where()->condition('sid', '=', $this->sid)->execute();
		if( !empty($data) ){
			session_decode($data[0]['sessiondata']);
			$this->uid = intval($data[0]['uid']);
			$this->sid = $data[0]['sid'];
			$this->isLoaded = true;
			$this->_authorized = true;
		}else{
			if( $this->_saved ){
				$this->deauthorize();
			}
		}
	}

	public function save(){
		$query = new Query('{sessions}');
		if( $this->_authorized ){
			$data = @session_encode();
			if($this->isLoaded){
				$query->update(array('sessiondata' => $data, 'ip' => $this->ip, 'updated' => $this->updateTime))
				->where()->condition('sid', '=', $this->sid)->execute();
			}
			if($this->uid > 0){

				$userVisit = new Query("{users}");
				$userVisit->update(array("visited" => time()))->where()->condition('uid', '=', $this->uid)->execute();
			}
		}
		//Delete old sessions
		$old = new Query("DELETE FROM {sessions} WHERE UNIX_TIMESTAMP() - updated > :idletime 
			AND updated <> 0", array(':idletime' => self::$idleLifetime));
		$old->execute();
	}

	public function getIPAddress(){
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

	public function getID(){
		return $this->sid;
	}

	public function getUID(){
		return intval($this->uid);
	}

	public function getHost(){
		return $this->ip;
	}

	public function getUpdateTime(){
		return $this->updateTime;
	}

	public function have($name){
		return isset($_SESSION[$name]);
	}

	public function get($name){
		if( isset($_SESSION[$name]) ){
			return $_SESSION[$name];
		}
		return "";
	}

	public function set($name, $value){
		$_SESSION[$name] = $value;
	}

	public function push($arrayName, $value){
		$_SESSION[$arrayName][] = $value;
	}

	public function pop($arrayName){
		if( isset($_SESSION[$arrayName]) && is_array($_SESSION[$arrayName]) ){
			$value = array_pop($arrayName);
			return $value;
		}
		return "";
	}

	public function delete($name){
		if( isset($_SESSION[$name]) ){
			unset($_SESSION[$name]);
		}
	}

	public function getCookie($name){
		if( isset($_COOKIE[$name]) ){
			return $_COOKIE[$name];
		}
		return "";
	}

	public function setCookie($name, $value, $time = 0, $httpOnly = true, $secure = false){
		if(!$time) $time = time() + 60 * 60 * 24 * 30;

		// If server name is at first domain level, cookie domain should be empty
		$serverName = (mb_strpos($_SERVER['SERVER_NAME'], ".") !== false) ? $_SERVER['SERVER_NAME'] : "";
		return setcookie($name, $value, $time, '/', $serverName, $secure, $httpOnly);
	}

	public function deleteCookie($name){
		if( isset($_COOKIE[$name]) ){
			setcookie($name, "", time() - 60 * 60 * 24 * 30, "/", $_SERVER['SERVER_NAME']);
			unset($_COOKIE[$name]);
		}
	}

	public function destroy(){
		if( session_id() ){
			$query = new Query('{sessions}');
			$query->delete()->where()->condition('sid', '=', $this->sid)->execute();
			session_unset();
			session_destroy();
		}
	}

	public function saveID($hash){
		$this->setCookie("USID".session_id(), $hash);
	}
}
?>