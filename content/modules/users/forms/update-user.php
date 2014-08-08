<?php
$data = $args[0];
$groups = $args[1];
$id = $args[2];
$profile_info = $args[3];
?>
<form action="manage.php?module=users" method="post" enctype="multipart/form-data" class="forms" style="width: 100%">
<input name="update" type="hidden">
<input name="id" type="hidden" value="<?php echo $data['id']; ?>">		
<br><br><label><b><?php $ucms->cout("module.users.form.login.label"); ?></b></label><br>
<input name="login" type="text" value="<?php echo $data['login']; ?>" maxlength="<?php echo LOGIN_MAX_SIZE; ?>">	
<br><br><label><b><?php $ucms->cout("module.users.form.password.label"); ?></b></label><br>
<input name="password" type="password" autocomplete="off">	
<br><br><label><b><?php $ucms->cout("module.users.form.email.label"); ?></b></label><br>
<input name="email" id="email" type="email" value="<?php echo $data['email']; ?>" maxlength="<?php echo PASSWORD_MAX_SIZE; ?>">	
<?php if($data['id'] > 1){ ?>
<br><br><label><b><?php $ucms->cout("module.users.form.group.label"); ?></b></label><br>
<select name="group">
	<?php
		for($i = 0; $i < count($groups); $i++){
			echo '<option value="'.$groups[$i]['id'].'" '.($groups[$i]['id'] == $data['group'] ? "selected" : "").'>'.$groups[$i]['name'].'</option>';
		}
	?>
</select>
<?php } if(USER_AVATARS){ ?>
<br><br><label><b><?php $ucms->cout("module.users.form.avatar.label"); ?></b></label><br>
<input name="avatar" id="avatar" type="file">	
<?php } ?>
<br><br><label><b><?php $ucms->cout("module.users.form.surname.label"); ?></b></label><br>
<input name="surname" id="surname" type="text" value="<?php echo $user->get_user_info('surname', $id, $profile_info) ?>">
<br><br><label><b><?php $ucms->cout("module.users.form.firstname.label"); ?></b></label><br>
<input name="name" id="name" type="text" value="<?php echo $user->get_user_info('firstname', $id, $profile_info) ?>">
<?php if(ALLOW_NICKNAMES){ ?>
<br><br><label><b><?php $ucms->cout("module.users.form.nickname.label"); ?></b></label><br>
<input name="nickname" id="nickname" type="text" value="<?php echo $user->get_user_info('nickname', $id, $profile_info) ?>">	
<?php } ?>
<br><br><label><b><?php $ucms->cout("module.users.form.icq.label"); ?></b></label><br>
<input name="icq" id="icq" type="text" value="<?php echo $user->get_user_info('icq', $id, $profile_info) ?>">	
<br><br><label><b><?php $ucms->cout("module.users.form.skype.label"); ?></b></label><br>
<input name="skype" id="skype" type="text" value="<?php echo $user->get_user_info('skype', $id, $profile_info) ?>">	
<?php
	$birthdate = explode('-', $user->get_user_info('birthdate', $id, $profile_info));
?>
<br><br><label><b><?php $ucms->cout("module.users.form.pm_alert.label"); ?></b></label>
<input name="pm-alert" id="pm-alert" type="checkbox" value="1" <?php if($user->get_user_info('pm_alert', $id, $profile_info) == 1) echo "checked"; ?>>	
<br><br><label><b><?php $ucms->cout("module.users.form.birthdate.label"); ?></b></label><br>
	<?php
	$day = empty($birthdate[2]) ? date('d') : $birthdate[2];
	echo '<input style="width: 50px;" type="text" name="day" value="'.$day.'" placeholder="'.$ucms->cout("module.users.form.birthdate.day.placeholder", true).'">';
	?>
<select name="month" style="width:100px;">
	<?php
	if(empty($birthdate[1]))
		echo "<option value=".date('m').">".$uc_months[date('m')]."</option>";
	else
		echo "<option value=".$birthdate[1].">".$uc_months[$birthdate[1]]."</option>";
	for ($i = 1; $i <= 12; $i++) {
		$m = $i < 10 ? "0$i" : $i;
		echo "<option value=\"$m\">$uc_months[$m]</option>";
	}
	?>
</select>
	<?php
	$year = empty($birthdate[0]) ? date('Y') : $birthdate[0];
	echo '<input style="width: 50px;" type="text" name="year" value="'.$year.'" placeholder="'.$ucms->cout("module.users.form.birthdate.year.placeholder", true).'">';
	?>
<br><br><label><b><?php $ucms->cout("module.users.form.addinfo.label"); ?></b></label><br>
<textarea name="addinfo" rows="10" cols="45"><?php echo $user->get_user_info('addinfo', $id, $profile_info) ?></textarea><br><br>
<input class="ucms-button-submit" type="submit" value="<?php $ucms->cout("module.users.form.update.button"); ?>">
</form>