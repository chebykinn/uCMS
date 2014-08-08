<?php
class edit extends uSers{
	function edit_main_form(){
		global $user;
		if($user->has_access(4)){
			$from = NICE_LINKS ? UCMS_DIR."/user/".$user->get_user_login() : UCMS_DIR."/?action=profile&amp;id=".$user->get_user_id();
			?>
			<form action="<?php echo $from; ?>" method="post" enctype="multipart/form-data">
				<table>
					<tr>
						<td style="padding:5px;">
							<label>Изменить логин:</label><br>
							<input name="login" type="text" value="<?php echo $user->get_user_login(); ?>">	
						</td>
						<td style="padding:5px;">
							<label>Изменить пароль:</label><br>
							<input name="password" id="password" type="password">	
						</td>
					</tr>
					<tr>
						<td style="padding:5px;">
							<label>Изменить email:</label><br>
							<input name="email" id="email" type="email" value="<?php echo $user->get_user_email(); ?>">	
						</td>
						<?php if(USER_AVATARS){ ?><td style="padding:5px;">
							<label>Изменить аватар:</label><br>
							<input name="avatar" id="avatar" type="file">	
						</td><?php } ?>
					</tr>
					<tr>
					<td>
						<input class="ubutton" type="submit" value="изменить">
					</td>
					</tr>
				</table>
				</form>
		<?php
		}else{
			echo '<div class="error">Вы не можете изменять свою страницу.</div>';
		}
	}

	function edit_add_form(){
		global $user, $months;
		if($user->has_access(4)){
			$from = NICE_LINKS ? UCMS_DIR."/user/".$user->get_user_login() : UCMS_DIR."/?action=profile&amp;id=".$user->get_user_id();
			?>
			<form action="<?php echo $from ?>" method="post">
				<table>
					<tr>
						<td style="padding:5px;">
							<label>Фамилия:</label><br>
							<input name="surname" id="surname" type="text" value="<?php echo $user->get_user_surname() ?>">
						</td>
						<td style="padding:5px;">
							<label>Имя:</label><br>
							<input name="name" id="name" type="text" value="<?php echo $user->get_user_firstname() ?>">	
						</td>
					</tr>
					<tr>
						<td style="padding:5px;">
							<label>ICQ:</label><br>
							<input name="icq" id="icq" type="text" value="<?php echo $user->get_user_icq() ?>">	
						</td>
						<td style="padding:5px;">
							<label>Skype:</label><br>
							<input name="skype" id="skype" type="text" value="<?php echo $user->get_user_skype() ?>">	
						</td>
					</tr>
					<tr>
						<td style="padding:5px; vertical-align: top;">
							<?php
								$birthdate = explode('-', $user->get_user_birthdate());
							?>
							<label>Дата рождения:</label><br>
							<select name="day">
								<?php
								if(empty($birthdate[2]))
									echo "<option value=".date('d').">".date('d')."</option>";
								else
									echo "<option value=".$birthdate[2].">".$birthdate[2]."</option>";
								for ( $i = 31; $i >= 1; $i-- ) {
									$d = $i < 10 ? "0$i" : $i;
									echo "<option value=\"$d\">$d</option>";
								}
								?>
							</select>
							<select name="month">
								<?php
								if(empty($birthdate[1]))
									echo "<option value=".date('m').">".$months[date('m')]."</option>";
								else
									echo "<option value=".$birthdate[1].">".$months[$birthdate[1]]."</option>";
								for ( $i = 12; $i >= 1; $i-- ) {
									$m = $i < 10 ? "0$i" : $i;
									echo "<option value=\"$m\">$months[$m]</option>";
								}
								?>
							</select>
							<select name="year">
								<?php
								if(empty($birthdate[0]))
									echo "<option value=".date('Y').">".date('Y')."</option>";
								else
									echo "<option value=".$birthdate[0].">".$birthdate[0]."</option>";
								for ( $i = date('Y'); $i >= 1900; $i-- ) {
									echo "<option value=\"$i\">$i</option>";
								}
								?>
							</select>
						</td>
						
						<td style="padding:5px;">
							<label>Уведомлять о ЛС по email: </label>
							<input type="checkbox" name="pm-alert" value="1" <?php if($user->get_user_pm_subscription()) echo "checked"; ?>>
						</td>
					</tr>
					<tr>
						<td colspan="2" style="padding:5px;">
							<label>Что-нибудь о себе:</label><br>
							<textarea name="addinfo" rows="10" cols="45"><?php echo $user->get_user_addinfo() ?></textarea>
						</td>
					</tr>
					<tr>
					<td>
						<input class="ubutton" type="submit" value="изменить">
					</td>
					</tr>
				</table>
				</form>
			<?php
		}
	}

