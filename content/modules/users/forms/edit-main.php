<?php
global $profile_login, $user_id;
$from = NICE_LINKS ? UCMS_DIR."/user/".$profile_login : UCMS_DIR."/?action=profile&amp;id=".$user_id;
?>
<form action="<?php echo $from; ?>" method="post" enctype="multipart/form-data">
	<table>
		<tr>
			<td style="padding:5px;">
				<label><?php $ucms->cout("module.users.form.edit-main.login.label"); ?></label><br>
				<input name="login" type="text" value="<?php echo htmlspecialchars($user->get_user_login($user_id)); ?>" maxlength="<?php echo LOGIN_MAX_SIZE; ?>">	
			</td>
			<td style="padding:5px;">
				<label><?php $ucms->cout("module.users.form.edit-main.password.label"); ?></label><br>
				<input name="password" id="password" type="password" autocomplete="off" maxlength="<?php echo PASSWORD_MAX_SIZE; ?>">	
			</td>
		</tr>
		<tr>
			<td style="padding:5px;">
				<label><?php $ucms->cout("module.users.form.edit-main.email.label"); ?></label><br>
				<input name="email" id="email" type="email" value="<?php echo htmlspecialchars($user->get_user_email($user_id)); ?>">	
			</td>
			<?php if(USER_AVATARS){ ?><td style="padding:5px;">
				<label><?php $ucms->cout("module.users.form.edit-main.avatar.label"); ?></label><br>
				<input name="avatar" id="avatar" type="file">	
			</td><?php } ?>
		</tr>
		<tr>
		<td>
			<input class="ubutton" type="submit" value="<?php $ucms->cout("module.users.form.edit-main.update.button"); ?>">
		</td>
		</tr>
	</table>
</form>