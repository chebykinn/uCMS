<?php
class uSers{

	function reset_form(){
		$action = NICE_LINKS? UCMS_DIR."/reset" : UCMS_DIR."/?action=reset";
		?>
		<form action="<?php echo $action ?>" method="post" style="margin: 0 auto; text-align; center;">
		<table class="reset" style="margin: 0 auto; text-align; center;">
		<?php if(!UNIQUE_EMAILS) { ?>
		<tr>
        	<td>Введите Ваш Логин: <br><input type="text" name="login" required><br><br></td>
        </tr><?php } ?>
        <tr>
        	<td>Введите Ваш E-mail: <br><input type="email" name="email" required><br><br></td>
        </tr>
        <tr>	
        	<td><input type="submit" name="submit" value="Отправить" class="ubutton"></td>
       	</tr>
        </table> 
        </form>
        <?php
    	
	}

	function get_user_id($login = ''){
		global $udb;
		if($login == ''){
			if(isset($_SESSION['id']) and $_SESSION['id'] != '')
				return $_SESSION['id'];
			else return false;
		}else{
			global $udb;
			$login = $udb->parse_value($login);
			$id = $udb->get_row("SELECT `id` FROM `".UC_PREFIX."users` WHERE `login` = '$login' LIMIT 1");
			$id = $id[0];
			if($id != '') return $id;
			else return false;
		}
	}

