<?php
/**
 *
 * uCMS Users
 * @package uCMS Users
 * @since uCMS 1.0
 * @version uCMS 1.3
 *
*/
class uSers{
	var $id;
	var $login;
	protected $password;
	var $group;
	var $avatar;
	var $email;
	var $activation;
	var $date;
	protected $session_hash;
	protected $regip;
	protected $logip;
	var $online;
	var $lastlogin;

	function __construct(){
		$this->id 			= 0;
		$this->login 		= "";
		$this->password 	= "";
		$this->group 		= 0;
		$this->avatar 		= "";
		$this->email 		= "";
		$this->activation 	= "";
		$this->date 		= "";
		$this->session_hash = "";
		$this->regip 		= "";
		$this->logip 		= "";
		$this->online 		= "";
		$this->lastlogin 	= "";
		$this->nickname 	= "";
	}

	/**
	 *
	 * Print reset form
	 * @package uCMS Users
	 * @since uCMS 1.0
	 * @version uCMS 1.3
	 * @return nothing
	 *
	*/
	function reset_form(){
		global $ucms;
		$ucms->template(get_module('path', 'users')."forms/reset_form.php");
	}

	/**
	 *
	 * Get user id of current user or user by given login
	 * @package uCMS Users
	 * @since uCMS 1.0
	 * @version uCMS 1.3
	 * @return string
	 *
	*/
	function get_user_id($login = ''){
		global $udb;
		if($login == ''){
			if($this->id > 0){
				return $this->id;
			}else return false;
		}else{
			global $udb;
			$login = $udb->parse_value($login);
			$id = $udb->get_row("SELECT `id` FROM `".UC_PREFIX."users` WHERE `login` = '$login' LIMIT 1");
			$id = $id[0];
			if($id != '') return $id;
			else return false;
		}
	}

	/**
	 *
	 * Get user login of current user or user by given id
	 * @package uCMS Users
	 * @since uCMS 1.0
	 * @version uCMS 1.3
	 * @return string
	 *
	*/
	function get_user_login($id = ''){
		global $udb;
		if($id == ''){
			if($this->login != ""){
				return $this->login;
			}elseif(isset($_SESSION['guest_login']))
				return $_SESSION['guest_login']; 
			else return false;
		}else{
			$id = (int) $id;
			$user = $udb->get_row("SELECT `login` FROM `".UC_PREFIX."users` WHERE `id` = '$id' LIMIT 1");
			$login = $user['login'];
			if(!$login) return false;
			else return $login;
		}
	}

	/**
	 *
	 * Get current user password
	 * @package uCMS Users
	 * @since uCMS 1.0
	 * @version uCMS 1.3
	 * @return string
	 *
	*/
	function get_user_password(){
		global $udb;
		if($this->password != ""){
			return $this->password;
		}else return false;
	}

	/**
	 *
	 * Get user group of current user or user by given id or login
	 * @package uCMS Users
	 * @since uCMS 1.0
	 * @version uCMS 1.3
	 * @return string
	 *
	*/
	function get_user_group($id = '', $login = ''){
		global $udb;
		if($id == '' and $login == ''){
			if($this->group > 0){
				return $this->group;
			}else{ 
				if(!$this->logged())
					return GUEST_GROUP_ID;
				return false;
			}
		}else{
			$login = $udb->parse_value($login);
			$id = (int) $id;
			$user = $udb->get_row("SELECT `group` FROM `".UC_PREFIX."users` WHERE `login` = '$login' or `id` = '$id' LIMIT 1");
			$group = $user['group'];
			if(!$group) return false;
			else return $group;
		}
	}

	/**
	 *
	 * Get user avatar of current user or user by given id or login
	 * @package uCMS Users
	 * @since uCMS 1.0
	 * @version uCMS 1.3
	 * @return string
	 *
	*/
	function get_user_avatar($id = '', $login = ''){
		global $udb;
		if($id != '' or $login != ''){
			$login = $udb->parse_value($login);
			$id = (int) $id;
			$user = $udb->get_row("SELECT `avatar` FROM `".UC_PREFIX."users` WHERE `login` = '$login' or `id` = '$id' LIMIT 1");
			$avatar = $user['avatar'];
			if(!$avatar) return false;
			else return $avatar;
			
		}else{
			if($this->logged() and $this->avatar != ""){
				return $this->avatar;
			}else return false;
		}
		
	}

	/**
	 *
	 * Get user email of current user or user by given id or login
	 * @package uCMS Users
	 * @since uCMS 1.0
	 * @version uCMS 1.3
	 * @return string
	 *
	*/
	function get_user_email($id = '', $login = ''){
		global $udb;
		if($id != '' or $login != ''){
			$login = $udb->parse_value($login);
			$id = (int) $id;
			$user = $udb->get_row("SELECT `email` FROM `".UC_PREFIX."users` WHERE `login` = '$login' or `id` = '$id' LIMIT 1");
			$email = $user['email'];
			if(!$email) return false;
			else return $email;
		}else{
			if($this->logged() and $this->email != ""){
				return $this->email;
			}else return false;
		}
	}

