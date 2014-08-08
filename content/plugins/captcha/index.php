<?php
define('CAPTCHA_IMAGE_PATH', UCMS_DIR.'/content/plugins/captcha/image.php');

function check_code(){
	global $reg, $login;

	$error = false;
	if(isset($reg)){
		$pos = 'reg';
	}elseif(isset($_SESSION['use_captcha'])){
		$pos = 'log';
	}else{
		$pos = 'com';
	}

	$code = isset($_POST['code']) ? mb_strtolower($_POST['code'], "UTF-8") : "";
	if(isset($_SESSION['captcha-code'])){
 		$code2 = mb_strtolower($_SESSION['captcha-code'], "UTF-8");
 		if($code !== $code2){
 			$error = true;
 		}
 		unset($_SESSION['captcha-code']);
	}else $error = true;
	if(USE_CAPTCHA > 1){
		if($error){
			if($pos == 'reg'){
				$reg->error = $error;
				$reg->user_error(3);
			}else{
				global $user;
				if( ((int) USE_CAPTCHA === 2 and !$user->logged()) or ((int) USE_CAPTCHA === 3 and !$user->has_access("comments", 3)) ){
					global $passed_check;
					$passed_check = false;
				}
			}
		}
	}
	if($pos == 'log'){
		if($error){
			$login->error = true;
		}
	}
}

function code_form(){
	global $reg, $user, $ucms, $action;
	$captcha = 0;
	if(!isset($reg)){
		if( (((int) USE_CAPTCHA === 2 and !$user->logged()) or ((int) USE_CAPTCHA === 3 and !$user->has_access("comments", 3))) and $action == 'posts' ){
			$captcha = 2;
		}
	}else{
		if((int) USE_CAPTCHA > 0) $captcha = 1;
	}
	if($captcha == 1){ ?>
		<tr>
			<td><label><b><?php $ucms->cout("plugin.captcha.enter_code.label"); ?></b><span style="color:#ff0000;">*</span></label></td> 
			<td><img src="<?php echo CAPTCHA_IMAGE_PATH; ?>" alt=""></td>		
		</tr>
		<tr>
			<td></td>
			<td><input type="text" name="code" required autocomplete="off"></td>
		</tr>
		<?php
	}elseif($captcha == 2){
		?>
		<tr>
			<td><label><b><?php $ucms->cout("plugin.captcha.enter_code.label"); ?></b><span style="color:#ff0000;">*</span></label></td> 
		</tr>
		<tr>
			<td><img src="<?php echo CAPTCHA_IMAGE_PATH; ?>" alt=""></td>		
		</tr>
		<tr>
			<td><input type="text" name="code" required autocomplete="off"></td>
		</tr>
		<?php 
	}
}

function login_code_form(){
	global $ucms;
	if(isset($_SESSION['use_captcha'])){
		?>
		<tr>
			<td><label><b><?php $ucms->cout("plugin.captcha.enter_code.label"); ?></b><span style="color:#ff0000;">*</span></label></td> 
		</tr>
		<tr>
			<td><img src="<?php echo CAPTCHA_IMAGE_PATH; ?>" alt=""></td>		
		</tr>
		<tr>
			<td><input type="text" name="code" required autocomplete="off"></td>
		</tr>
		<?php
	}
}

$event->bind_action("user.registration.check", "check_code");
$event->bind_action("user.registration.form", "code_form");
$event->bind_action("comment.add.check", "check_code");
$event->bind_action("comment.add.form", "code_form");
$event->bind_action("user.login.form", "login_code_form");
$event->bind_action("user.login.check", "check_code");
?>