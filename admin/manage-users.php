<?php	
	function delete_user($id){
		global $user, $udb;
		if($user->get_user_id() == $id){
			$accessLVL = 3;
		}elseif($user->get_user_group($id) == 1){
			$accessLVL = 7;
		}else{
			$accessLVL = 5;
		}
		if($user->has_access(4, $accessLVL)){
			if(!$id)
				return false;
			else{
				$id = (int) $id;
				if($id > 1){
					$avatar = $udb->get_row("SELECT `avatar` FROM `".UC_PREFIX."users` WHERE `id` = '$id'");
					if($avatar['avatar'] != '' and $avatar['avatar'] != 'no-avatar.jpg'){
						$avatar = substr($avatar['avatar'], 1);
						unlink($avatar);
					}
					$udb->query("DELETE FROM `".UC_PREFIX."users` WHERE `id` = '$id'");
					$udb->query("DELETE FROM `".UC_PREFIX."usersinfo` WHERE `userid` = '$id'");
					header("Location: ".UCMS_DIR."/admin/users.php");
					$_SESSION['success_del'] = true;
				}else header("Location: ".UCMS_DIR."/admin/users.php");
			}
		}
	}

	function activate_user($id){
		global $user, $udb;
		if($user->has_access(4, 4)){
			if(!$id)
				return false;
			else{
				$id = (int) $id;
				$udb->query("UPDATE `".UC_PREFIX."users` SET `activation` = 1 WHERE `id` = '$id'");
				header("Location: ".UCMS_DIR."/admin/users.php");
				$_SESSION['success_act'] = true;
			}
		}
	}

	function add_user_form(){
		global $months, $udb, $user;
		$groups = $udb->get_rows("SELECT `id`, `name` FROM `".UC_PREFIX."groups` WHERE `id` >= '".$user->get_user_group()."' ORDER BY `id` ASC");
		?>
		<h3>Добавление пользователя</h3>
		<form action="users.php" method="post" enctype="multipart/form-data" class="forms" style="width: 100%">
			<input name="add" type="hidden">	
			<br><br><label><b>Логин:</b></label><br>
			<input name="login" type="text" required>	
			<br><br><label><b>Пароль:</b></label><br>
			<input name="password" id="password" type="password" required>	
			<br><br><label><b>Email:</b></label><br>
			<input name="email" id="email" type="email" required>
			<br><br><label><b>Группа:</b></label><br>
			<select name="group">
				<?php
					for($i = 0; $i < count($groups); $i++){
						echo '<option value="'.$groups[$i]['id'].'">'.$groups[$i]['name'].'</option>';
					}
				?>
			</select>
			<?php if(USER_AVATARS){ ?>
			<br><br><label><b>Аватар:</b></label><br>
			<input name="avatar" id="avatar" type="file">	
			<?php } ?>
			<br><br><label><b>Фамилия:</b></label><br>
			<input name="surname" id="surname" type="text">
			<br><br><label><b>Имя:</label><br>
			<input name="name" id="name" type="text">	
			<br><br><label><b>ICQ:</label><br>
			<input name="icq" id="icq" type="text">	
			<br><br><label><b>Skype:</label><br>
			<input name="skype" id="skype" type="text">	
			<br><br><label><b>Уведомлять о ЛС по email:</b></label>
			<input name="pm-alert" id="pm-alert" value="1" type="checkbox">	
			<br><br><label><b>Дата рождения:</b></label><br>
			<select name="day" style="width:100px;">
				<?php
				echo "<option value=".date('d').">".date('d')."</option>";
				for ($i = 1; $i <= 31; $i++) {
					$d = $i < 10 ? "0$i" : $i;
					echo "<option value=\"$d\">$d</option>";
				}
				?>
			</select>
			<select name="month" style="width:100px;">
				<?php
				
				echo "<option value=".date('m').">".$months[date('m')]."</option>";
				for ($i = 1; $i <= 12; $i++) {
					$m = $i < 10 ? "0$i" : $i;
					echo "<option value=\"$m\">$months[$m]</option>";
				}
				?>
			</select>
			<select name="year" style="width:100px;">
				<?php
				echo "<option value=".date('Y').">".date('Y')."</option>";
				for ($i = date('Y'); $i >= 1900; $i--) {
					echo "<option value=\"$i\">$i</option>";
				}
				?>
			</select>	
			<br><br><label><b>О себе:</b></label><br>
			<textarea name="addinfo" rows="10" cols="45"></textarea><br><br>
			<input class="ucms-button-submit" type="submit" value="добавить">
		</form>
		<?php
	}

	function update_user_form($id){
		global $user, $udb, $months;
		if(!$id){
			return false;	
		}else{
			$id = (int) $id;
			$data = $udb->get_row("SELECT * FROM `".UC_PREFIX."users` WHERE `id` = '$id' LIMIT 1");
			$add_data = $udb->get_row("SELECT * FROM `".UC_PREFIX."usersinfo` WHERE `userid` = '$id' LIMIT 1");
			$groups = $udb->get_rows("SELECT `id`, `name` FROM `".UC_PREFIX."groups` WHERE `id` >= '".$user->get_user_group()."' ORDER BY `id` ASC");
			if(!empty($data['id']) and !empty($add_data['id'])){
				if($data['id'] == $user->get_user_id()){
					$accessLVL = 2;
				}elseif($user->get_user_group($data['id']) == 1){
					$accessLVL = 6;
				}else{
					$accessLVL = 4;
				}

				if($user->has_access(4, $accessLVL)){ 
					?>
					<form action="users.php" method="post" enctype="multipart/form-data" class="forms" style="width: 100%">
						<input name="update" type="hidden">
						<input name="id" type="hidden" value="<?php echo $data['id']; ?>">		
						<br><br><label><b>Изменить логин:</b></label><br>
						<input name="login" type="text" value="<?php echo $data['login']; ?>">	
						<br><br><label><b>Изменить пароль:</b></label><br>
						<input name="password" id="password" type="password">	
						<br><br><label><b>Изменить email:</b></label><br>
						<input name="email" id="email" type="email" value="<?php echo $data['email']; ?>">	
						<?php if($data['id'] > 1){ ?>
						<br><br><label><b>Группа:</b></label><br>
						<select name="group">
							<?php
								for($i = 0; $i < count($groups); $i++){
									echo '<option value="'.$groups[$i]['id'].'" '.($groups[$i]['id'] == $data['group'] ? "selected" : "").'>'.$groups[$i]['name'].'</option>';
								}
							?>
						</select>
						<?php } if(USER_AVATARS){ ?>
						<br><br><label><b>Изменить аватар:</b></label><br>
						<input name="avatar" id="avatar" type="file">	
						<?php } ?>
						<br><br><label><b>Фамилия:</b></label><br>
						<input name="surname" id="surname" type="text" value="<?php echo $add_data['surname'] ?>">
						<br><br><label><b>Имя:</label><br>
						<input name="name" id="name" type="text" value="<?php echo $add_data['firstname'] ?>">	
						<br><br><label><b>ICQ:</label><br>
						<input name="icq" id="icq" type="text" value="<?php echo $add_data['icq'] ?>">	
						<br><br><label><b>Skype:</label><br>
						<input name="skype" id="skype" type="text" value="<?php echo $add_data['skype'] ?>">	
						<?php
							$birthdate = explode('-', $add_data['birthdate']);
						?>
						<br><br><label><b>Уведомлять о ЛС по email:</b></label>
						<input name="pm-alert" id="pm-alert" type="checkbox" value="1" <?php if($add_data['pm-alert'] == 1) echo "checked"; ?>>	
						<br><br><label><b>Дата рождения:</b></label><br>
						<select name="day" style="width:100px;">
							<?php
							if(empty($birthdate[2]))
								echo "<option value=".date('d').">".date('d')."</option>";
							else
								echo "<option value=".$birthdate[2].">".$birthdate[2]."</option>";
							for ($i = 31; $i >= 1; $i--) {
								$d = $i < 10 ? "0$i" : $i;
								echo "<option value=\"$d\">$d</option>";
							}
							?>
						</select>
						<select name="month" style="width:100px;">
							<?php
							if(empty($birthdate[1]))
								echo "<option value=".date('m').">".$months[date('m')]."</option>";
							else
								echo "<option value=".$birthdate[1].">".$months[$birthdate[1]]."</option>";
							for ($i = 12; $i >= 1; $i--) {
								$m = $i < 10 ? "0$i" : $i;
								echo "<option value=\"$m\">$months[$m]</option>";
							}
							?>
						</select>
						<select name="year" style="width:100px;">
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
						<br><br><label><b>Что-нибудь о себе:</b></label><br>
						<textarea name="addinfo" rows="10" cols="45"><?php echo $add_data['addinfo'] ?></textarea><br><br>
						<input class="ucms-button-submit" type="submit" value="изменить">
					</form>
				<?php
				}
			}else{
				header("Location: users.php");
			}
		}

	}
	function user_errors($err_code){
		$err_array = array(
			1 => "- Вы ввели не всю информацию, пожалуйста заполните все поля!<br>",
			2 => "- Неправильно введен е-mail.<br>",
			3 => "- Неправильно введен код с картинки.<br>",
			4 => "- Ошибка! Вы не зарегистрированы.<br>",
			5 => "- Логин должен содержать не менее 4-х символов и не более 16.<br>",
			6 => "- Пароль должен содержать не менее 6-х символов и не более 20.<br>",
			7 => "- Извините, введённый вами логин уже зарегистрирован.<br>",
			8 => "- Аватар должен быть в формате <strong>JPG, GIF или PNG</strong>.<br>",
			9 => "- Извините, введённый вами е-mail уже зарегистрирован.<br>");
		echo $err_array[$err_code];
	}

	function check_login($login, $user_id = ''){
		global $udb, $user;
		$login = $udb->parse_value(stripslashes($login));
		$login = htmlspecialchars($login);
		$login = trim($login);
		$reg = "/[^(\w)|(\x7F-\xFF)|(\s)]/"; 
		$login = preg_replace($reg,'',$login);
		
		if (mb_strlen($login, "UTF-8") < 4 or mb_strlen($login, "UTF-8") > 16){
			user_errors(5);
			return false;
		}
		if(isset($user_id) and $user_id != ''){
			$ologin = $user->get_user_login($user_id);
		}else $ologin = '';
		$is_login = $udb->get_row("SELECT `id` FROM `".UC_PREFIX."users` WHERE `login` = '$login' LIMIT 1");
		if (!empty($is_login['id']) and $login != $ologin) {
			user_errors(7);
			return false;
		}
		return $login;
	}

	function check_password($password){
		global $udb, $user;
		$password = $udb->parse_value(stripslashes($password));
		$password = htmlspecialchars(trim($password));
		if (strlen($password) < 6 or strlen($password) > 20){
			user_errors(6);
			return false;
		}
		$password = $user->crypt_password($password);
		return $password;
	}


	function add_user($p){
		global $udb, $user, $ucms;
		$login = $p['login'];
		$password = $p['password'];
		$email = $udb->parse_value($p['email']);
		$group = (int) $p['group'];
		if($group <= 0) $group = DEFAULT_GROUP;
		$surname = $udb->parse_value($p['surname']);
		$firstname = $udb->parse_value($p['name']);
		$icq = (int) $p['icq'];
		$skype = $udb->parse_value($p['skype']);
		$birthdate = $udb->parse_value($p['year'].$p['month'].$p['day']);
		$addinfo = $udb->parse_value($p['addinfo']);
		$addinfo = strip_tags($addinfo, '<p><a><pre><img><br><b><em><i><strike>');
		if(isset($p['pm-alert']) and $p['pm-alert'] == 1)
			$pm_alert = 1;
		else $pm_alert = 0;
		if (empty($login) or empty($password) or empty($email)) {
			user_errors(1);
			$error = true;
		}		
		if(!check_login($login))
			$error = true;
		else
			$login = check_login($login);
		
		if(!check_password($password))
			$error = true;
		else
			$password = check_password($password);
		
		if (!preg_match("/@/i", $email)) {
			user_errors(2);
			$error = true;
		}else{
			$test = $udb->get_row("SELECT `id` FROM `".UC_PREFIX."users` WHERE `email` = '$email'");
			if(!empty($test['id']) and UNIQUE_EMAILS){
				user_errors(9);
				$error = true;
			}
		}
	
		if(!$error){
			if(USER_AVATARS)
				$avatar = set_user_avatar($login);
			else $avatar = '';
			$ip = $user->get_user_ip();
			$result = $udb->query("INSERT INTO `".UC_PREFIX."users` VALUES(NULL,'$login','$password', '$group','$avatar','$email', 1, NOW(), '', '$ip', '$ip', '0', NOW())");
			if ($result){
				$user = $udb->get_row("SELECT `id` FROM `".UC_PREFIX."users` WHERE `login` = '$login' LIMIT 1");
				$user_id = $user['id'];
				$user_meta = $udb->query("INSERT INTO `".UC_PREFIX."usersinfo` VALUES(NULL, '$user_id', '$firstname', '$surname', '$icq', '$skype', '$addinfo', '$birthdate', '$pm_alert')");
				if(!$user){
					user_errors(4);
					return false;
				}
				header("Location: ".UCMS_DIR."/admin/users.php");
				$_SESSION['success_add'] = true;
			}
			else{
				user_errors(4);
				return false;
			}
		}else{
			return false;
		}	

	}

	function update_user($p){
		global $udb, $user, $ucms;
		$user_id = (int) $p['id'];
		if(isset($p['login']) and $p['login'] != ''){
			$login = check_login($p['login'], $user_id);
			if($login != ''){
				
				$udb->query("UPDATE `".UC_PREFIX."users` SET `login` = '$login' WHERE `id` = '$user_id'");
			}elseif(!$login) $error = true;
		}
		if(isset($p['password']) and $p['password'] != ''){
			$password = check_password($p['password']);
			if($password != ''){
				
				$udb->query("UPDATE `".UC_PREFIX."users` SET `password` = '$password' WHERE `id` = '$user_id'");
			}
		}
		if(isset($p['group']) and $p['group'] != ''){
			$group = (int) $p['group'];
			if($group <= 0) $group = DEFAULT_GROUP;
			if($group != ''){
				
				$udb->query("UPDATE `".UC_PREFIX."users` SET `group` = '$group' WHERE `id` = '$user_id'");
			}
		}
		if(isset($p['email']) and $p['email'] != ''){
			$email = $udb->parse_value(trim(htmlspecialchars($p['email'])));
			if(!preg_match("/[0-9a-z_]+@[0-9a-z_^\.]+\.[a-z]{2,3}/i", $email)){
				user_errors(2);
				$error = true;
				
			}else{
				if(UNIQUE_EMAILS){
					$test = $udb->get_row("SELECT `id` FROM `".UC_PREFIX."users` WHERE `email` = '$email'");
					if(!empty($test['id']) and $test['id'] != $user_id){
						user_errors(9);
						$error = true;
					}else{
						
						$udb->query("UPDATE `".UC_PREFIX."users` SET `email` = '$email' WHERE `id` = '$user_id'");
					}
				}else{
					
					$udb->query("UPDATE `".UC_PREFIX."users` SET `email` = '$email' WHERE `id` = '$user_id'");
				}
			}
		}
		if(isset($_FILES['avatar']['name']) and $_FILES['avatar']['name'] != ''){
			if(!isset($login)) $login = $user->get_user_login($user_id);
			$avatar = set_user_avatar($login);
			
			$udb->query("UPDATE `".UC_PREFIX."users` SET `avatar` = '$avatar' WHERE `id` = '$user_id'");
		}

		if(isset($p['name']) and $p['name'] != ''){
			$name = $udb->parse_value((htmlspecialchars(trim($p['name']))));
			
			$udb->query("UPDATE `".UC_PREFIX."usersinfo` SET `firstname` = '$name' WHERE `id` = '$user_id'");	
		}

		if(isset($p['surname']) and $p['surname'] != ''){
			$surname = $udb->parse_value((htmlspecialchars(trim($p['surname']))));
			
			$udb->query("UPDATE `".UC_PREFIX."usersinfo` SET `surname` = '$surname' WHERE `id` = '$user_id'");	
		}

		if(isset($p['icq']) and $p['icq'] != ''){
			$icq = (int) $p['icq'];
			$udb->query("UPDATE `".UC_PREFIX."usersinfo` SET `icq` = '$icq' WHERE `id` = '$user_id'");	
		}

		if(isset($p['skype']) and $p['skype'] != ''){
			$skype = $udb->parse_value($p['skype']);
			$udb->query("UPDATE `".UC_PREFIX."usersinfo` SET `skype` = '$skype' WHERE `id` = '$user_id'");	
		}

		if(isset($p['day']) and $p['day'] != '' and isset($p['month']) and $p['month'] != '' and isset($p['year']) and $p['year'] != ''){
			$day = (int) $p['day'];
			$month = (int) $p['month'];
			$year = (int) $p['year'];
			$birthdate = "$year-$month-$day";
			$udb->query("UPDATE `".UC_PREFIX."usersinfo` SET `birthdate` = '$birthdate' WHERE `id` = '$user_id'");	
		}

		if(isset($p['addinfo']) and $p['addinfo'] != ''){
			$addinfo = $udb->parse_value($p['addinfo']);
			$addinfo = strip_tags($addinfo, '<p><a><pre><img><br><b><em><i><strike>');
			$udb->query("UPDATE `".UC_PREFIX."usersinfo` SET `addinfo` = '$addinfo' WHERE `id` = '$user_id'");	
		}

		if(isset($p['pm-alert']) and $p['pm-alert'] == 1){
			$udb->query("UPDATE `".UC_PREFIX."usersinfo` SET `pm-alert` = '1' WHERE `id` = '$user_id'");	
		}else{
			$udb->query("UPDATE `".UC_PREFIX."usersinfo` SET `pm-alert` = '0' WHERE `id` = '$user_id'");	
		}

		if(!$error) {
			$udb->query("UPDATE `".UC_PREFIX."stats` SET `value` = '$user_id', `update` = NOW() WHERE `id` = '2'");
			header("Location: ".UCMS_DIR."/admin/users.php");
			$_SESSION['success_upd'] = true;
		}
	}

	function manage_users(){
		global $udb, $ucms, $user;
		if (isset($_POST['item']) and isset($_POST['actions'])){
			$items = array();
			$action = (int) $_POST['actions'];
			foreach ($_POST['item'] as $id) {
				$id = (int) $id;
				$page = $udb->get_row("SELECT `group`, `id` FROM `".UC_PREFIX."users` WHERE `id` = '$id' LIMIT 1");
				if($page){
					if(!empty($page['group'])){
						if($page['group'] == 1){
							$accessLVL = 6;
						}else{
							$accessLVL = 4;
						}
					}
				}
				if($action == 3) $accessLVL++;
				if ( $user->has_access(4, $accessLVL) and ($id != $user->get_user_id()) and ($id > 1) ) {
					$items[] = $id;
				}
			}
			$ids = implode(',', $items);
			if (count($items) > 0) {
				switch ($action) {
					case 1:
						$upd = $udb->query("UPDATE `".UC_PREFIX."users` SET `activation` = '1' WHERE `id` IN ($ids)");
						if (count($items) > 1) {
							$_SESSION['success_actm'] = true;
						}else 
							$_SESSION['success_act'] = true;
 						header("Location: ".UCMS_DIR."/admin/users.php");
					break;
	
					case 2:
						$upd = $udb->query("UPDATE `".UC_PREFIX."users` SET `activation` = '0' WHERE `id` IN ($ids)");
						if (count($items) > 1) {
							$_SESSION['success_actm'] = false;
						}else 
							$_SESSION['success_act'] = false;
 						header("Location: ".UCMS_DIR."/admin/users.php");
					break;
	
					case 3:
						$del = $udb->query("DELETE FROM `".UC_PREFIX."users` WHERE `id` IN ($ids)");
						if (count($items) > 1) {
							$_SESSION['success_delm'] = true;
						}else 
							$_SESSION['success_del'] = true;
 						header("Location: ".UCMS_DIR."/admin/users.php");
					break;
					
				}
			}
		}
		if(isset($_POST['search']) and $_POST['search'] != ''){
			$search = $udb->parse_value($_POST['search']);
			echo "<br>Поиск пользователя $search<br>";
			$swhere = "WHERE `login` = '$search' or `email` = '$search'";
		}else $swhere = '';
		$call = $udb->num_rows("SELECT * FROM `".UC_PREFIX."users`");
		$cactivated = $udb->num_rows("SELECT * FROM `".UC_PREFIX."users` WHERE `activation` > 0");
		$cdeactivated = $udb->num_rows("SELECT * FROM `".UC_PREFIX."users` WHERE `activation` = 0");
		$columns = array('login','email', 'group', 'date', 'lastlogin');
		$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
		$orderby = isset($_GET['orderby']) ? in_array($_GET['orderby'], $columns) ? $_GET['orderby'] : 'id' : 'id';
		$order = (isset($_GET['order']) and $_GET['order'] == 'DESC') ? 'ASC' : 'DESC';
		$status = isset($_GET['status']) ? $_GET['status'] : "";
		switch ($status) {
			case 'activated':
				$swhere = "WHERE `activation` = 1";
				break;

			case 'deactivated':
				$swhere = "WHERE `activation` = 0";
				break;

			default:
				$swhere = "";
			break;
		}
		$count = $udb->num_rows("SELECT * FROM `".UC_PREFIX."users` $swhere ORDER BY `$orderby` $order");
		$perpage = 25;
		$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
		if($page <= 0) $page = 1;
		$pages_count = 0;
		if($count != 0){ 
			$pages_count = ceil($count / $perpage); 
			if ($page > $pages_count):
				$page = $pages_count;
			endif; 
			$start_pos = ($page - 1) * $perpage;
			$users = $udb->get_rows("SELECT * FROM `".UC_PREFIX."users` $swhere ORDER BY `$orderby` $order LIMIT $start_pos, $perpage");
		}

		$lall = $status != '' ? "<a href=\"".UCMS_DIR."/admin/users.php".(isset($_GET['orderby']) ? "?orderby=".$_GET['orderby'] : "").(isset($_GET['order']) ? "&amp;order=".$_GET['order'] : "").(isset($_GET['page']) ? ((isset($_GET['orderby']) and isset($_GET['order'])) ? "&amp;" : "?")."page=".$_GET['page'] : "")."\">Все</a>" : "<b>Все</b>"; 
		$lactivated = $status != 'activated' ? "<a href=\"".UCMS_DIR."/admin/users.php?status=activated".(isset($_GET['orderby']) ? "&amp;orderby=".$_GET['orderby'] : "").(isset($_GET['order']) ? "&amp;order=".$_GET['order'] : "").(isset($_GET['page']) ? "&amp;page=".$_GET['page'] : "")."\">Активированные</a>" : "<b>Активированные</b>"; 
		$ldeactivated =  $status != 'deactivated' ? "<a href=\"".UCMS_DIR."/admin/users.php?status=deactivated".(isset($_GET['orderby']) ? "&amp;orderby=".$_GET['orderby'] : "").(isset($_GET['order']) ? "&amp;order=".$_GET['order'] : "").(isset($_GET['page']) ? "&amp;page=".$_GET['page'] : "")."\">Неактивированные</a>" : "<b>Неактивированные</b>"; 
		?>
		<?php if($user->has_access(4, 4)){ ?>
		<br>
		Показывать: <?php echo $lall." ($call)"; ?> | <?php echo $lactivated." ($cactivated)"; ?> | <?php echo $ldeactivated." ($cdeactivated)"; ?>
		<br><br>
		<form action="users.php" method="post" style="float: right">
			<input type="text" style="width: 250px" name="search" placeholder="логин или e-mail">
			<input type="submit" value="Искать" style="width: 100px" class="ucms-button-submit">
		</form>
		<form action="users.php" method="post">
		<select name="actions" style="width: 250px;">
			<option>Отмеченные</option>
			<option value="1">Активировать</option>
			<option value="2">Деактивировать</option>
			<option value="3">Удалить</option>
		</select>
		<input type="submit" value="Применить" class="ucms-button-submit">
		<br>
		<?php } ?>
		<?php
		if($pages_count > 1){
			echo "<br>";
			pages($page, $count, $pages_count, 15, false);
			echo '<br>';
		}?><br>
		<table class="manage"><?php
		$link1 = UCMS_DIR."/admin/users.php?orderby=login&amp;order=".$order.(isset($_GET['page']) ? "&amp;page=".$_GET['page'] : "");
		$link2 = UCMS_DIR."/admin/users.php?orderby=email&amp;order=".$order.(isset($_GET['page']) ? "&amp;page=".$_GET['page'] : "");
		$link3 = UCMS_DIR."/admin/users.php?orderby=group&amp;order=".$order.(isset($_GET['page']) ? "&amp;page=".$_GET['page'] : "");
		$link4 = UCMS_DIR."/admin/users.php?orderby=date&amp;order=".$order.(isset($_GET['page']) ? "&amp;page=".$_GET['page'] : "");
		$link5 = UCMS_DIR."/admin/users.php?orderby=lastlogin&amp;order=".$order.(isset($_GET['page']) ? "&amp;page=".$_GET['page'] : "");
		$mark = $order == "ASC" ? '↑' : '↓';
		?>
			<tr>
				<th style="width: 15px;"><input type="checkbox" name="select-all" value="1"></th>
				<?php if(USER_AVATARS){?><th>Аватар</th><?php } ?>
				<th><a href="<?php echo $link1; ?>">Логин<?php echo $mark; ?></a></th>
				<?php if($user->has_access(4, 4)) { ?><th><a href="<?php echo $link2; ?>">E-mail<?php echo $mark; ?></a></th><?php } ?>
				<?php if($user->has_access(4, 4)) { ?><th>IP</th><?php } ?>
				<th style="width: 80px"><a href="<?php echo $link3; ?>">Группа<?php echo $mark; ?></a></th>
				<th><a href="<?php echo $link4; ?>">Дата регистрации<?php echo $mark; ?></a></th>
				<?php if($user->has_access(4, 4)) { ?><th><a href="<?php echo $link5; ?>">Дата последнего входа<?php echo $mark; ?></a></th><?php } ?>
				<th style="width: 200px">Управление</th>
			</tr>
			<?php
			if($count != 0){
				for ($i = 0; $i < count($users); $i++) { 
					$groups[] = $udb->parse_value($users[$i]['group']);
				}
	
				$groups = implode("','", $groups);
				$groups = "'".$groups."'";
				$groups_meta = $udb->get_rows("SELECT `id`, `name` FROM `".UC_PREFIX."groups` WHERE `id` in ($groups)");

           	 	for($i = 0; $i < count($users); $i++){
           	 		for($j = 0; $j < count($groups_meta); $j++){
						if($users[$i]['group'] === $groups_meta[$j]['id']){
							$user_group_name = $groups_meta[$j]['name'];
							break;
						}
					}
					$user_page = NICE_LINKS ? UCMS_DIR."/user/".$users[$i]['login'] : UCMS_DIR."/?action=profile&amp;id=".$users[$i]['id'];
					if($user->get_user_id() == $users[$i]['id']){
						$accessLVL = 2;
					}elseif($users[$i]['group'] == 1){
						$accessLVL = 6;
					}else{
						$accessLVL = 4;
					}
					?>
					<tr>
						<td><input type="checkbox" name="item[]" value="<?php echo $users[$i]['id']; ?>"></td>
						<?php if(USER_AVATARS){?><td style="padding: 0px; padding-top: 3px; width: 32px; text-align: center;"><img src="<?php echo "../".AVATARS_PATH.$users[$i]['avatar'] ?>" width="32" height="32" alt=""></td><?php } ?>
						<td style="width: 45%;"><a target="_blank" href="<?=$user_page?>"><?php echo $users[$i]['login']; if($users[$i]['online'] == 1) echo ' (Онлайн)'; if($users[$i]['activation'] != 1) echo ' (Неактивирован)'; ?></a></td>
						<?php if($user->has_access(4, 4)) { ?><td><?php echo $users[$i]['email']; ?></td><?php } ?>
						<?php if($user->has_access(4, 4)) { ?><td><?php echo $users[$i]['regip']; ?></td><?php } ?>
						<td><?php echo $user_group_name; ?></td>
						<td><?php echo $ucms->format_date($users[$i]['date'])?></td>
						<?php if($user->has_access(4, 4)) { ?><td><?php echo $ucms->format_date($users[$i]['lastlogin'])?></td><?php } ?>
						<td><span class="actions"><?php if($user->has_access(4, 4)){ ?><?php if($users[$i]['activation'] != 1) echo '<a href="'.UCMS_DIR.'/admin/users.php?action=activate&amp;id='.$users[$i]['id'].'">Активировать</a> | '; ?><?php } ?><?php if($user->has_access(4, $accessLVL)){ ?><a href="<?php echo UCMS_DIR?>/admin/users.php?action=update&amp;id=<?=$users[$i]['id']?>">Изменить</a><?php } ?><?php if($user->has_access(4, $accessLVL+1) and $users[$i]['id'] > 1){ ?> | <a href="<?php echo UCMS_DIR?>/admin/users.php?action=delete&amp;id=<?=$users[$i]['id']?>">Удалить</a><?php } ?></span></td>
						
					</tr>
				<?php
           	 	}
        	}else{
        		$c = USER_AVATARS ? 9 : 8;
        		if(!$user->has_access(4, 4)) $c -= 3;
        		?>
				<td colspan="<?php echo $c; ?>" style="text-align:center;">Никого не найдено.</td>
        		<?php
        	}
		echo '</table>';
	}

	function set_user_avatar($login){
		global $udb;
		$tmp_name = isset($_FILES['avatar']['tmp_name']) ? $_FILES['avatar']['tmp_name'] : '';
		$avatar = isset($_FILES['avatar']['name']) ? $_FILES['avatar']['name'] : '';
		if (empty($avatar)){
			$avatar = "no-avatar.jpg";
			return $avatar;
		}else{
			$ava = $udb->get_row("SELECT `avatar` FROM `".UC_PREFIX."users` WHERE `login` = '$login'");
			if($ava['avatar'] != '' and $ava['avatar'] != 'no-avatar.jpg'){
				unlink(substr($ava['avatar'], 1));
			}
			$avatar_dir = '../content/avatars/';
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
					imagecopyresampled($dest, $im, 0, 0, 0, 0, AVATAR_WIDTH, AVATAR_HEIGHT, min($w_src, $h_src), min($w_src, $h_src)); 
				if ($w_src == $h_src)
					imagecopyresampled($dest, $im, 0, 0, 0, 0, AVATAR_WIDTH, AVATAR_HEIGHT, $w_src, $w_src); 
				imagejpeg($dest, $avatar_dir.mb_strtolower($login, "UTF-8").".jpg");
				$avatar = mb_strtolower($login, "UTF-8").".jpg";
				$delfull = $avatar_dir.$filename; 
				unlink ($delfull);
				return $avatar;
			}else{
				user_errors(8);
				$error = true;
			}
		}
	}	
?>