	/**
	 *
	 * Get activation result and print the alert
	 * @package uCMS Users
	 * @since uCMS 1.0
	 * @version uCMS 1.3
	 * @return nothing
	 *
	*/
	function get_activation_result(){
		global $event, $ucms;
		if(isset($_SESSION['activate'])){
			if($_SESSION['activate']){
				$event->do_actions("user.activated");
				echo '<div class="success">'.$ucms->cout("module.users.alert.success.user_activated", true, SITE_NAME).'</div>';
			}
			else{
				echo '<div class="error">'.$ucms->cout("module.users.alert.error.user_activate", true).'</div>';
			}
			unset($_SESSION['activate']);
		}
	}

	/**
	 *
	 * Get group name of current user or user by given id or login
	 * @package uCMS Users
	 * @since uCMS 1.0
	 * @version uCMS 1.3
	 * @return string
	 *
	*/
	function get_user_group_name($id = '', $login = ''){
		global $udb;
		if($id != '' or $login != ''){
			$id = (int) $id;
			$login = $udb->parse_value($login);
			if($id != '')
				$group_id = $this->get_user_group($id);
			if($login != '')
				$group_id = $this->get_user_group($login);
			$user = $udb->get_row("SELECT `name` FROM `".UC_PREFIX."groups` WHERE `id` = '$group_id' LIMIT 1");
			$name = $user['name'];
			if(!$name) return false;
			else return $name;
		}else{
			$group_id = $this->get_user_group();
			$user = $udb->get_row("SELECT `name` FROM `".UC_PREFIX."groups` WHERE `id` = '$group_id' LIMIT 1");
			$name = $user['name'];
			if(!$name) return false;
			else return $name;
		}
	}

	/**
	 *
	 * Get group name by given $group_id
	 * @package uCMS Users
	 * @since uCMS 1.3
	 * @version uCMS 1.3
	 * @return string
	 *
	*/
	function get_group_name($group_id){
		global $udb;
		if($group_id != ''){
			$group_id = (int) $group_id;
			$user = $udb->get_row("SELECT `name` FROM `".UC_PREFIX."groups` WHERE `id` = '$group_id' LIMIT 1");
			$name = $user['name'];
			if(!$name) return false;
			else return $name;
		}
	}

	/**
	 *
	 * Get group alias by given $group_id
	 * @package uCMS Users
	 * @since uCMS 1.3
	 * @version uCMS 1.3
	 * @return string
	 *
	*/
	function get_group_alias($group_id){
		global $udb;
		if($group_id != ''){
			$group_id = (int) $group_id;
			$user = $udb->get_row("SELECT `alias` FROM `".UC_PREFIX."groups` WHERE `id` = '$group_id' LIMIT 1");
			$alias = $user['alias'];
			if(!$alias) return false;
			else return $alias;
		}
	}

	/**
	 *
	 * Get current user ip
	 * @package uCMS Users
	 * @since uCMS 1.1
	 * @version uCMS 1.3
	 * @return string
	 *
	*/
	function get_user_ip(){

		// $ip = $_SERVER["HTTP_X_CLIENT_IP"];
		// if(empty($ip)){
		// 	$ip = $_SERVER["REMOTE_ADDR"]; 
		// }
		$ip = @$_SERVER['HTTP_X_CLIENT_IP'];
		if (empty($ip)) $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
		if (!empty($ip)) {
		    $ip = explode(",", $ip);
		    $ip = $ip[0];
		}
		if (empty($ip)) $ip = $_SERVER['REMOTE_ADDR'];
		return $ip;
	}

	/**
	 *
	 * Generate session hash
	 * @package uCMS Users
	 * @since uCMS 1.2
	 * @version uCMS 1.3
	 * @return string
	 *
	*/
	function session_hash($length = 6){
		$chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHI JKLMNOPRQSTUVWXYZ0123456789";
		$code = "";
		$clen = strlen($chars) - 1;  
		while (strlen($code) < $length) {
			$code .= $chars[mt_rand(0,$clen)];  
		}
		return $code;
	}

	/**
	 *
	 * Prepare guest user
	 * @package uCMS Users
	 * @since uCMS 1.2
	 * @version uCMS 1.3
	 * @return string
	 *
	*/
	function guest(){
		global $udb, $ucms;
		if(!$this->logged()){	
			if(!isset($_SESSION['guest_login'])){
				$_SESSION['guest_login'] = $ucms->cout("module.users.guest_login", true);
			}
		}else return false;
		$guests_count = $this->get_site_visitors_count()-$this->get_users_online_count();
		$ucms->update_setting('guests_count', $guests_count);
	}

	/**
	 *
	 * Get number of guests on the site
	 * @package uCMS Users
	 * @since uCMS 1.2
	 * @version uCMS 1.3
	 * @return int
	 *
	*/
	function get_guests_count(){
		if(VISITORS_COUNT == 0 or USERS_ONLINE_COUNT > VISITORS_COUNT) return 0;
		return VISITORS_COUNT-USERS_ONLINE_COUNT;
	}

	/**
	 *
	 * Get number of registered users on the site
	 * @package uCMS Users
	 * @since uCMS 1.2
	 * @version uCMS 1.3
	 * @return int
	 *
	*/
	function get_users_online_count(){
		return USERS_ONLINE_COUNT;
	}

