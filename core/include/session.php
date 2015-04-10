<?php
class Session{
	private static $instance;
	private $sid;
	private $uid;
	private $ip;
	private $updateTime;
	private $loaded;

	public static function getCurrent(){
		if ( is_null( self::$instance ) ){
			self::$instance = new self();
		}
		return self::$instance;
	}

	public static function start(){
		self::$instance = new self();
	}

	public function __construct(){
		if (!isset($_SESSION)){
			session_start();
		}
		ini_set('session.cookie_lifetime', 0); // set session life until the browser closes
		ini_set("session.gc_maxlifetime", SESSION_IDLE_LIFETIME);
		$this->sid = !empty($_SESSION['hash']) ? $_SESSION['hash'] : session_id();
		$this->ip = $this->getIPAddress();
		$this->updateTime = time();
		$this->loaded = false;
	}

	public function load(){
		$query = new Query('{sessions}');
		$data = $query->select(array('sessiondata', 'uid', 'sid'))->where()->condition('sid', '=', $this->sid)->execute();
		if( !empty($data) ){
			session_decode($data[0]['sessiondata']);
			$this->uid = $data[0]['uid'];
			$this->sid = $data[0]['sid'];
			$this->loaded = true;
		}

	}

	public function save(){
		$data = session_encode();
		$query = new Query('{sessions}');
		if($this->loaded){
			$query->update(array('sessiondata' => $data, 'ip' => $this->ip, 'updated' => $this->updateTime))
			->where()->condition('sid', '=', $this->sid)->execute();
		}else{
			$query->insert(array('sid' => $this->sid, 'sessiondata' => $data, 'ip' => $this->ip, 'updated' => $this->updateTime))->execute();
		}

		//Delete old sessions
		$query->delete()->where()->condition('UNIX_TIMESTAMP() - `updated`', '>', SESSION_IDLE_LIFETIME, true)->execute();
	}

	public function getIPAddress(){
		if( isset($_SERVER['HTTP_X_CLIENT_IP']) ){
			$ip = $_SERVER['HTTP_X_CLIENT_IP'];
		}
		if (empty($ip)) $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
		if (!empty($ip)) {
			$ip = explode(",", $ip);
			$ip = $ip[0];
		}
		if (empty($ip)) $ip = $_SERVER['REMOTE_ADDR'];
		return $ip;
	}

	public function getID(){
		return $this->sid;
	}

	public function getUID(){
		return intval($this->uid);
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

	public function setCookie($name, $value, $time){
		return setcookie($name, $value, $time, '/');
	}

	public function deleteCookie($name){
		if( isset($_COOKIE[$name]) ){
			setcookie($name, "", time() - 60 * 60 * 24 * 30, "/");
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
}
?>