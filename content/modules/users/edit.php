<?php
/**
 *
 * uCMS Users Edit Class
 * @package Users Edit
 * @since uCMS 1.2
 * @version uCMS 1.3
 *
*/
class edit extends uSers{
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
	var $columns;
	var $values;

	/**
	 *
	 * Load main edit form
	 * @package uCMS
	 * @since uCMS 1.2
	 * @version uCMS 1.3
	 * @return nothing
	 *
	*/
	function edit_main_form(){
		global $ucms;
		if($this->has_access("users", 2)){
			$ucms->template(get_module("path", "users").'forms/edit-main.php');
		}else{
			echo '<div class="error">'.$ucms->cout("module.users.error.edit.no_permissions", true).'</div>';
		}
	}

	/**
	 *
	 * Load additional edit form
	 * @package uCMS
	 * @since uCMS 1.2
	 * @version uCMS 1.3
	 * @return nothing
	 *
	*/
	function edit_add_form(){
		global $uc_months, $profile_info, $ucms;
		if($this->has_access("users", 2)){
			$ucms->template(get_module("path", "users").'forms/edit-add.php', true, $profile_info);
		}
	}

	/**
	 *
	 * Apply changes of main data from form
	 * @package uCMS
	 * @since uCMS 1.2
	 * @version uCMS 1.3
	 * @return nothing
	 *
	*/
	function edit_main_data(){
		global $udb, $ucms, $user_id;
		$error = false;
		$login = $this->get_user_login($user_id);
		$from = NICE_LINKS ? UCMS_DIR."/user/$login/edit" : UCMS_DIR."/?action=profile&edit=".$user_id;
		if(isset($_POST['login']) and $_POST['login'] != ''){
			$login = $this->check_login($_POST['login'], $user_id);
			if($login != ''){
				$udb->query("UPDATE `".UC_PREFIX."users` SET `login` = '$login' WHERE `id` = '$user_id'");
				$_SESSION['login'] = $login;
				$from = NICE_LINKS ? UCMS_DIR."/user/$login/edit" : UCMS_DIR."/?action=profile&edit=".$user_id;
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
			$avatar = $this->set_user_avatar($login);
			if(!$avatar){
				$this->user_error(8);
				$error = true;
			}
			$udb->query("UPDATE `".UC_PREFIX."users` SET `avatar` = '$avatar' WHERE `id` = '$user_id'");
		}
		if(!$error) {
			header("Location: $from");
			$_SESSION['success_main'] = true;
		}
	}

	/**
	 *
	 * Apply changes of additional data from form
	 * @package uCMS
	 * @since uCMS 1.2
	 * @version uCMS 1.3
	 * @return nothing
	 *
	*/
	function edit_add_data(){
		global $ucms, $udb, $profile_info, $event, $user_id;
		$error = false;
		$login = $this->get_user_login($user_id);
		if(isset($_POST['name'])){
			$this->values[] = $udb->parse_value((htmlspecialchars(trim($_POST['name']))));
			$this->columns[] = 'firstname';
		}
		if(isset($_POST['surname'])){
			$this->values[] = $udb->parse_value((htmlspecialchars(trim($_POST['surname']))));
			$this->columns[] = 'surname';
		}
		if(isset($_POST['icq'])){
			$this->values[] = (int) $_POST['icq'];
			$this->columns[] = 'icq';
		}
		if(isset($_POST['skype'])){
			$this->values[] = $udb->parse_value($_POST['skype']);
			$this->columns[] = 'skype';
		}
		if(isset($_POST['day']) and $_POST['day'] != '' and isset($_POST['month']) and $_POST['month'] != '' and isset($_POST['year']) and $_POST['year'] != ''){
			$day = (int) $_POST['day'];
			$month = $udb->parse_value($_POST['month']);
			$year = (int) $_POST['year'];
			$this->values[] = "$year-$month-$day";
			$this->columns[] = 'birthdate';
		}
		if(isset($_POST['pm-alert']) and $_POST['pm-alert'] == 1){
			$this->values[] = 1;
			$this->columns[] = 'pm_alert';
		}else{
			$this->values[] = 0;
			$this->columns[] = 'pm_alert';
		}
		if(isset($_POST['addinfo'])){
			$addinfo = $udb->parse_value($_POST['addinfo']);
			$this->values[] = strip_tags($addinfo, '<p><a><pre><img><br><b><em><i><strike>');
			$this->columns[] = 'addinfo';
		}
		if(isset($_POST['nickname']) and ALLOW_NICKNAMES){
			$nickname = $udb->parse_value(strip_tags(trim($_POST['nickname'])));
			$this->values[] = $nickname;
			$this->columns[] = 'nickname';
		}
		$event->do_actions("user.edit-additional-data.check");
		if(!empty($this->columns) and !empty($this->values)){
			$i = 0;
			foreach ($this->columns as $column) {
				if($this->get_user_info($column, $user_id) !== false){
					$udb->query("UPDATE `".UC_PREFIX."usersinfo` SET `value` = '".$this->values[$i]."' WHERE `user_id` = '$user_id' AND `name` = '$column'");
				}
				else
					$udb->query("INSERT INTO `".UC_PREFIX."usersinfo` (`id`, `user_id`, `name`, `value`, `required`, `update`)
						VALUES(NULL, '$user_id', '$column', '".$this->values[$i]."', '0', NOW())");	
				$i++;
			}
			
		}
		header("Location: ".$ucms->get_back_url());
		$_SESSION['success_add'] = true;
	}

	/**
	 *
	 * Alert user if changes were applied
	 * @package uCMS
	 * @since uCMS 1.2
	 * @version uCMS 1.3
	 * @return string
	 *
	*/
	function header_messages(){
		global $ucms;
		if(isset($_POST['login']) or isset($_POST['password']) or isset($_POST['email'])){
			echo '<div class="error">';
			$this->edit_main_data();
			echo '</div>';
		}elseif(isset($_SESSION['success_main'])){
			echo '<div class="success">'.$ucms->cout("module.users.error.edit.alert.success.update_main", true).'</div>';
			unset($_SESSION['success_main']);
		}
		if(isset($_POST['edit-add'])){
			echo '<div class="error">';
			$this->edit_add_data();
			echo '</div>';
		}elseif(isset($_SESSION['success_add'])){
			echo '<div class="success">'.$ucms->cout("module.users.error.edit.alert.success.update_add", true).'</div>';
			unset($_SESSION['success_add']);
		}
	}
}
?>