	function get_user_login($id = ''){
		global $udb;
		if($id == ''){
			if(isset($_SESSION['login']) and $_SESSION['login'] != ''){
				$id = $this->get_user_id();
				if(UPDATE_USER == $id){
					$user = $udb->get_row("SELECT `login` FROM `".UC_PREFIX."users` WHERE `id` = '$id' LIMIT 1");
					if(!$user){
						$this->logout();
						return false;
					}
					$login = $user['login'];
					if($_SESSION['login'] == $login) return $_SESSION['login'];
					else{
						$_SESSION['login'] = $login;
						return $_SESSION['login'];
					}	
				}else return $_SESSION['login'];
				
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

	function get_user_password(){
		global $udb;
		if(isset($_SESSION['password']) and $_SESSION['password'] != ''){
			$id = $this->get_user_id();
			if(UPDATE_USER == $id){
				$user = $udb->get_row("SELECT `password` FROM `".UC_PREFIX."users` WHERE `id` = '$id' LIMIT 1");
				if(!$user){
					$this->logout();
					return false;
				}
				$password = $user['password'];
				if($_SESSION['password'] == $password) return $_SESSION['password'];
				else{
					$_SESSION['password'] = $password;
					return $_SESSION['password'];
				}
			}else return $_SESSION['password'];
			
		}
		else return false;
	}

	function get_user_group($id = '', $login = ''){
		global $udb;
		if($id == '' and $login == ''){
			$id = $this->get_user_id();
			if(!isset($_SESSION['group']) or UPDATE_USER == $id){
				$login = $this->get_user_login();
				$password = $this->get_user_password();
				$user = $udb->get_row("SELECT `group` FROM `".UC_PREFIX."users` WHERE `login` = '$login' AND `password` = '$password' LIMIT 1");
				$group = $user['group'];
			}else $group = $_SESSION['group'];
			if(!$group){
				if(!$this->logged())
					return 6;
				else{ 
					$udb->get_query("UPDATE `".UC_PREFIX."stats` SET `value` = '$id' WHERE `id` = '2' LIMIT 1");
					return false;
				}
			} 
			else{ 
				$_SESSION['group'] = $group;
				return $group;
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
			if($this->logged()){
				$id = $this->get_user_id();
				if(!isset($_SESSION['avatar']) or UPDATE_USER == $id){
					$login = $this->get_user_login();
					$password = $this->get_user_password();
					$user = $udb->get_row("SELECT `avatar` FROM `".UC_PREFIX."users` WHERE `login` = '$login' AND `password` = '$password' LIMIT 1");
					$avatar = $user['avatar'];
				}else $avatar = $_SESSION['avatar'];
				if(!$avatar){ 
					$udb->get_query("UPDATE `".UC_PREFIX."stats` SET `value` = '$id' WHERE `id` = '2' LIMIT 1");
					return false;
				}
				else{ 
					$_SESSION['avatar'] = $avatar;
					return $avatar;
				}
			}
		}
		
	}

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
			if($this->logged()){
				$id = $this->get_user_id();
				if(!isset($_SESSION['email']) or UPDATE_USER == $id){
					$login = $this->get_user_login();
					$password = $this->get_user_password();
					$user = $udb->get_row("SELECT `email` FROM `".UC_PREFIX."users` WHERE `login` = '$login' AND `password` = '$password' LIMIT 1");
					$email = $user['email'];
				}else $email = $_SESSION['email'];
				if(!$email){ 
					$udb->get_query("UPDATE `".UC_PREFIX."stats` SET `value` = '$id' WHERE `id` = '2' LIMIT 1");
					return false;
				}else{
					$_SESSION['email'] = $email;
					return $email;
				}
			}
			else return false;
		}
	}

	function get_user_surname($id = ''){
		global $udb;
		if($id != ''){
			$id = (int) $id;
			$user = $udb->get_row("SELECT `surname` FROM `".UC_PREFIX."usersinfo` WHERE `userid` = '$id' LIMIT 1");
			$surname = $user['surname'];
			if(!$surname) return false;
			else return $surname;
		}else{
			if($this->logged()){
				$id = $this->get_user_id();
				$user = $udb->get_row("SELECT `surname` FROM `".UC_PREFIX."usersinfo` WHERE `userid` = '$id' LIMIT 1");
				$surname = $user['surname'];
				return $surname;
			}
			else return false;
		}
	}

	function get_user_firstname($id = ''){
		global $udb;
		if($id != ''){
			$id = (int) $id;
			$user = $udb->get_row("SELECT `firstname` FROM `".UC_PREFIX."usersinfo` WHERE `userid` = '$id' LIMIT 1");
			$firstname = $user['firstname'];
			if(!$firstname) return false;
			else return $firstname;
		}else{
			if($this->logged()){
				$id = $this->get_user_id();
				$user = $udb->get_row("SELECT `firstname` FROM `".UC_PREFIX."usersinfo` WHERE `userid` = '$id' LIMIT 1");
				$firstname = $user['firstname'];
				return $firstname;
			}
			else return false;
		}
	}

	function get_user_icq($id = ''){
		global $udb;
		if($id != ''){
			$id = (int) $id;
			$user = $udb->get_row("SELECT `icq` FROM `".UC_PREFIX."usersinfo` WHERE `userid` = '$id' LIMIT 1");
			$icq = $user['icq'];
			if(!$icq) return false;
			else return $icq;
		}else{
			if($this->logged()){
				$id = $this->get_user_id();
				$user = $udb->get_row("SELECT `icq` FROM `".UC_PREFIX."usersinfo` WHERE `userid` = '$id' LIMIT 1");
				$icq = $user['icq'];
				return $icq;
			}
			else return false;
		}
	}

	function get_user_skype($id = ''){
		global $udb;
		if($id != ''){
			$id = (int) $id;
			$user = $udb->get_row("SELECT `skype` FROM `".UC_PREFIX."usersinfo` WHERE `userid` = '$id' LIMIT 1");
			$skype = $user['skype'];
			if(!$skype) return false;
			else return $skype;
		}else{
			if($this->logged()){
				$id = $this->get_user_id();
				$user = $udb->get_row("SELECT `skype` FROM `".UC_PREFIX."usersinfo` WHERE `userid` = '$id' LIMIT 1");
				$skype = $user['skype'];
				return $skype;
			}
			else return false;
		}
	}

	function get_user_addinfo($id = ''){
		global $udb;
		if($id != ''){
			$id = (int) $id;
			$user = $udb->get_row("SELECT `addinfo` FROM `".UC_PREFIX."usersinfo` WHERE `userid` = '$id' LIMIT 1");
			$addinfo = $user['addinfo'];
			if(!$addinfo) return false;
			else return $addinfo;
		}else{
			if($this->logged()){
				$id = $this->get_user_id();
				$user = $udb->get_row("SELECT `addinfo` FROM `".UC_PREFIX."usersinfo` WHERE `userid` = '$id' LIMIT 1");
				$addinfo = $user['addinfo'];
				return $addinfo;
			}
			else return false;
		}
	}

	function get_user_birthdate($id = ''){
		global $udb;
		if($id != ''){
			$id = (int) $id;
			$user = $udb->get_row("SELECT `birthdate` FROM `".UC_PREFIX."usersinfo` WHERE `userid` = '$id' LIMIT 1");
			$birthdate = $user['birthdate'];
			if(!$birthdate) return false;
			else return $birthdate;
		}else{
			if($this->logged()){
				$id = $this->get_user_id();
				$user = $udb->get_row("SELECT `birthdate` FROM `".UC_PREFIX."usersinfo` WHERE `userid` = '$id' LIMIT 1");
				$birthdate = $user['birthdate'];
				return $birthdate;
			}
			else return false;
		}
	}

	function get_user_pm_subscription($id = ''){
		global $udb;
		if($id != ''){
			$id = (int) $id;
			$user = $udb->get_row("SELECT `pm-alert` FROM `".UC_PREFIX."usersinfo` WHERE `userid` = '$id' LIMIT 1");
			$pm_alert = $user['pm-alert'];
			if($pm_alert != 1) return false;
			else return true;
		}else{
			if($this->logged()){
				$id = $this->get_user_id();
				$user = $udb->get_row("SELECT `pm-alert` FROM `".UC_PREFIX."usersinfo` WHERE `userid` = '$id' LIMIT 1");
				$pm_alert = $user['pm-alert'];
				if($pm_alert != 1) return false;
				else return true;
			}
			else return false;
		}
	}

	function get_activation_result(){
		if(isset($_SESSION['activate'])){
			if($_SESSION['activate'])
				echo '<div class="success">Ваш аккаунт подтвержден! Теперь вы можете зайти на сайт '.SITE_NAME.'!</div>';
			else
				echo '<div class="error">Произошла ошибка и ваш аккаунт не был подтвержден.</div>';
			unset($_SESSION['activate']);
		}
	}

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


	function get_user_ip(){
		$ip = getenv("HTTP_X_FORWARDED_FOR");
		if (empty($ip) || $ip == 'unknown'){
			$ip = getenv("REMOTE_ADDR"); 
		}
		return $ip;
	}

	function session_hash($length = 6){
		$chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHI JKLMNOPRQSTUVWXYZ0123456789";
     	$code = "";
     	$clen = strlen($chars) - 1;  
     	while (strlen($code) < $length) {
            $code .= $chars[mt_rand(0,$clen)];  
    	}
    	return $code;
	}

	function guest(){
		global $udb;
		if(!$this->logged()){
			$guest_id = session_id();
			
			if(!isset($_SESSION['guest_time']) or !isset($_SESSION['guest_login'])){
				$_SESSION['guest_login'] = "Гость".date("His");
				$guest_add = $udb->query("UPDATE `".UC_PREFIX."stats` SET `value` = `value` + 1, `update` = NOW() WHERE `id` = '1'");
				if($guest_add){
					$_SESSION['guest_time'] = getdate();
					return true;
				}
				$_SESSION['guest_time'] = getdate();
			}else{
				$newtime = getdate();
				$newmin = $newtime['minutes'] - $_SESSION['guest_time']['minutes'];
				if($newmin >= 15){
					$udb->query("UPDATE `".UC_PREFIX."stats` SET `value` = `value` + 1, `update` = NOW() WHERE `id` = '1'");
				}
			}
		}else return false;
	}

	function get_guests_count(){
		global $udb;
		$count = $udb->get_row("SELECT `value` FROM `".UC_PREFIX."stats` WHERE `id` = '1'");
		if($count) return $count['value'];
		else return false;
	}

	function logged(){
		if(!$this->get_user_login() or !$this->get_user_password()){
			if(!$this->get_user_id()){
				return false;
			}else{
				unset($_SESSION['id']);
				return false;
			}
		}else return true;
	}

	function logout(){
		session_destroy();
		global $udb;
		$user_id = $this->get_user_id();
		$udb->query("UPDATE `".UC_PREFIX."users` SET `online` = '0' WHERE `id` = '$user_id'");
		unset($_SESSION['avatar']);
		unset($_SESSION['email']);
		unset($_SESSION['group']);
		unset($_SESSION['password']);
		unset($_SESSION['login']); 
		unset($_SESSION['id']);
		setcookie("id", "", time() - 3600*24*30*12);
		setcookie("hash", "", time() - 3600*24*30*12);
	}

	function admin(){
		if($this->logged() and $this->get_user_group() == 1) return true;
		else return false;
	}

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

	function autologin(){
		global $udb;
		$count = $udb->num_rows("SELECT * FROM `".UC_PREFIX."users` LIMIT 1");
		if(!$count){
			header("Location: ".UCMS_DIR."/sys/install/index.php");
			exit;
		}
		if(!$this->logged()){
			if(isset($_COOKIE['id']) and isset($_COOKIE['hash'])){
				if($_COOKIE['id'] > 0 and $_COOKIE['hash'] != ''){
						$test = $udb->get_row("SELECT `session_hash`, `id` FROM `".UC_PREFIX."users` WHERE `id` = '".intval($_COOKIE['id'])."' LIMIT 1");
						if($test['session_hash'] == $_COOKIE['hash'] and $test['id'] == $_COOKIE['id']){
							$userdata = $udb->get_row("SELECT `password`, `group`, `login` FROM `".UC_PREFIX."users` WHERE `id` = '$test[id]' LIMIT 1");
							$_SESSION['id'] = $test['id'];
							$_SESSION['group'] = $userdata['group']; 
							$_SESSION['password'] = $userdata['password']; 
							$_SESSION['login'] = $userdata['login'];
							$udb->query("UPDATE `".UC_PREFIX."users` SET `online` = '1' WHERE `id` = '$test[id]'");
							return true;
						}else{
							setcookie("id", "", time() - 3600*24*30*12, "/");
       						setcookie("hash", "", time() - 3600*24*30*12, "/");
       						return false;
						}
					}else return false;
				}else return false;
		}else{
			$uid = $this->get_user_id();
			$udb->query("UPDATE `".UC_PREFIX."users` SET `online` = '1', `lastlogin` = NOW() WHERE `id` = '$uid' AND UNIX_TIMESTAMP() - UNIX_TIMESTAMP(lastlogin) > 1300");
			return true;
		}
	}

	function crypt_password($password){
	 	global $udb;
		$salt = substr(sha1($password),0,22);
		$password = $udb->parse_value(stripslashes($password));
		$password = htmlspecialchars(trim($password));
		$password = crypt($password, '$2a$10$'.$salt);
		return $password;
	}

	function reset_password(){
		global $udb, $url_all, $ucms;
		$domain = preg_replace("#(http://)#", '', SITE_DOMAIN);
		if(isset($_SESSION['reset-stage1']) and $_SESSION['reset-stage1']){
			echo '<br><div class="success">Ссылка на потверждение восстановления пароля была отправлена на указанный e-mail.</div><br>';
			unset($_SESSION['reset-stage1']);
		}
		if(isset($_SESSION['reset-stage2']) and $_SESSION['reset-stage2']){
			echo '<br><div class="success">Новый пароль успешно сгенерирован и выслан вам на e-mail.</div><br>';
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
					echo "<div class=\"error\">Пользователя с таким e-mail адресом или логином не существует.</div>";
	            }else{
	            	if(!isset($login) or $login == '') $login = $this->get_user_login($result['id']); 
					$time = date('H'); 
					$t = strptime($time, '%H'); 
					$duration = mktime($t['tm_hour']+4); 
					$duration = date('H', $duration + 4); 
					$secret = md5(strrev($result['id'].$email.$login.$duration));
					if(UNIQUE_EMAILS){
						$link = NICE_LINKS ? "<a href=\"".UCMS_URL."/reset/$email/$secret\">ссылке</a>" : "<a href=\"".UCMS_URL."/?action=reset&amp;email=$email&amp;code=$secret\">ссылке</a>";
					}else{
						$link = NICE_LINKS ? "<a href=\"".UCMS_URL."/reset/$login/$email/$secret\">ссылке</a>" : "<a href=\"".UCMS_URL."/?action=reset&amp;login=$login&amp;email=$email&amp;code=$secret\">ссылке</a>";
					}         	
	            	
	            	$headers = "Content-type:text/html; charset=utf-8\r\n";
	            	$subject = "Заявка на восстановление пароля";
					$headers .= 'From: '.'=?UTF-8?B?'.base64_encode(SITE_NAME).'?='.' <uCMS@'.$domain.'>'."\r\n";
					$message = "Здравствуйте, ".$login."! <br> Вы давали заявку на смену пароля. Чтобы сменить его, перейдите по $link. (действительна 1 час) <br>Если вы этого не делали, то просто проигнорируйте письмо.";
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
					echo "<div class=\"error\">Пользователя с таким e-mail адресом или логином не существует.</div>";
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
	            		$subject = "Восстановление пароля";
	            		$headers .= 'From: '.'=?UTF-8?B?'.base64_encode(SITE_NAME).'?='.' <ucms@'.$domain.'>'."\r\n";
	                	$message = "Здравствуйте, ".$login."! <br> Система сгенерировала для Вас пароль, теперь Вы сможете войти на сайт ".SITE_NAME.", используя его. После входа желательно его сменить.<br> <b>Пароль:</b><br>".$new_password;
	                	$headers .= "Content-type:text/html; charset=utf-8\r\n";
	                	mail($mail, '=?UTF-8?B?'.base64_encode($subject).'?=', $message, $headers);
	                	if(ADMIN_EMAIL != ''){
	               			$user_link = NICE_LINKS ? "<a href=\"".UCMS_URL."/user/$login\">$login</a>" : "<a href=\"".UCMS_URL."/?action=profle&amp;id=$result[id]\">$login</a>";
	               			$headers2 = "Content-type:text/html; charset=utf-8\r\n";
	            			$subject2 = "Пользователь восстановил пароль";
	            			$headers2 .= 'From: '.'=?UTF-8?B?'.base64_encode(SITE_NAME).'?='.' <ucms@'.$domain.'>'."\r\n";
	               			$message2 = "Здравствуйте, Администратор! <br> Пользователь $user_link восстановил пароль.";
	               			$headers2 .= "Content-type:text/html; charset=utf-8\r\n";
	               			mail(ADMIN_EMAIL, '=?UTF-8?B?'.base64_encode($subject2).'?=', $message2, $headers2);
	               		}
	 					$back = NICE_LINKS ? UCMS_DIR."/reset" : UCMS_DIR."/?action=reset"; 
	 					header("Location: $back");
	 					$_SESSION['reset-stage2'] = true;
	 					return true;
					}else{
						echo "<div class=\"error\">Введен неверный код или ссылка устарела.</div>";
					}
				}
 		}
 	}

