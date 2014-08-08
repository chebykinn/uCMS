<?php
$groups = $udb->get_rows("SELECT `id`, `name` FROM `".UC_PREFIX."groups` WHERE `id` >= '".$user->get_user_group()."' ORDER BY `id` ASC");
?>
<form action="manage.php?module=users" method="post" enctype="multipart/form-data" class="forms" style="width: 100%">
	<input name="add" type="hidden">	
	<br><br><label><b><?php $ucms->cout("module.users.form.login.label"); ?></b></label><br>
	<input name="login" type="text" required maxlength="<?php echo LOGIN_MAX_SIZE; ?>">	
	<br><br><label><b><?php $ucms->cout("module.users.form.password.label"); ?></b></label><br>
	<input name="password" id="password" type="password" required autocomplete="off" maxlength="<?php echo PASSWORD_MAX_SIZE; ?>">	
	<br><br><label><b><?php $ucms->cout("module.users.form.email.label"); ?></b></label><br>
	<input name="email" id="email" type="email" required>
	<br><br><label><b><?php $ucms->cout("module.users.form.group.label"); ?></b></label><br>
	<select name="group">
		<?php
			for($i = 0; $i < count($groups); $i++){
				echo '<option value="'.$groups[$i]['id'].'">'.$groups[$i]['name'].'</option>';
			}
		?>
	</select>
	<?php if(USER_AVATARS){ ?>
	<br><br><label><b><?php $ucms->cout("module.users.form.avatar.label"); ?></b></label><br>
	<input name="avatar" id="avatar" type="file">	
	<?php } ?>
	<br><br><label><b><?php $ucms->cout("module.users.form.surname.label"); ?></b></label><br>
	<input name="surname" id="surname" type="text">
	<br><br><label><b><?php $ucms->cout("module.users.form.firstname.label"); ?></b></label><br>
	<input name="name" id="name" type="text">
	<?php if(ALLOW_NICKNAMES){ ?>
	<br><br><label><b><?php $ucms->cout("module.users.form.nickname.label"); ?></b></label><br>
	<input name="nickname" id="nickname" type="text">	
	<?php } ?>
	<br><br><label><b><?php $ucms->cout("module.users.form.icq.label"); ?></b></label><br>
	<input name="icq" id="icq" type="text">	
	<br><br><label><b><?php $ucms->cout("module.users.form.skype.label"); ?></b></label><br>
	<input name="skype" id="skype" type="text">	
	<br><br><label><b><?php $ucms->cout("module.users.form.pm_alert.label"); ?></b></label>
	<input name="pm-alert" id="pm-alert" value="1" type="checkbox">	
	<br><br><label><b><?php $ucms->cout("module.users.form.birthdate.label"); ?></b></label><br>
	<?php
	echo '<input style="width: 50px;" type="text" name="day" value="'.date('d').'" placeholder="'.$ucms->cout("module.users.form.birthdate.day.placeholder", true).'">';
	?>
	<select name="month" style="width:100px;">
		<?php
		
		echo "<option value=".date('m').">".$uc_months[date('m')]."</option>";
		for ($i = 1; $i <= 12; $i++) {
			$m = $i < 10 ? "0$i" : $i;
			echo "<option value=\"$m\">$uc_months[$m]</option>";
		}
		?>
	</select>
	<?php
	echo '<input style="width: 50px;" type="text" name="year" value="'.date('Y').'" placeholder="'.$ucms->cout("module.users.form.birthdate.year.placeholder", true).'">';
	?>
	<br><br><label><b><?php $ucms->cout("module.users.form.addinfo.label"); ?></b></label><br>
	<textarea name="addinfo" rows="10" cols="45"></textarea><br><br>
	<input class="ucms-button-submit" type="submit" value="<?php $ucms->cout("module.users.form.add.button"); ?>">
</form>