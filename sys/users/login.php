<?php

class login extends uSers{

	function authenticate(){
		global $udb;
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
			echo "Вы ввели не всю информацию, вернитесь назад и заполните все поля!";
		}else{
			$login = $udb->parse_value(stripslashes($login));
			$login = trim(htmlspecialchars($login));
			$password = $udb->parse_value(stripslashes($password));
			$password = trim(htmlspecialchars($password));
			$ip = $this->get_user_ip();
			$udb->query("DELETE FROM `".UC_PREFIX."attempts` WHERE UNIX_TIMESTAMP() - UNIX_TIMESTAMP(date) > 900");  
			$usercheck = $udb->get_row("SELECT `times` FROM `".UC_PREFIX."attempts` WHERE `ip` = '$ip'");
			if ($usercheck['times'] >= LOGIN_ATTEMPTS_NUM)
				echo "Вы набрали логин или пароль неверно ".LOGIN_ATTEMPTS_NUM." раз. Подождите 15 минут до следующей попытки.";
			else{
				$password = $this->crypt_password($password); 
				$usercheck = $udb->get_row("SELECT * FROM `".UC_PREFIX."users` WHERE (`login` = '$login' OR `email` = '$login') AND `password` = '$password' AND `activation` = '1' LIMIT 1");    
				if(empty($usercheck['id'])){       
					$tmp = $udb->get_row("SELECT `ip` FROM `".UC_PREFIX."attempts` WHERE `ip` = '$ip' LIMIT 1");
					if($ip == $tmp[0]){
						$timez = $udb->get_row("SELECT `times` FROM `".UC_PREFIX."attempts` WHERE `ip` = '$ip' LIMIT 1");        
						$times = $timez[0] + 1;
						$udb->query("UPDATE `".UC_PREFIX."attempts` SET `times`= '$times', `date` = NOW() WHERE `ip`='$ip'");
					}	          
					else{
						$udb->query("INSERT INTO `".UC_PREFIX."attempts` (ip,date,times) VALUES ('$ip',NOW(),'1')");            
					}
					echo "Извините, введённые вами логин или пароль неверные.";
				}
				else{ 
					if(isset($_SESSION['admin-login'])){
						unset($_SESSION['admin-login']);
						header("Location: ".UCMS_DIR."/admin");
					}else{
						header("Location: ".$this->get_back_url());
					}
					
					$_SESSION['password'] = $password; 
					$_SESSION['login'] = $usercheck['login']; 
					$_SESSION['id'] = $usercheck['id'];
					
					$ip = $this->get_user_ip();

					$hash = md5($this->session_hash(10));

					$_SESSION['hash'] = $hash;
					$udb->query("UPDATE `".UC_PREFIX."users` SET `logip` = '$ip', `session_hash` = '$hash' WHERE `login` = '$_SESSION[login]'");
					$udb->query("UPDATE `".UC_PREFIX."users` SET `online` = '1' WHERE `login` = '$_SESSION[login]'");
					$udb->query("UPDATE `".UC_PREFIX."stats` SET `value` = `value` - 1, `update` = NOW() WHERE `id` = '1'");
					if (isset($_POST['auto']) and $_POST['auto'] == 1){
						setcookie("hash", $hash, time() + 60 * 60 * 24 * 30, '/');
						setcookie("id", $_SESSION['id'], time() + 60 * 60 * 24 * 30, '/');	
					}
				}                 
			}
		}
	}

	function login_form(){
		$register_link = NICE_LINKS ? UCMS_DIR.'/registration' : UCMS_DIR.'/?action=registration';
		$reset_link = NICE_LINKS ? UCMS_DIR.'/reset' : UCMS_DIR.'/?action=reset';
		$login_link = NICE_LINKS ? UCMS_DIR.'/login' : UCMS_DIR.'/?action=login';
		?>
			<form action="<?php echo $login_link ?>" method="post">
					<table style="margin: 0 auto;">
						<tr>					
							<td><input name="login" type="text" size="15" maxlength="25" placeholder="логин или e-mail" required ></td>
						</tr>
						<tr>
							<td><input name="password" type="password" size="15" maxlength="15" placeholder="пароль" required></td>
						</tr>
						<tr>
							<td><input name="auto" type="checkbox" value="1" >Запомнить меня</td>
						</tr>
						<tr>
							<td><button type="submit" name="submit" class="ubutton">Войти</button></td>
						</tr>
						<?php if(ALLOW_REGISTRATION){ ?><tr>
							<td><a href="<?php echo $register_link; ?>">Зарегистрироваться</a></td> 
						</tr><?php } ?>
						<tr>
							<td><a href="<?php echo $reset_link; ?>">Забыли пароль?</a> 
						</tr>
					</table>
			</form>
			<?php
	}

	function login_test(){
		if(isset($_POST['login']) and isset($_POST['password'])){
			echo '<div class="error">';
			echo $this->authenticate();
			echo '</div>';
		}
	}
}    
?>