 	function get_profile(){
 		global $user_id, $udb;
 		if ($this->has_access(4, 1)){
			$result = $udb->get_row("SELECT `email`, `login`, `group`, `avatar`, `activation` FROM `".UC_PREFIX."users` WHERE `id` = '$user_id' LIMIT 1"); 
			if ( !$result or ($result['activation'] != 1 and !$this->has_access(4, 4)) ) {   
				return false;
			}else{
				$addresult = $udb->get_row("SELECT * FROM `".UC_PREFIX."usersinfo` WHERE `userid` = '$user_id' LIMIT 1"); 
				$user_login = $result['login'];
				$user_email = $result['email'];
				$user_group = $result['group'];
				$groupresult = $udb->get_row("SELECT `name` FROM `".UC_PREFIX."groups` WHERE `id` = '$user_group' LIMIT 1"); 
				$user_group = $groupresult['name'];
				$user_surname = $addresult['surname'];
				$user_firstname = $addresult['firstname'];
				$user_icq = $addresult['icq'];
				$user_skype = $addresult['skype'];
				$user_birthdate = $addresult['birthdate'];
				$user_addinfo = $addresult['addinfo'];
				if(USER_AVATARS) 
					$user_avatar = $result['avatar'];
				else $user_avatar = '';
				$profile = array(
					0 => $user_login, 
					1 => $user_email,
					2 => $user_group, 
					3 => $user_avatar,	
					4 => $user_surname,
					5 => $user_firstname,
					6 => $user_icq,
					7 => $user_skype,
					8 => $user_birthdate,
					9 => $user_addinfo);
				return $profile;
			}
		}	
	}

