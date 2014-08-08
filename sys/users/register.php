<?php
class register extends uSers{
	var $error = false;
	function register_user($login, $password, $code, $email){
		global $udb;
		if(!ALLOW_REGISTRATION){
			$this->user_error(10);
			$error = true;
			return false;
		}else{
			if ($login == '')
				unset($login);
	
			if ($password == '')
				unset($password);
	
			if ($code == '')
				unset($code);
	
			if ($email == '')
				unset($email);
	
			if (empty($login) or empty($password) or empty($email) or (empty($code) and USE_CAPTCHA > 0)) {
				$this->user_error(1);
				$error = true;
			}

			if(isset($code)){
				if(!$this->check_code($code) and USE_CAPTCHA > 1){
					$this->user_error(3);
					$error = true;
				}
			}else if(USE_CAPTCHA > 1){
				$this->user_error(3);
				$error = true;
			}

			if(!$this->check_login($login))
				$error = true;
			else
				$login = $this->check_login($login);
			
			if(!$this->check_password($password))
				$error = true;
			else
				$password = $this->check_password($password);
			
			if (!preg_match("/@/i", $email)) {
				$this->user_error(2);
				$error = true;
			}else{
				$email = $udb->parse_value($email);
				$test = $udb->get_row("SELECT `id` FROM `".UC_PREFIX."users` WHERE `email` = '$email'");
				if(!empty($test['id']) and UNIQUE_EMAILS){
					$this->user_error(9);
					$error = true;
				}
			}
		
			if(!$error){
				if(USER_AVATARS)
					$avatar = $this->set_user_avatar($login);
				else $avatar = '';
				$ip = $this->get_user_ip();
				$result = $udb->query("INSERT INTO `".UC_PREFIX."users` VALUES(NULL,'$login','$password', '".DEFAULT_GROUP."','$avatar','$email', 0, NOW(), '', '$ip', '$ip', '0', NOW())");
				if ($result){
					$user = $udb->get_row("SELECT `id` FROM `".UC_PREFIX."users` WHERE `login` = '$login' LIMIT 1");
					$user_id = $user['id'];
					$user_meta = $udb->query("INSERT INTO `".UC_PREFIX."usersinfo` VALUES(NULL, '$user_id', '', '', '', '', '', NOW(), 0)");
					$mail = $this->send_message($login, $user_id, $email);
					if(!$mail){
						$this->user_error(4);
						return false;
					}
					if(!NICE_LINKS)
						header("Location: ".UCMS_DIR."/?action=registration");
					else
						header("Location: ".UCMS_DIR."/registration");
					$_SESSION['register'] = 'success';
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

	function check_code($code){
   	 	if(isset($_SESSION['captcha-code'])){
   	 		$code2 = $_SESSION['captcha-code'];
   	 		if($code === $code2){
   	 			unset($_SESSION['captcha-code']);
   	 			return true;
   	 		}else{
   	 			unset($_SESSION['captcha-code']);
   	 			return false;
   	 		}
   		}else return false;
	}

	function check_login($login){
		global $udb;
		$login = $udb->parse_value(stripslashes($login));
		$login = htmlspecialchars($login);
		$login = trim($login);
		$reg = "/[^(\w)|(\x7F-\xFF)|(\s)]/"; 
		$login = preg_replace($reg,'',$login);
		if (mb_strlen($login, "UTF-8") < 4 or mb_strlen($login, "UTF-8") > 16){
			$this->user_error(5);
			return false;
		}
		
		$is_login = $udb->get_row("SELECT `id` FROM `".UC_PREFIX."users` WHERE `login` = '$login' LIMIT 1");
		if (!empty($is_login['id'])) {
			$this->user_error(7);
			return false;
		}
		return $login;
	}

	function check_password($password){
		global $udb;
		$password = $udb->parse_value(stripslashes($password));
		$password = htmlspecialchars(trim($password));
		if (strlen($password) < 6 or strlen($password) > 20){
			$this->user_error(6);
			return false;
		}
		$password = $this->crypt_password($password);
		return $password;
	}

	function set_user_avatar($login){
		$tmp_name = isset($_FILES['avatar']['tmp_name']) ? $_FILES['avatar']['tmp_name'] : '';
		$avatar = isset($_FILES['avatar']['name']) ? $_FILES['avatar']['name'] : '';
		if (empty($avatar)){
			$avatar = "no-avatar.jpg";
			return $avatar;
		}
		else{
			$avatar_dir = 'content/avatars/';
			if(preg_match('/[.](JPG)|(jpg)|(gif)|(GIF)|(png)|(PNG)$/',$_FILES['avatar']['name'])){	
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
				imagejpeg($dest, $avatar_dir.mb_strtolower($login, "UTF-8").".jpg");
				$avatar = mb_strtolower($login, "UTF-8").".jpg";
				$delfull = $avatar_dir.$filename; 
				unlink ($delfull);
				return $avatar;
			}else{
				$this->user_error(8);
				$error = true;
			}
		}
	}	

	function send_message($login, $user_id, $email){
		global $ucms, $user;
		$domain = preg_replace('#(http://)#', '', SITE_DOMAIN);
		$activation = md5($user_id).md5($login);
		$activation_link = NICE_LINKS ? "<a href=\"".SITE_DOMAIN.UCMS_DIR."/activation/$login/$activation\">ссылке</a>" : "<a href=\"".SITE_DOMAIN.UCMS_DIR."/?action=activation&amp;login=$login&amp;code=$activation\">ссылке</a>";
		$headers = "Content-type:text/html; charset=utf-8\r\n";
		$subject = "Подтверждение регистрации";
		$message = "Здравствуйте, пользователь! <br>
			Спасибо за регистрацию на ".SITE_NAME."!
			<br><br><b>Ваш логин:</b> $login
			<br><b>Ваш пароль вы знаете сами.</b><br>
			<br>Перейдите по $activation_link, чтобы активировать ваш аккаунт. 
			<br> <br> С уважением, Администрация ".SITE_NAME.".";
		$headers .= 'From: '.'=?UTF-8?B?'.base64_encode(SITE_NAME).'?='.' <ucms@'.$domain.'>'."\r\n";
		$sent = mail($email, '=?UTF-8?B?'.base64_encode($subject).'?=', $message, $headers);
		$date = $ucms->get_date();
		if(NEW_USER_EMAIL != ''){
			$a_headers = "Content-type:text/html; charset=utf-8\r\n";
			$a_subject = "Был зарегистрирован пользователь $login";
			$a_message = "Здравствуйте, Администратор! <br>
				На вашем сайте, $date, зарегистрировался пользователь.
				<br><br><b>Логин:</b> $login
				<br><br><b>E-mail:</b> $email
				<br>Перейдите по <a href='".SITE_DOMAIN.UCMS_DIR."/admin/users.php'>ссылке</a>, чтобы активировать пользователя.";
			$a_headers .= 'From: '.'=?UTF-8?B?'.base64_encode(SITE_NAME).'?='.' <ucms@'.$domain.'>'."\r\n";
			$a_sent = mail($user->get_user_email(1), '=?UTF-8?B?'.base64_encode($a_subject).'?=', $a_message, $a_headers);
		}
		if($sent) return true;
		else return false;
		
	}

	function user_error($err_code){
		$err_array = array(
			1 => "- Вы ввели не всю информацию, пожалуйста заполните все поля.<br>",
			2 => "- Неправильно введен е-mail.<br>",
			3 => "- Неправильно введен код с картинки.<br>",
			4 => "- Произошла ошибка. Вы не зарегистрированы.<br>",
			5 => "- Логин должен содержать не менее 4-х символов и не более 16.<br>",
			6 => "- Пароль должен содержать не менее 6-х символов и не более 20.<br>",
			7 => "- Извините, введённый вами логин уже зарегистрирован.<br>",
			8 => "- Аватар должен быть в формате <strong>JPG, GIF или PNG</strong>.<br>",
			9 => "- Извините, введённый вами е-mail уже зарегистрирован.<br>",
			10 => "Извините, но регистрация на сайте ограничена.<br>");
		echo $err_array[$err_code];
	}

	function registration_form(){
		$from = NICE_LINKS ? UCMS_DIR."/registration" : UCMS_DIR."/?action=registration";
		if(!ALLOW_REGISTRATION){
			echo '<div class="error">';
			$this->user_error(10);
			echo '</div>';
			return false;
		}
		?>
		<form action="<?php echo $from; ?>" method="post" style="margin: 0 auto;" enctype="multipart/form-data">
			<input type="hidden" name="action" value="registration">
		<table style="margin: 0 auto;" class="registration">
			<tr>
				<td><label><b>Ваш логин: </b><span style="color:#ff0000;">*</span></label></td>
				<td><input name="login" type="text" size="15" maxlength="15" required <?php if(isset($_POST['login'])) echo 'value='.htmlspecialchars($_POST['login'])?> ></td>
			</tr> 
			<tr>

				<td><label><b>Ваш пароль: </b><span style="color:#ff0000;">*</span></label></td>
				<td><input name="password" type="password" size="15" maxlength="15" required></td>
			</tr>
			<tr>
				<td><label><b>Ваш E-mail: </b><span style="color:#ff0000;">*</span></label></td> 
				<td><input name="email" type="email" size="15" maxlength="100" required  <?php if(isset($_POST['email'])) echo 'value='.htmlspecialchars($_POST['email'])?> ></td>
			</tr>
			<?php if(USER_AVATARS){ ?><tr>
			<td><label><b>Ваш аватар: </b></label></td> 
			<td><input type="file" name="avatar"></td>
			</tr>
			<?php } ?>
			<?php if(USE_CAPTCHA > 0){ ?>
			<tr>
				<td><label><b>Введите код с картинки: </b><span style="color:#ff0000;">*</span></label></td> 
				<td><img src="<?php echo UCMS_DIR; ?>/sys/users/code/capcha-img.php" alt=""></td>		
			</tr>
			<tr>
				<td></td>
				<td><input type="text" name="code" required></td>
			</tr>
			<?php } ?>
		</table>
		<br>
		<div style="text-align: center;">
			<input type="submit" name="submit" value="Зарегистрироваться" class="ubutton">
		</div>
		</form>
		<?php
	}

	function registration_test(){
		if(isset($_POST['login']) and isset($_POST['password']) and isset($_POST['email']) and (isset($_POST['code']) or USE_CAPTCHA == 0)){
			echo '<div class="error">';
			echo '<b>Во время регистрации произошли следущие ошибки:</b><br><br>';
			$code = USE_CAPTCHA > 0 ? $_POST['code'] : '';
			echo $this->register_user($_POST['login'], $_POST['password'], $code, $_POST['email']);
			echo '</div><br>';
		}else 
		if(isset($_SESSION['register'])){
			echo '<div class="success">';
			echo 'Вы успешно зарегистрированы. На ваш е-mail было выслано сообщение с подтверждением регистрации.<br>';
			echo '</div><br>';
			unset($_SESSION['register']);
		}

	}
}
?>