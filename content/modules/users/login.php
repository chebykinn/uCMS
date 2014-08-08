<?php

class login extends uSers{
	var $error = false;
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
	
	function authenticate(){
		global $udb, $ucms, $event;

		if(isset($_POST['login'])){ 
			$login = $_POST['login']; 
			if ($login == '')
				unset($login);
		}

		if (isset($_POST['password'])){ 
			$password = $_POST['password']; 
			if ($password == '')
				unset($password);
		}
		if (empty($login) or empty($password)){
			return $ucms->cout("module.users.login.error.empty_fields", true);
		}else{
			$reg = "/[^(\w)|(\s)|(\x7F-\xFF)]/";
			$login = $udb->parse_value(stripslashes($login));
			$login = trim(htmlspecialchars($login));
			$login = preg_replace($reg,'',$login);
			$password = $udb->parse_value(stripslashes($password));
			$password = trim(htmlspecialchars($password));
			$ip = $this->get_user_ip();
			$udb->query("DELETE FROM `".UC_PREFIX."attempts` WHERE UNIX_TIMESTAMP() - UNIX_TIMESTAMP(date) > 900");  
			$usercheck = $udb->get_row("SELECT `times` FROM `".UC_PREFIX."attempts` WHERE `ip` = '$ip'");
			$event->do_actions("user.login.check");
			if ($usercheck['times'] >= LOGIN_ATTEMPTS_NUM and !isset($_POST['code']) or $this->error){
				if(AFTER_FAILED_LOGIN_ACTION == 1){
					$_SESSION['use_captcha'] = true;
					return $ucms->cout("module.users.login.error.failed_login_captcha", true, LOGIN_ATTEMPTS_NUM);
				}else
					return $ucms->cout("module.users.login.error.failed_login_delay", true, LOGIN_ATTEMPTS_NUM);
			}
			else{
				$password = $this->crypt_password($password); 
				$usercheck = $udb->get_row("SELECT * FROM `".UC_PREFIX."users` WHERE (`login` = '$login' OR `email` = '$login') AND `password` = '$password' AND `activation` = '1' LIMIT 1");    
				if(empty($usercheck['id'])){       
					$tmp = $udb->get_row("SELECT `ip` FROM `".UC_PREFIX."attempts` WHERE `ip` = '$ip' LIMIT 1");
					if($ip == $tmp[0]){
						$udb->query("UPDATE `".UC_PREFIX."attempts` SET `times` = `times` + 1, `date` = NOW() WHERE `ip` = '$ip'");
					}	          
					else{
						$add = $udb->query("INSERT INTO `".UC_PREFIX."attempts` (`ip`, `date`, `times`) VALUES ('$ip',NOW(),'1')", true);
						if(!$add){
							$udb->query("UPDATE `".UC_PREFIX."attempts` SET `times` = `times` + 1, `date` = NOW() WHERE `ip` = '$ip'", true);
						}
					}
					return $ucms->cout("module.users.login.error.wrong_login_or_password", true);
				}
				else{ 
					$event->do_actions("user.logged_in", array($usercheck['id']));
					
					if(isset($_SESSION['use_captcha']))
						unset($_SESSION['use_captcha']);
					if(isset($_SESSION['admin-login'])){
						unset($_SESSION['admin-login']);
						header("Location: ".UCMS_DIR."/admin");
					}else{
						header("Location: ".$ucms->get_back_url());
					}

					$hash = md5($this->session_hash(10));

					$_SESSION['id'] = $usercheck['id'];
					$_SESSION['hash'] = $hash;
					unset($_SESSION['guest_login']);
					$udb->query("UPDATE `".UC_PREFIX."users` SET `logip` = '$ip', `session_hash` = '$hash' WHERE `login` = '$usercheck[login]'");
					$udb->query("UPDATE `".UC_PREFIX."users` SET `online` = '1' WHERE `login` = '$usercheck[login]'");
					$udb->query("UPDATE `".UC_PREFIX."attempts` SET `times` = '0' WHERE `ip` = '$ip'");
					$ucms->update_setting("guests_count", GUESTS_COUNT-1);
					if (isset($_POST['auto']) and $_POST['auto'] == 1){
						setcookie("hash", $hash, time() + 60 * 60 * 24 * 30, '/');
						setcookie("id", $usercheck['id'], time() + 60 * 60 * 24 * 30, '/');
					}
				}                 
			}
		}
	}

	function login_form(){
		global $ucms;
		$ucms->template(get_module('path', 'users')."forms/login_form.php");
	}

	function login_test(){
		if(isset($_POST['login']) and isset($_POST['password'])){
			global $result;
			echo '<div class="error">';
			echo $result;
			echo '</div>';
		}
	}
}    
?>