	function is_profile(){
		global $profile_login;
		$login = $this->get_user_login();
		if($profile_login == $login){
			return true;
		}else return false;
	}

	function get_back_url(){
		$back_link = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : UCMS_DIR.'/';
		return $back_link;
	}

	function profile_menu(){
		global $url_all;
		if(!$this->logged()):
			require_once ABSPATH.UC_USERS_PATH.'login.php';
			$login = new login();
			$login->login_form();
 		else: 
 			if(isset($_SESSION['activate']) and $_SESSION['activate']){
				echo '<div class="success" style="width: 80%;">Ваш профиль успешно активирован!</div>';
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
			<li><a href="<?php echo $profile_link; ?>">Ваш профиль</a></li>
			<li><a href="<?php echo $userlist_link; ?>">Список пользователей</a></li>
			<li><a href="<?php echo $logout_link; ?>">Выход</a><br></li>
			<?php if($this->has_access(0, 4)) echo '<li><a href="'.UCMS_DIR.'/admin">Панель управления</a><br></li>'; ?>
			</ul>
			</div> 
		<?php
		endif; 
	}

	function list_users(){
		echo "<br><br>"; 
		global $page, $udb, $count, $pages_count, $users, $ucms;
		$module = NICE_LINKS ? UCMS_DIR.'/users' : UCMS_DIR.'/?action=userlist&amp;';
		if($this->has_access(4, 1)){
		pages($page, $count, $pages_count, 10);
		?><br> 
		<table class="userlist">
			<tr>
				<?php if(USER_AVATARS) echo '<td><b>Аватар</b></td>'; ?>
				<td><b>Логин</b></td>
				<td><b>Группа</b></td>
				<td><b>Дата регистрации</b></td>
			</tr>
			<?php
				for($i = 0; $i < count($users); $i++){
					$groups[] = $users[$i]['group'];
				}
				$groups = implode("','", $groups);
				$groups = "'".$groups."'";
				$groups_meta = $udb->get_rows("SELECT `id`, `name` FROM `".UC_PREFIX."groups` WHERE `id` in ($groups) ");

           		for($i = 0; $i < count($users); $i++){
					for($j = 0; $j < count($groups_meta); $j++){
					if($users[$i]['group'] === $groups_meta[$j]['id']){
						$group_name = $groups_meta[$j]['name'];
						break;
					}
				}
				$profile_link = NICE_LINKS ? UCMS_DIR."/user/".$users[$i]['login'] : UCMS_DIR."/?action=profile&id=".$users[$i]['id'];
					?>
					<tr>
						<?php if(USER_AVATARS) echo '<td style="width:64px;"><img src="'.UCMS_DIR."/".AVATARS_PATH.$users[$i]['avatar'].'" alt="'.$users[$i]['login'].'" width="32" height="32" /></td>'; ?>
						<td style="width: 45%;"><a href="<?php echo $profile_link;?>"><?php echo $users[$i]['login']; if($users[$i]['online'] == 1){ echo " (Онлайн)"; } ?></a></td>
						<td><?php echo $group_name; ?></td>
						<td><?php echo $ucms->format_date($users[$i]['date'], false)?></td>
					</tr>
				<?php
            	}
       	 	

			?>
		</table>
		<?php
		}else{
			if($this->logged())
				echo '<div class="error">У вас нет доступа к этой странице.</div>';
			else echo '<div class="error">Вход на эту страницу разрешен только зарегистрированным пользователям.</div>';
		}
	}

	function has_access($accessID = 0, $accessLVL = 2){
		if($accessLVL > 7) $accessLVL = 7;
		if($accessLVL < 0) $accessLVL = 0;
		global $posts_access, $comments_access, $pages_access, $users_access;
		if($accessID == 0){
			if($posts_access >= $accessLVL or $comments_access >= $accessLVL or $pages_access >= $accessLVL or $users_access >= $accessLVL){
				return true;
			}else return false;
		}else{ 
			switch ($accessID) {
				case 1:
					if($posts_access >= $accessLVL)
						return true;
				break;
				
				case 2:
					if($comments_access >= $accessLVL)
						return true;
				break;

				case 3:
					if($pages_access >= $accessLVL)
						return true;
				break;

				case 4:
					if($users_access >= $accessLVL)
						return true;
				break;

				case 5:
					if($posts_access >= $accessLVL and $comments_access >= $accessLVL and $pages_access >= $accessLVL and $users_access >= $accessLVL)
						return true;
				break;

				case 6:
					if($posts_access == $accessLVL and $comments_access == $accessLVL and $pages_access == $accessLVL and $users_access == $accessLVL)
						return true;
				break;
				
				default:
					return false;
				break;
			}
		}
	}

	function get_userlist_link(){
		$userlist_link = NICE_LINKS ? UCMS_DIR.'/users' : UCMS_DIR.'/?action=userlist';
		return $userlist_link;
	}

	function get_profile_link($id = ''){
		$id = empty($id) ? $this->get_user_id() : $id;
		$login = empty($id) ? $this->get_user_login() : $this->get_user_login($id);
		$user_link = NICE_LINKS ? UCMS_DIR.'/user/'.$login : UCMS_DIR.'/?action=profile&amp;id='.$id;
		return $user_link;
	}

	function get_user_contrib_link($module, $id, $amp = ''){
		$mdl = NICE_LINKS ? UCMS_DIR.'/user/'.$this->get_user_login($id).'/'.$module : UCMS_DIR.'/?action=profile&amp;'.$module.'='.$id.$amp;
		return $mdl;
	}

	function get_logout_link(){
		$logout_link = NICE_LINKS ? UCMS_DIR.'/logout' : UCMS_DIR.'/?action=logout';
		return $logout_link;
	}

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
}
?>