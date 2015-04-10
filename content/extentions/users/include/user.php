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
	protected static $instance;
	
	public static function current($id = 0){
		if ( is_null( self::$instance ) ){
			self::$instance = new self($id);
		}
		return self::$instance;
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
}
?>