	function edit_main_data(){
		global $udb, $user, $ucms;
		$from = NICE_LINKS ? UCMS_DIR."/user/".$user->get_user_login() : UCMS_DIR."/?action=profile&id=".$user->get_user_id();
		$error = false;
		$user_id = $user->get_user_id();
		$login = $user->get_user_login();
		if(isset($_POST['login']) and $_POST['login'] != ''){
			$login = $this->check_login($_POST['login']);
			if($login != ''){
				$udb->query("UPDATE `".UC_PREFIX."users` SET `login` = '$login' WHERE `id` = '$user_id'");
				$_SESSION['login'] = $login;
				$from = NICE_LINKS ? UCMS_DIR."/user/".$login : UCMS_DIR."/?action=profile&id=".$user->get_user_id();
			}else
				$error = true;	
		}
		if(isset($_POST['password']) and $_POST['password'] != ''){
			$password = $this->check_password($_POST['password']);
			if($password != ''){
				$udb->query("UPDATE `".UC_PREFIX."users` SET `password` = '$password' WHERE `id` = '$user_id'");
				$_SESSION['password'] = $password;
			}else
				$error = true;
		}

		if(isset($_POST['email']) and $_POST['email'] != ''){
			$email = $udb->parse_value(trim(htmlspecialchars($_POST['email'])));
			if(!preg_match("/@/i", $email)){
				$this->user_error(2);
				$error = true;
				
			}else{
				if(UNIQUE_EMAILS){
					$test = $udb->get_row("SELECT `id` FROM `".UC_PREFIX."users` WHERE `email` = '$email'");
					if(!empty($test['id']) and $test['id'] != $user_id){
						$this->user_error(9);
						$error = true;
					}else
						$udb->query("UPDATE `".UC_PREFIX."users` SET `email` = '$email' WHERE `id` = '$user_id'");
				}else
					$udb->query("UPDATE `".UC_PREFIX."users` SET `email` = '$email' WHERE `id` = '$user_id'");
			}
		}
		if(isset($_FILES['avatar']['name']) and $_FILES['avatar']['name'] != ''){
			$avatar = $this->set_user_avatar();
			if(!$avatar){
				$this->user_error(8);
				$error = true;
			}
			$udb->query("UPDATE `".UC_PREFIX."users` SET `avatar` = '$avatar' WHERE `id` = '$user_id'");
		}
		if(!$error) {
			$udb->query("UPDATE `".UC_PREFIX."stats` SET `value` = '$user_id', `update` = NOW() WHERE `id` = '2'");
			header("Location: $from");
			$_SESSION['success_main'] = true;
		}
	}

	function edit_add_data(){
		global $user, $ucms, $udb;
		$from = NICE_LINKS ? UCMS_DIR."/user/".$user->get_user_login() : UCMS_DIR."/?action=profile&id=".$user->get_user_id();
		$error = false;
		$user_id = $user->get_user_id();
		$login = $user->get_user_login();
		if(isset($_POST['name']) and $_POST['name'] != ''){
			$name = $udb->parse_value((htmlspecialchars(trim($_POST['name']))));
			$udb->query("UPDATE `".UC_PREFIX."usersinfo` SET `firstname` = '$name' WHERE `id` = '$user_id'");	
		}
		if(isset($_POST['surname']) and $_POST['surname'] != ''){
			$surname = $udb->parse_value((htmlspecialchars(trim($_POST['surname']))));
			$udb->query("UPDATE `".UC_PREFIX."usersinfo` SET `surname` = '$surname' WHERE `id` = '$user_id'");	
		}
		if(isset($_POST['icq']) and $_POST['icq'] != ''){
			$icq = (int) $_POST['icq'];
			$udb->query("UPDATE `".UC_PREFIX."usersinfo` SET `icq` = '$icq' WHERE `id` = '$user_id'");	
		}
		if(isset($_POST['skype']) and $_POST['skype'] != ''){
			$skype = $udb->parse_value($_POST['skype']);
			$udb->query("UPDATE `".UC_PREFIX."usersinfo` SET `skype` = '$skype' WHERE `id` = '$user_id'");	
		}
		if(isset($_POST['day']) and $_POST['day'] != '' and isset($_POST['month']) and $_POST['month'] != '' and isset($_POST['year']) and $_POST['year'] != ''){
			$day = (int) $_POST['day'];
			$month = (int) $_POST['month'];
			$year = (int) $_POST['year'];
			$birthdate = "$year-$month-$day";
			$udb->query("UPDATE `".UC_PREFIX."usersinfo` SET `birthdate` = '$birthdate' WHERE `id` = '$user_id'");	
		}
		if(isset($_POST['pm-alert']) and $_POST['pm-alert'] == 1){
			$udb->query("UPDATE `".UC_PREFIX."usersinfo` SET `pm-alert` = '1' WHERE `id` = '$user_id'");	
		}else{
			$udb->query("UPDATE `".UC_PREFIX."usersinfo` SET `pm-alert` = '0' WHERE `id` = '$user_id'");	
		}
		if(isset($_POST['addinfo']) and $_POST['addinfo'] != ''){
			$addinfo = $udb->parse_value($_POST['addinfo']);
			$addinfo = strip_tags($addinfo, '<p><a><pre><img><br><b><em><i><strike>');
			$udb->query("UPDATE `".UC_PREFIX."usersinfo` SET `addinfo` = '$addinfo' WHERE `id` = '$user_id'");	
		}
		$udb->query("UPDATE `".UC_PREFIX."stats` SET `value` = '$user_id', `update` = NOW() WHERE `id` = '2'");
		header("Location: ".$ucms->get_back_url());
		$_SESSION['success_add'] = true;
	}

