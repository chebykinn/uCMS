<?php
if($user->logged()) {
	header("Location: ".UCMS_DIR."/");
	exit;
}
$time_check = $udb->get_rows("SELECT `avatar`, `id` FROM `".UC_PREFIX."users` WHERE `activation` = '0' AND UNIX_TIMESTAMP() - UNIX_TIMESTAMP(date) > 3600");
if(!$time_check){
	if(!NICE_LINKS){
		if(isset($_GET['code']) and $_GET['code'] != '' and isset($_GET['login']) and $_GET['login'] != ''){
			$code = $_GET['code'];
			$login = $udb->parse_value($_GET['login']); 

		}
	}else{
		if(isset($url_all[2]) and isset($url_all[3]) and $url_all[2] != '' and $url_all[3] != ''){
			$code = $url_all[3];
			$login = $udb->parse_value($url_all[2]); 
		}
	}
	
	if(isset($code) and $code != '' and isset($login) and $login != ''){
	}else{
		header("Location: ".UCMS_DIR."/");
		exit;
	} 	
	$get = $udb->get_row("SELECT `id`, `group`, `password`, `activation` FROM `".UC_PREFIX."users` WHERE `login` = '$login' LIMIT 1");
	if($get){
		if($get['activation'] == 1){
			header("Location: ".UCMS_DIR."/");
			exit;
		}else{
			$activation = md5($get['id']).md5($login);
			if($activation == $code) {
				$activate = $udb->query("UPDATE `".UC_PREFIX."users` SET `activation` = '1' WHERE `login` = '$login'");
				$_SESSION['activate'] = true;		
				$_SESSION['group'] = $get['group']; 
				$_SESSION['password'] = $get['password']; 
				$_SESSION['login'] = $login; 
				$_SESSION['id'] = $get['id'];
				$ip = $user->get_user_ip();
				$hash = md5($user->session_hash(10));
				$udb->query("UPDATE `".UC_PREFIX."users` SET `logip` = '$ip', `session_hash` = '$hash', `online` = '1' WHERE `login` = '$_SESSION[login]'");
			}
			else
				$_SESSION['activate'] = false;
		}
	}
}else{
	if(USER_AVATARS){
		for($i = 0; $i < count($time_check); $i++){
			if ($time_check[$i]['avatar'] != "no-avatar.jpg" or $time_check['avatar'] != ''){
				unlink(substr($time_check[$i]['avatar']), 0);
			}
				
		}
	}
	$udb->query("DELETE FROM `".UC_PREFIX."users` WHERE `activation` = '0' AND UNIX_TIMESTAMP() - UNIX_TIMESTAMP(date) > 3600");
	$udb->query("DELETE FROM `".UC_PREFIX."usersinfo` WHERE `userid` = '$time_check[id]'");
}
header("Location: ".UCMS_DIR."/");
?>