	/**
	 *
	 * Get number of all users on the site
	 * @package uCMS Users
	 * @since uCMS 1.2
	 * @version uCMS 1.3
	 * @return int
	 *
	*/
	function get_site_visitors_count(){
		return VISITORS_COUNT;
	}

	/**
	 *
	 * Calculate number of all users on the site
	 * @package uCMS Users
	 * @since uCMS 1.2
	 * @version uCMS 1.3
	 * @return int
	 *
	*/
	function get_site_visitors(){
		if ( $directory_handle = opendir( session_save_path() ) ) {
			$count = 0;
			while ( false !== ( $file = readdir( $directory_handle ) ) ) {
				if($file != '.' && $file != '..'){
					if(time() - filemtime(session_save_path()."/$file") < MAX_IDLE_TIME * 60) {
						$count++;
					}
				} 
			}
			closedir($directory_handle);
			return $count;
		} else {
			return false;
		}
	}

	/**
	 *
	 * Check if current user is logged in
	 * @package uCMS Users
	 * @since uCMS 1.0
	 * @version uCMS 1.3
	 * @return bool
	 *
	*/
	function logged(){
		if(!$this->get_user_login() or !$this->get_user_password()){
			if(!$this->get_user_id()){
				return false;
			}else{
				$this->id = 0;
				return false;
			}
		}else return true;
	}

	/**
	 *
	 * Logout current user
	 * @package uCMS Users
	 * @since uCMS 1.1
	 * @version uCMS 1.3
	 * @return nothing
	 *
	*/
	function logout(){
		global $udb, $event;
		session_destroy();
		$user_id = $this->get_user_id();
		$udb->query("UPDATE `".UC_PREFIX."users` SET `online` = '0' WHERE `id` = '$user_id'");
		unset($_SESSION['avatar']);
		unset($_SESSION['email']);
		unset($_SESSION['group']);
		unset($_SESSION['password']);
		unset($_SESSION['login']); 
		unset($_SESSION['id']);
		setcookie("id", "", time() - 3600*24*30*12, '/', $_SERVER['SERVER_NAME']);
		setcookie("hash", "", time() - 3600*24*30*12, '/', $_SERVER['SERVER_NAME']);
		$event->do_actions("user.logged_out");
	}

	/**
	 *
	 * Check if current user is administrator
	 * @package uCMS Users
	 * @since uCMS 1.1
	 * @version uCMS 1.3
	 * @return bool
	 *
	*/
	function admin(){
		if($this->logged() and $this->get_user_group() == ADMINISTRATOR_GROUP_ID) return true;
		else return false;
	}

	/**
	 *
	 * Get if current user or user by given id or login is administrator
	 * @package uCMS Users
	 * @since uCMS 1.2
	 * @version uCMS 1.3
	 * @return bool
	 *
	*/
	function is_admin($id = '', $login = ''){
		global $udb;
		if($id != '' or $login != ''){
			$login = $udb->parse_value($login);
			$id = (int) $id;
			$group = $this->get_user_group($id, $login);
			if($group != 1) return false;
			else return true;
		}else{
			$id = $this->get_user_id();
			$group = $this->get_user_group();
			if($group != 1) return false;
			else return true;
		}
	}