	function check_login($login){
		global $udb, $user;
		$login = $udb->parse_value(stripslashes($login));
		$login = htmlspecialchars($login);
		$login = trim($login);
		$reg = "/(<|>|'|%|#|!|-|@)$/i"; 
		$login = preg_replace($reg,'',$login);
		
		if (mb_strlen($login, "UTF-8") < 4 or mb_strlen($login, "UTF-8") > 16){
			$this->user_error(5);
			return false;
		}
		
		$is_login = $udb->get_row("SELECT `id` FROM `".UC_PREFIX."users` WHERE `login` = '$login' LIMIT 1");
		if (!empty($is_login['id']) and $is_login['id'] != $user->get_user_id()) {
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

	function user_error($err_code){
		$err_array = array(
			1 => "- Вы ввели не всю информацию, пожалуйста заполните все поля!<br>",
			2 => "- Неправильно введен е-mail.<br>",
			3 => "- Неправильно введен код с картинки.<br>",
			4 => "- Ошибка! Данные не изменены.<br>",
			5 => "- Логин должен содержать не менее 4-х символов и не более 16.<br>",
			6 => "- Пароль должен содержать не менее 6-х символов и не более 20.<br>",
			7 => "- Извините, введённый вами логин уже зарегистрирован.<br>",
			8 => "- Аватар должен быть в формате <strong>JPG, GIF или PNG</strong>.<br>",
			9 => "- Извините, введённый вами e-mail уже зарегистрирован.<br>",);
		echo $err_array[$err_code];
	}

	function set_user_avatar(){
		global $udb;
		$tmp_name = isset($_FILES['avatar']['tmp_name']) ? $_FILES['avatar']['tmp_name'] : '';
		$avatar = isset($_FILES['avatar']['name']) ? $_FILES['avatar']['name'] : '';
		if (empty($avatar)){
			$avatar = "no-avatar.jpg";
			return $avatar;
		}
		else{
			$id = $this->get_user_id();
			$ava = $udb->get_row("SELECT `avatar` FROM `".UC_PREFIX."users` WHERE `id` = '$id'");
			if($ava['avatar'] != '' and $ava['avatar'] != 'no-avatar.jpg'){
				unlink(substr($ava['avatar'], 1));
			}
			$avatar_dir = 'content/avatars/';
			if(preg_match('/[.](JPG)|(jpg)|(gif)|(GIF)|(png)|(PNG)$/', $_FILES['avatar']['name'])){	
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
					imagecopyresampled($dest, $im, 0, 0, 0, 0, AVATAR_WIDTH, AVATAR_HEIGHT, min($w_src, $h_src), min($w_src, $h_src)); 
				if ($w_src == $h_src)
					imagecopyresampled($dest, $im, 0, 0, 0, 0, AVATAR_WIDTH, AVATAR_HEIGHT, $w_src, $w_src); 
				imagejpeg($dest, $avatar_dir.mb_strtolower($this->get_user_login(), "UTF-8").".jpg");
				$avatar = mb_strtolower($this->get_user_login(), "UTF-8").".jpg";
				$delfull = $avatar_dir.$filename; 
				unlink ($delfull);
				return $avatar;
			}else{
				return false;
			}
		}
	}

	function header_messages(){
		if(isset($_POST['login']) or isset($_POST['password']) or isset($_POST['email'])){
			echo '<div class="error">';
			$this->edit_main_data();
			echo '</div>';
		}elseif(isset($_SESSION['success_main'])){
			echo '<div class="success">Ваши основные данные успешно изменены.</div>';
			unset($_SESSION['success_main']);
		}
		if(isset($_POST['surname']) or isset($_POST['name']) or isset($_POST['icq']) or isset($_POST['skype']) or (isset($_POST['day']) and isset($_POST['month']) and isset($_POST['year'])) or isset($_POST['addinfo'])){
			echo '<div class="error">';
			$this->edit_add_data();
			echo '</div>';
		}elseif(isset($_SESSION['success_add'])){
			echo '<div class="success">Ваши данные успешно изменены.</div>';
			unset($_SESSION['success_add']);
		}
	}
}
?>
