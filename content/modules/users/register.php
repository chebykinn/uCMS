<?php
class register extends uSers{
	var $error = false;
	function register_user($login, $password, $email){
		global $udb, $event;
		if(!ALLOW_REGISTRATION){
			$this->user_error(10);
			$this->error = true;
			return false;
		}else{
			if ($login == '')
				unset($login);
	
			if ($password == '')
				unset($password);

			if ($email == '')
				unset($email);
	
			if (empty($login) or empty($password) or empty($email)) {
				$this->user_error(1);
				$this->error = true;
			}

			if(!$this->check_login($login))
				$this->error = true;
			else
				$login = $this->check_login($login);
			
			if(!$this->check_password($password))
				$this->error = true;
			else
				$password = $this->check_password($password);
			
			if (!preg_match("/@/i", $email)) {
				$this->user_error(2);
				$this->error = true;
			}else{
				$email = $udb->parse_value($email);
				$test = $udb->get_row("SELECT `id` FROM `".UC_PREFIX."users` WHERE `email` = '$email'");
				if(!empty($test['id']) and UNIQUE_EMAILS){
					$this->user_error(9);
					$this->error = true;
				}
			}
			$event->do_actions("user.registration.check");
			if(!$this->error){
				if(USER_AVATARS)
					$avatar = $this->set_user_avatar($login);
				else $avatar = '';
				$ip = $this->get_user_ip();
				$result = $udb->query("INSERT INTO `".UC_PREFIX."users` (`id`, `login`, `password`, `group`, `avatar`, `email`, `activation`, `date`, `session_hash`, `regip`, `logip`, `online`, `lastlogin`)
					VALUES(NULL,'$login','$password', '".DEFAULT_GROUP."','$avatar','$email', 0, NOW(), '', '$ip', '$ip', '0', NOW())");
				if ($result){
					$user = $udb->get_row("SELECT `id` FROM `".UC_PREFIX."users` WHERE `login` = '$login' LIMIT 1");
					$user_id = $user['id'];
					if(USERS_ACTIVATION)
						$mail = $this->send_message($login, $user_id, $email);
					else{
						$mail = true;
					}
					if(!$mail){
						$this->user_error(4);
						$udb->query("DELETE FROM `".UC_PREFIX."users` WHERE `id` = '$user_id'");
						return false;
					}
					if(!NICE_LINKS)
						header("Location: ".UCMS_DIR."/?action=registration");
					else
						header("Location: ".UCMS_DIR."/registration");
					$_SESSION['register'] = 'success';
					$event->do_actions("user.registered", array($login, $email));
				}
				else{
					$this->user_error(4);
					return false;
				}
			}else{
				return false;
			}
		}
	}

	function send_message($login, $user_id, $email){
		global $ucms, $user;
		$domain = preg_replace('#(http://)#', '', SITE_DOMAIN);
		$activation = md5($user_id).md5($login);
		$activation_link = NICE_LINKS ?
		"<a href=\"".SITE_DOMAIN.UCMS_DIR."/activation/$login/$activation\">".$ucms->cout("module.users.registration.activation_link", true)."</a>"
		 : "<a href=\"".SITE_DOMAIN.UCMS_DIR."/?action=activation&amp;login=$login&amp;code=$activation\">".$ucms->cout("module.users.registration.activation_link", true)."</a>";
		$headers = "Content-type:text/html; charset=utf-8\r\n";
		$subject = $ucms->cout("module.users.registration.message.subject", true);
		$message = $ucms->cout("module.users.registration.message.text", true, SITE_NAME, $login, $activation_link, SITE_NAME);
		$headers .= 'From: '.'=?UTF-8?B?'.base64_encode(SITE_NAME).'?='.' <uCMS@'.$domain.'>'."\r\n";
		$sent = mail($email, '=?UTF-8?B?'.base64_encode($subject).'?=', $message, $headers);
		if($sent) return true;
		else return false;
		
	}

	function registration_form(){
		if(!ALLOW_REGISTRATION){
			echo '<div class="error">';
			$this->user_error(10);
			echo '</div>';
			return false;
		}
		global $event, $ucms;
		$ucms->template(get_module('path', 'users')."forms/registration_form.php");
	}

	function registration_test(){
		global $ucms;
		if(isset($_POST['login']) and isset($_POST['password']) and isset($_POST['email'])){
			echo '<div class="error">';
			echo '<b>'.$ucms->cout("module.users.registration.errors_occurred", true).'</b><br><br>';
			echo $this->register_user($_POST['login'], $_POST['password'], $_POST['email']);
			echo '</div><br>';
		}else 
		if(isset($_SESSION['register'])){
			echo '<div class="success">';
			if(USERS_ACTIVATION){
				$ucms->cout("module.users.registration.alert.success.registered.activate");
			}
			else{
				$ucms->cout("module.users.registration.alert.success.registered.no_activate");
			}
			echo '</div><br>';
			unset($_SESSION['register']);
		}

	}
}
?>