	/**
	 *
	 * Login user on the site by his session or by saved cookies
	 * @package uCMS Users
	 * @since uCMS 1.0
	 * @version uCMS 1.3
	 * @return bool
	 *
	*/
	function autologin(){
		global $udb;
		$count = $udb->num_rows("SELECT * FROM `".UC_PREFIX."users` LIMIT 1");
		if(!$count){
			header("Location: ".UCMS_DIR."/sys/install/index.php");
			exit;
		}
		if(!$this->logged()){
			$id = 0;
			$hash = 0;
			if(isset($_SESSION['id'])) {
				$id = $_SESSION['id'];
			}
			if(isset($_SESSION['hash'])) {
				$hash = $_SESSION['hash'];
			}
			if(isset($_COOKIE['id']) && $id == 0) $id = $_COOKIE['id'];
			if(isset($_COOKIE['hash']) && $hash == 0) $hash = $_COOKIE['hash'];
			if($id > 0 and $hash != ""){
				
				$userdata = $udb->get_row("SELECT `u`.*, `uf`.`value` AS `nickname` FROM `".UC_PREFIX."users` AS `u` FORCE INDEX (PRIMARY)
					LEFT JOIN `".UC_PREFIX."usersinfo` AS `uf` ON `uf`.`user_id` = `u`.`id` AND `uf`.`name` = 'nickname'
					WHERE `u`.`id` = '".intval($id)."' LIMIT 1");
				if($userdata and $userdata['session_hash'] === $hash and (int) $userdata['id'] === (int) $id){
					$this->id = $id;
					$this->login = $userdata['login'];
					$this->password = $userdata['password'];
					$this->group = $userdata['group'];
					$this->avatar = $userdata['avatar'];
					$this->email = $userdata['email'];
					$this->activation = $userdata['activation'];
					$this->date = $userdata['date'];
					$this->session_hash = $userdata['session_hash'];
					$this->regip = $userdata['regip'];
					$this->logip = $userdata['logip'];
					$this->online = $userdata['online'];
					$this->lastlogin = $userdata['lastlogin'];
					$this->nickname = $userdata['nickname'];
					$udb->query("UPDATE `".UC_PREFIX."users` SET `online` = '1', `lastlogin` = NOW() WHERE `id` = '$id'");
					return true;
				}else{
					setcookie("id", "", time() - 3600*24*30*12, "/");
					setcookie("hash", "", time() - 3600*24*30*12, "/");
					return false;
				}
			}else return false;
		}
	}

	/**
	 *
	 * Encrypt given $password with uCMS algorithm
	 * @package uCMS Users
	 * @since uCMS 1.0
	 * @version uCMS 1.3
	 * @return string
	 *
	*/
	function crypt_password($password){
		global $udb;
		$salt = substr(sha1($password),0,22);
		$password = $udb->parse_value(stripslashes($password));
		$password = htmlspecialchars(trim($password));
		$password = crypt($password, '$2a$10$'.$salt);
		return $password;
	}

	/**
	 *
	 * Handle resetting user passwords
	 * @package uCMS Users
	 * @since uCMS 1.0
	 * @version uCMS 1.3
	 * @return string
	 *
	*/
	function reset_password(){
		global $udb, $url_all, $ucms;
		$domain = preg_replace("#(http://)#", '', SITE_DOMAIN);
		if(isset($_SESSION['reset-stage1']) and $_SESSION['reset-stage1']){
			echo '<br><div class="success">'.$ucms->cout("module.users.alert.success.reset.stage1", true).'</div><br>';
			unset($_SESSION['reset-stage1']);
		}
		if(isset($_SESSION['reset-stage2']) and $_SESSION['reset-stage2']){
			echo '<br><div class="success">'.$ucms->cout("module.users.alert.success.reset.stage2", true).'</div><br>';
			unset($_SESSION['reset-stage2']);
		}
		if(isset($_POST['email'])){
			$email = $udb->parse_value($_POST['email']); 
			if ($email == '') unset($email); 
		}
		if(!UNIQUE_EMAILS and isset($_POST['login'])){
			$login = $udb->parse_value($_POST['login']); 
			if ($login == '') unset($login); 
		}
		if(NICE_LINKS){
			if(UNIQUE_EMAILS){
				$secr = isset($url_all[3]) ? $url_all[3] : '';
				$mail = isset($url_all[2]) ? $url_all[2] : '';
				$login2 = '';
			}else{
				$secr = isset($url_all[4]) ? $url_all[4] : '';
				$mail = isset($url_all[3]) ? $url_all[3] : '';
				$login2 = isset($url_all[2]) ? $url_all[2] : '';
			}
		}else{
			$secr = isset($_GET['code']) ? $_GET['code'] : '';
			$mail = isset($_GET['email']) ? $_GET['email'] : '';
			$login2 = isset($_GET['login']) ? $_GET['login'] : '';
		}
		if($secr == '' and $mail == '' and $login2 == ''){
			if(isset($email)){
				if(UNIQUE_EMAILS)
					$result = $udb->get_row("SELECT `id` FROM `".UC_PREFIX."users` WHERE `email` = '$email' AND `activation` = '1' LIMIT 1");
				else
					$result = $udb->get_row("SELECT `id` FROM `".UC_PREFIX."users` WHERE `email` = '$email' AND `login` = '$login' AND `activation` = '1' LIMIT 1");
				if(empty($result['id']) or $result['id'] == ''){
					echo "<div class=\"error\">".$ucms->cout("module.users.alert.error.reset.no_such_user", true)."</div>";
				}else{
					if(!isset($login) or $login == '') $login = $this->get_user_login($result['id']); 
					$time = date('H'); 
					$t = strptime($time, '%H'); 
					$duration = mktime($t['tm_hour']+4); 
					$duration = date('H', $duration + 4); 
					$secret = md5(strrev($result['id'].$email.$login.$duration));
					if(UNIQUE_EMAILS){
						$link = NICE_LINKS ? "<a href=\"".UCMS_URL."reset/$email/$secret\">".$ucms->cout("module.users.reset.email.link.label", true)."</a>"
						 : "<a href=\"".UCMS_URL."?action=reset&amp;email=$email&amp;code=$secret\">".$ucms->cout("module.users.reset.email.link.label", true)."</a>";
					}else{
						$link = NICE_LINKS ? "<a href=\"".UCMS_URL."reset/$login/$email/$secret\">".$ucms->cout("module.users.reset.email.link.label", true)."</a>"
						 : "<a href=\"".UCMS_URL."?action=reset&amp;login=$login&amp;email=$email&amp;code=$secret\">".$ucms->cout("module.users.reset.email.link.label", true)."</a>";
					}         	
					
					$headers = "Content-type:text/html; charset=utf-8\r\n";
					$subject = $ucms->cout("module.users.reset.email.stage1.subject", true);
					$headers .= 'From: '.'=?UTF-8?B?'.base64_encode(SITE_NAME).'?='.' <uCMS@'.$domain.'>'."\r\n";
					$message = $ucms->cout("module.users.reset.email.stage1.message", true, $login, $link);
					$headers .= "Content-type:text/html; charset=utf-8\r\n";
					mail($email, '=?UTF-8?B?'.base64_encode($subject).'?=', $message, $headers);
					$back = NICE_LINKS ? UCMS_DIR."/reset" : UCMS_DIR."/?action=reset"; 
					header("Location: $back");
					$_SESSION['reset-stage1'] = true;
					return true;
				}
			}
		}else{
			if(UNIQUE_EMAILS)
				$result = $udb->get_row("SELECT `id` FROM `".UC_PREFIX."users` WHERE `email` = '$mail' AND `activation` = '1' LIMIT 1");
			else
				$result = $udb->get_row("SELECT `id` FROM `".UC_PREFIX."users` WHERE `email` = '$mail' AND `login` = '$login2' AND `activation` = '1' LIMIT 1");
			if(!isset($login2) or $login2 == '') $login = $this->get_user_login($result['id']); else $login = $login2;
				if(empty($result['id']) or $result['id'] == ''){
					echo "<div class=\"error\">".$ucms->cout("module.users.alert.error.reset.no_such_user", true)."</div>";
				}else{
					$time = date('H'); 
					$t = strptime($time, '%H'); 
					$duration = mktime($t['tm_hour'] + 4); 
					$duration = date('H', $duration + 4);
					$secret = md5(strrev($result['id'].$mail.$login.$duration));
					if($secret == $secr){	
						$datenow = date('YmdHis');
						$new_password = md5($datenow);
						$new_password = substr($new_password, 2, 8);
						$new_password_sh = $this->crypt_password($new_password);
						$udb->query("UPDATE `".UC_PREFIX."users` SET `password` = '$new_password_sh' WHERE `login` = '$login' ");

						$headers = "Content-type:text/html; charset=utf-8\r\n";
						$subject = $ucms->cout("module.users.reset.email.stage2.subject", true);
						$headers .= 'From: '.'=?UTF-8?B?'.base64_encode(SITE_NAME).'?='.' <uCMS@'.$domain.'>'."\r\n";
						$message = $ucms->cout("module.users.reset.email.stage2.message", true, $login, SITE_NAME, $new_password);
						$headers .= "Content-type:text/html; charset=utf-8\r\n";
						mail($mail, '=?UTF-8?B?'.base64_encode($subject).'?=', $message, $headers);
						if(ADMIN_EMAIL != ''){
							$user_link = NICE_LINKS ? "<a href=\"".UCMS_URL."user/$login\">$login</a>" : "<a href=\"".UCMS_URL."?action=profle&amp;id=$result[id]\">$login</a>";
							$headers2 = "Content-type:text/html; charset=utf-8\r\n";
							$subject2 = $ucms->cout("module.users.reset.email.admin.subject", true);
							$headers2 .= 'From: '.'=?UTF-8?B?'.base64_encode(SITE_NAME).'?='.' <uCMS@'.$domain.'>'."\r\n";
							$message2 = $ucms->cout("module.users.reset.email.admin.message", true, $user_link);
							$headers2 .= "Content-type:text/html; charset=utf-8\r\n";
							mail(ADMIN_EMAIL, '=?UTF-8?B?'.base64_encode($subject2).'?=', $message2, $headers2);
						}
						$back = NICE_LINKS ? UCMS_DIR."/reset" : UCMS_DIR."/?action=reset"; 
						header("Location: $back");
						$_SESSION['reset-stage2'] = true;
						return true;
					}else{
						echo "<div class=\"error\">".$ucms->cout("module.users.alert.error.reset.wrong_code", true)."</div>";
					}
				}
		}
	}

	/**
	 *
	 * Get user profile data from $user_id
	 * @package uCMS Users
	 * @since uCMS 1.0
	 * @version uCMS 1.3
	 * @return array
	 *
	*/
	function get_profile(){
		global $user_id, $udb;
		if ($this->has_access("users", 1)){
			$result = $udb->get_row("SELECT `email`, `login`, `group`, `avatar`, `activation` FROM `".UC_PREFIX."users` WHERE `id` = '$user_id' LIMIT 1"); 
			if ( !$result or ($result['activation'] < 1 and !$this->has_access("users", 4)) ) {   
				return false;
			}else{
				$user_info = $udb->get_rows("SELECT * FROM `".UC_PREFIX."usersinfo` WHERE `user_id` = '$user_id' ORDER BY `user_id` ASC"); 
				$user_login = $result['login'];
				$user_email = $result['email'];
				$user_group_id = $result['group'];
				$groupresult = $udb->get_row("SELECT `name` FROM `".UC_PREFIX."groups` WHERE `id` = '$user_group_id' LIMIT 1"); 
				$user_group = $groupresult['name'];
				if(USER_AVATARS) 
					$user_avatar = $result['avatar'];
				else $user_avatar = '';
				$profile = array(
					0 => $user_login, 
					1 => $user_email,
					2 => $user_group_id,
					3 => $user_group, 
					4 => $user_avatar,	
					5 => $user_info);
				return $profile;
			}
		}	
	}

	/**
	 *
	 * Check if current profile page belongs to current user
	 * @package uCMS Users
	 * @since uCMS 1.0
	 * @version uCMS 1.3
	 * @return bool
	 *
	*/
	function is_profile(){
		global $profile_login;
		$login = $this->get_user_login();
		if($profile_login == $login){
			return true;
		}else return false;
	}

	/**
	 *
	 * Print profile menu for widget
	 * @package uCMS Users
	 * @since uCMS 1.0
	 * @version uCMS 1.3
	 * @return nothing
	 *
	*/
	function profile_menu(){
		global $url_all, $login, $ucms;
		if(!$this->logged()):
			$login->login_form();
		else: 
			if(isset($_SESSION['activate']) and $_SESSION['activate']){
				echo '<div class="success" style="width: 80%;">'.$ucms->cout("module.users.alert.success.profile.user_activated", true).'</div>';
				unset($_SESSION['activate']);
			}
			$profile_link = $this->get_profile_link();
			$userlist_link = $this->get_userlist_link();			
			$logout_link = $this->get_logout_link();
		?>
			<div class="user-mini"> 
			<b><?php echo $this->get_user_login(); ?></b><br>
			<?php if(USER_AVATARS) echo '<img src="'.UCMS_DIR."/".AVATARS_PATH.$this->get_user_avatar().'" alt="">'; ?>
			<ul class="umenu">
			<li class="group-tag"><?php echo $this->get_user_group_name(); ?></li>
			<li><a href="<?php echo $profile_link; ?>"><?php $ucms->cout("module.users.profile_menu.profile_link.label"); ?></a></li>
			<li><a href="<?php echo $userlist_link; ?>"><?php $ucms->cout("module.users.profile_menu.userlist_link.label"); ?></a></li>
			<li><a href="<?php echo $logout_link; ?>"><?php $ucms->cout("module.users.profile_menu.logout_link.label"); ?></a><br></li>
			<?php if($this->has_access("all", 4)) echo '<li><a href="'.UCMS_DIR.'/admin">'.$ucms->cout("module.users.profile_menu.panel_link.label", true).'</a><br></li>'; ?>
			</ul>
			</div> 
		<?php
		endif; 
	}

	/**
	 *
	 * Print complete list of users
	 * @package uCMS Users
	 * @since uCMS 1.0
	 * @version uCMS 1.3
	 * @return nothing
	 *
	*/
	function list_users($users = ''){
		global $page, $udb, $count, $pages_count, $ucms;
		if($users == ""){
			global $users;
		}
		$module = NICE_LINKS ? UCMS_DIR.'/users' : UCMS_DIR.'/?action=userlist&amp;';
		if($this->has_access("users", 1)){
			$ucms->template(get_module("path", "users")."forms/list_users.php", true, $page, $count, $pages_count, $users);
		}else{
			if($this->logged())
				echo '<div class="error">'.$ucms->cout("module.users.alert.error.list_users.no_access.user", true).'</div>';
			else echo '<div class="error">'.$ucms->cout("module.users.alert.error.list_users.no_access.guest", true).'</div>';
		}
	}

	/**
	 *
	 * Check if current user or user by $user_id has access
	 * for current module by its $moduleID on given $accessLVL
	 * @package uCMS Users
	 * @since uCMS 1.1
	 * @version uCMS 1.3
	 * @return bool
	 *
	*/
	function has_access($moduleID = 0, $accessLVL = 2, $user_id = 0){
		if($accessLVL < 0) $accessLVL = 0;
		if($accessLVL > 7) $accessLVL = 7;
		global $group;

		$permissions = $group->get_user_permissions($user_id);
		$multiple = explode(",", $moduleID);
		if(!empty($multiple[1])){

			$mode = explode(":", $multiple[0]);
			$multiple[0] = !empty($mode[1]) ? $mode[1] : $mode[0];
			$mode = $mode[0];
			if(is_integer($mode)){
				$mode = "o";
			}
			$one = false;
			foreach ($multiple as $module) {
				$res = $this->has_access($module, $accessLVL);
				if($res){
					$one = true;
					if($mode == 'o') break;
				}
				if(!$res and $mode == 'a'){
					return false;
				}
			}
			if($one)
				return true;
		}
		switch ($moduleID) {
			case 'all':
				foreach ($permissions as $permission) {
					if($permission < $accessLVL)
						return false;
				}
				return true;
			break;
	
			case 'all_equal':
				foreach ($permissions as $permission) {
					if($permission != $accessLVL)
						return false;
				}
				return true;
			break;

			case 'system':
				if($this->has_access("a:posts,comments,pages,users,links", 7) and $this->has_access("a:themes,widgets,plugins,fileman", 4)){
					return true;
				}
				return false;
			break;

			case 'at_least_one':

				foreach ($permissions as $permission) {
					if($permission >= $accessLVL)
						return true;
				}
				return false;
			break;
		}
		if($group->get_permission($moduleID, $user_id) >= $accessLVL)
			return true;
		return false;
	}

	/**
	 *
	 * Get link to the list of users
	 * @package uCMS Users
	 * @since uCMS 1.2
	 * @version uCMS 1.3
	 * @return string
	 *
	*/
	function get_userlist_link(){
		$userlist_link = NICE_LINKS ? UCMS_DIR.'/users' : UCMS_DIR.'/?action=users';
		return $userlist_link;
	}

	/**
	 *
	 * Get link to the profile of current user or user by given $id or $login
	 * @package uCMS Users
	 * @since uCMS 1.2
	 * @version uCMS 1.3
	 * @return string
	 *
	*/
	function get_profile_link($id = '', $login = ''){
		$id = empty($id) ? $this->get_user_id() : (int) $id;
		$login = empty($login) ? (empty($id) ? $this->get_user_login() : $this->get_user_login($id)) : urlencode($login);
		$user_link = NICE_LINKS ? UCMS_DIR.'/user/'.$login : UCMS_DIR.'/?action=profile&amp;id='.$id;
		return $user_link;
	}

	/**
	 *
	 * Get link to the user's posts or comments or anything else from $module of current user or user by given $id or $login
	 * @package uCMS Users
	 * @since uCMS 1.2
	 * @version uCMS 1.3
	 * @return string
	 *
	*/
	function get_user_contrib_link($module, $id, $amp = ''){
		$mdl = NICE_LINKS ? UCMS_DIR.'/user/'.$this->get_user_login($id).'/'.$module : UCMS_DIR.'/?action=profile&amp;'.$module.'='.$id.$amp;
		return $mdl;
	}

	/**
	 *
	 * Get logout link
	 * @package uCMS Users
	 * @since uCMS 1.2
	 * @version uCMS 1.3
	 * @return string
	 *
	*/
	function get_logout_link(){
		$logout_link = NICE_LINKS ? UCMS_DIR.'/logout' : UCMS_DIR.'/?action=logout';
		return $logout_link;
	}

	/**
	 *
	 * Get online status of current user or user by given $id
	 * @package uCMS Users
	 * @since uCMS 1.2
	 * @version uCMS 1.3
	 * @return bool
	 *
	*/
	function is_online($id = ''){
		global $udb;
		if($id != ''){
			$id = (int) $id;
			$user = $udb->get_row("SELECT `online` FROM `".UC_PREFIX."users` WHERE `id` = '$id' LIMIT 1");
			$online = $user['online'];
			if($online != 1) return false;
			else return true;
		}else{
			if($this->logged()){
				$id = $this->get_user_id();
				$user = $udb->get_row("SELECT `online` FROM `".UC_PREFIX."users` WHERE `id` = '$id' LIMIT 1");
				$online = $user['online'];
				if($online != 1) return false;
				else return true;
			}
			else return false;
		}
	}

	/**
	 *
	 * Get link to the control panel to edit current user or user by given $id
	 * @package uCMS Users
	 * @since uCMS 1.3
	 * @version uCMS 1.3
	 * @return string
	 *
	*/
	function get_admin_edit_link($id = ''){
		$id = empty($id) ? $this->get_user_id() : $id;
		return UCMS_DIR."/admin/manage.php?module=users&amp;action=update&amp;id=$id";
	}

	/**
	 *
	 * Get user additional info from $column from given array $profile_info or prepared one
	 * of current user or user by given $user_id
	 * @package uCMS Users
	 * @since uCMS 1.3
	 * @version uCMS 1.3
	 * @return string
	 *
	*/
	function get_user_info($column, $user_id = 0, $profile_info = array()){
		global $udb;
		$user_id = (int) $user_id;
		$column = $udb->parse_value($column);
		if($user_id < 1){
			$user_id = $this->get_user_id();
		}
		if(empty($profile_info)){
			global $profile_info;
		}
		if(empty($profile_info)){
			$value = $udb->get_row("SELECT `value` FROM `".UC_PREFIX."usersinfo` WHERE `user_id` = '$user_id' AND `name` = '$column' LIMIT 1");
			if($value and count($value) > 0){
				return $value['value'];
			}
		}else{
			for ($i = 0; $i < count($profile_info); $i++) { 
				if($profile_info[$i]['name'] == $column){
					return $profile_info[$i]['value'];
				}
			}
		}
		return false;
	}

	/**
	 *
	 * Get nickname of current user or user by given $id
	 * @package uCMS Users
	 * @since uCMS 1.3
	 * @version uCMS 1.3
	 * @return string
	 *
	*/
	function get_user_nickname($id = ''){
		global $udb;
		if($id == ''){
			if($this->nickname != ""){
				return $this->nickname;
			}
			else return false;
		}else{
			$id = (int) $id;
			$nickname = $this->get_user_info('nickname', $id);
			if(!$nickname) return false;
			else return $nickname;
		}
	}

	function check_login($login, $user_id = ''){
		global $udb;
		$login = $udb->parse_value(stripslashes($login));
		$login = htmlspecialchars($login);
		$login = trim($login);
		$reg = "/[^(\w)|(\s)|(\x7F-\xFF)]/";
		$login = preg_replace($reg,'',$login);
		
		if (mb_strlen($login, "UTF-8") < LOGIN_MIN_SIZE or mb_strlen($login, "UTF-8") > LOGIN_MAX_SIZE){
			$this->user_error(5);
			return false;
		}
		if(isset($user_id) and $user_id != ''){
			$ologin = $this->get_user_login($user_id);
		}else $ologin = '';
		$is_login = $udb->get_row("SELECT `id` FROM `".UC_PREFIX."users` WHERE `login` = '$login' LIMIT 1");
		if (!empty($is_login['id']) and mb_strtolower($login, "UTF-8") != mb_strtolower($ologin, "UTF-8")) {
			$this->user_error(7);
			return false;
		}
		return $login;
	}

	function check_password($password){
		global $udb;
		$password = $udb->parse_value(stripslashes($password));
		$password = htmlspecialchars(trim($password));
		if (strlen($password) < PASSWORD_MIN_SIZE or strlen($password) > PASSWORD_MAX_SIZE){
			$this->user_error(6);
			return false;
		}
		$password = $this->crypt_password($password);
		return $password;
	}

	function set_user_avatar($login){
		global $udb;
		$tmp_name = isset($_FILES['avatar']['tmp_name']) ? $_FILES['avatar']['tmp_name'] : '';
		$avatar = isset($_FILES['avatar']['name']) ? $_FILES['avatar']['name'] : '';
		if (empty($avatar)){
			$avatar = "no-avatar.jpg";
			return $avatar;
		}
		else{
			$ava = $udb->get_row("SELECT `avatar` FROM `".UC_PREFIX."users` WHERE `login` = '$login'");
			if($ava['avatar'] != '' and $ava['avatar'] != 'no-avatar.jpg'){
				if(file_exists(AVATARS_PATH.$ava['avatar']))
					unlink(AVATARS_PATH.$ava['avatar']);
			}
			$types = array('image/jpeg', 'image/png', 'image/gif');
			$avatar_dir = ABSPATH.'content/avatars/';
			$finfo = finfo_open(FILEINFO_MIME_TYPE);
			$mime = finfo_file($finfo, $tmp_name);
			finfo_close($finfo);
			if(in_array($mime, $types) && preg_match('/[.](JPG)|(jpg)|(gif)|(GIF)|(png)|(PNG)$/',$_FILES['avatar']['name'])){	
				$filename = $avatar;
				$source = $tmp_name;	
				$target = $avatar_dir.$filename;
				move_uploaded_file($source, $target);
				if(preg_match('/[.](GIF)|(gif)$/', $filename))
					$im = imagecreatefromgif($avatar_dir.$filename);
			
				if(preg_match('/[.](PNG)|(png)$/', $filename))
					$im = imagecreatefrompng($avatar_dir.$filename);

				if(preg_match('/[.](JPG)|(jpg)|(jpeg)|(JPEG)$/', $filename))
					$im = imagecreatefromjpeg($avatar_dir.$filename);
		
				$w_src = imagesx($im);
				$h_src = imagesy($im);
				$dest = imagecreatetruecolor(AVATAR_WIDTH, AVATAR_HEIGHT); 
				if ($w_src > $h_src) 
					imagecopyresampled($dest, $im, 0, 0,
				round((max($w_src, $h_src) - min($w_src, $h_src)) / 2), 0, AVATAR_WIDTH, AVATAR_HEIGHT, min($w_src, $h_src), min($w_src, $h_src)); 
				if ($w_src < $h_src) 
					imagecopyresampled($dest, $im, 0, 0, 0, 0, AVATAR_WIDTH, AVATAR_HEIGHT, min($w_src ,$h_src), min($w_src, $h_src)); 
				if ($w_src == $h_src)
					imagecopyresampled($dest, $im, 0, 0, 0, 0, AVATAR_WIDTH, AVATAR_HEIGHT, $w_src, $w_src); 
				$avatar = preg_replace('/[^a-zA-Zа-яА-Я0-9_-]/ui', "", mb_strtolower($login, "UTF-8")).".jpg";
				imagejpeg($dest, $avatar_dir.$avatar);
				$delfull = $avatar_dir.$filename; 
				unlink ($delfull);
				return $avatar;
			}else{
				$this->user_error(8);
				$avatar = "no-avatar.jpg";
				return $avatar;
			}
		}
	}

	function user_error($err_code){
		global $ucms;
		$err_array = array(
			1 => "- ".$ucms->cout("module.users.error.missing_info.label",	 	true)."<br>",
			2 => "- ".$ucms->cout("module.users.error.wrong_email.label",	 	true)."<br>",
			3 => "- ".$ucms->cout("module.users.error.wrong_code.label",	 	true)."<br>",
			4 => "- ".$ucms->cout("module.users.error.unknown.label",	 	 	true)."<br>",
			5 => "- ".$ucms->cout("module.users.error.login_size.label",	 	true, LOGIN_MIN_SIZE, LOGIN_MAX_SIZE)."<br>",
			6 => "- ".$ucms->cout("module.users.error.password_size.label",	    true, PASSWORD_MIN_SIZE, PASSWORD_MAX_SIZE)."<br>",
			7 => "- ".$ucms->cout("module.users.error.login_registered.label",  true)."<br>",
			8 => "- ".$ucms->cout("module.users.error.wrong_avatar.label",	 	true)."<br>",
			9 => "- ".$ucms->cout("module.users.error.email_registered.label",	true)."<br>",
			10 => 	  $ucms->cout("module.users.error.no_registration.label",	true));
		echo $err_array[$err_code];
	}
}
?>