<h2><?php $ucms->cout("module.users.settings.header.label"); ?></h2><br>
<form action="settings.php?module=users" method="post">
	<input type="hidden" name="user_avatars" value=0>
	<input type="hidden" name="user_messages" value=0>
	<input type="hidden" name="allow_registration" value=0>
	<input type="hidden" name="allow_nicknames" value=0>
	<input type="hidden" name="unique_emails" value=0>
	<input type="hidden" name="users_activation" value=0>
	<input type="hidden" name="new_users_notification" value=0>
	<input type="hidden" name="user_logged_in_notifications" value=0>
	<table class="forms">
		<tr>
		<td><?php $ucms->cout("module.users.settings.user_avatars.label"); ?></td>
		<td><input type="checkbox" name="user_avatars" value=1 <?php if(USER_AVATARS) echo "checked" ?>></td>
		<td><?php $ucms->cout("module.users.settings.user_avatars.description"); ?></td>
		</tr>
		<?php if(USER_AVATARS){ ?>
		<tr>
		<td><?php $ucms->cout("module.users.settings.avatar_size.label"); ?></td>
		<td><input type="number" name="avatar_width" min="50" max ="1000" value="<?php echo AVATAR_WIDTH; ?>"> x <input type="number" name="avatar_height" min="50" max ="1000" value="<?php echo AVATAR_HEIGHT; ?>"></td>
		<td><?php $ucms->cout("module.users.settings.avatar_size.description"); ?></td>
		</tr>
		<?php } ?>
		<tr>
		<td><?php $ucms->cout("module.users.settings.user_messages.label"); ?></td>
		<td><input type="checkbox" name="user_messages" value=1 <?php if(USER_MESSAGES) echo "checked" ?>></td>
		<td><?php $ucms->cout("module.users.settings.user_messages.description"); ?></td>
		</tr>
		<tr>
		<td><?php $ucms->cout("module.users.settings.allow_registration.label"); ?></td>
		<td><input type="checkbox" name="allow_registration" value=1 <?php if(ALLOW_REGISTRATION) echo "checked" ?>></td>
		<td><?php $ucms->cout("module.users.settings.allow_registration.description"); ?></td>
		</tr>
		<tr>
		<td><?php $ucms->cout("module.users.settings.unique_emails.label"); ?></td>
		<td><input type="checkbox" name="unique_emails" value=1 <?php if(UNIQUE_EMAILS) echo "checked" ?>></td>
		<td><?php $ucms->cout("module.users.settings.unique_emails.description"); ?></td>
		</tr>
		<tr>
		<td><?php $ucms->cout("module.users.settings.allow_nicknames.label"); ?></td>
		<td><input type="checkbox" name="allow_nicknames" value=1 <?php if(ALLOW_NICKNAMES) echo "checked" ?>></td>
		<td><?php $ucms->cout("module.users.settings.allow_nicknames.description"); ?></td>
		</tr>
		<tr>
		<td><?php $ucms->cout("module.users.settings.default_group.label"); ?></td>
		<td><select name="default_group" style="width: 150px;">
			
			<?php
			$grp = $udb->get_rows("SELECT `id`,`name` FROM `".UC_PREFIX."groups` ORDER BY `id` ASC");
			if(!$user->has_access('users', 6)) $j = 1;
			else $j = 0;
			for($j; $j < count($grp); $j++){
				echo "<option value=\"".$grp[$j]['id']."\" ".(DEFAULT_GROUP == $grp[$j]['id'] ? "selected" : "").">".$grp[$j]['name']."</option>";
			}
			?>
		</select></td>
		<td><?php $ucms->cout("module.users.settings.default_group.description"); ?></td>
		</tr>
		<tr>
		<td><?php $ucms->cout("module.users.settings.login_attempts_num.label"); ?></td>
		<td><input type="number" name="login_attempts_num" min="0" value="<?php echo LOGIN_ATTEMPTS_NUM ?>"></td>
		<td><?php $ucms->cout("module.users.settings.login_attempts_num.description"); ?></td>
		</tr>
		<tr>
		<td><?php $ucms->cout("module.users.settings.after_failed_login_action.label"); ?></td>
		<td><select name="after_failed_login_action">
			<option value="0" <?php if(AFTER_FAILED_LOGIN_ACTION == 0) echo "selected"; ?>><?php $ucms->cout("module.users.settings.after_failed_login_action.delay.option"); ?></option>
			<option value="1" <?php if(AFTER_FAILED_LOGIN_ACTION == 1) echo "selected"; ?>><?php $ucms->cout("module.users.settings.after_failed_login_action.code.option"); ?></option>
		</select></td>
		<td><?php $ucms->cout("module.users.settings.after_failed_login_action.description"); ?></td>
		</tr>
		<tr>
		<td><?php $ucms->cout("module.users.settings.use_captcha.label"); ?></td>
		<td>
			<select name="use_captcha">
			<option value="0" <?php if(USE_CAPTCHA == 0) echo "selected"; ?>><?php $ucms->cout("module.users.settings.use_captcha.nowhere.option"); ?></option>
			<option value="1" <?php if(USE_CAPTCHA == 1) echo "selected"; ?>><?php $ucms->cout("module.users.settings.use_captcha.registration.option"); ?></option>
			<option value="2" <?php if(USE_CAPTCHA == 2) echo "selected"; ?>><?php $ucms->cout("module.users.settings.use_captcha.guest_comment.option"); ?></option>
			<option value="3" <?php if(USE_CAPTCHA == 3) echo "selected"; ?>><?php $ucms->cout("module.users.settings.use_captcha.user_comment.option"); ?></option>
			</select>
		</td>
		<td><?php $ucms->cout("module.users.settings.use_captcha.description"); ?></td>
		</tr>
		<tr>
			<td><?php $ucms->cout("module.users.settings.users_activation.label"); ?></td>
			<td><input type="checkbox" name="users_activation" value="1" <?php if(USERS_ACTIVATION) echo "checked" ?>></td>
			<td><?php $ucms->cout("module.users.settings.users_activation.description"); ?></td>
		</tr>
		<tr>
			<td><?php $ucms->cout("module.users.settings.login_size.label"); ?></td>
			<td>
			<?php $ucms->cout("module.users.settings.login_size.from.label"); ?>
			<input type="number" placeholder="<?php $ucms->cout("module.users.settings.login_min_size.placeholder"); ?>" style="width: 50px;" name="login_min_size" min="1" value="<?php echo LOGIN_MIN_SIZE ?>">
			<?php $ucms->cout("module.users.settings.login_size.to.label"); ?>
			<input type="number" placeholder="<?php $ucms->cout("module.users.settings.login_max_size.placeholder"); ?>" style="width: 50px;" name="login_max_size" min="1" value="<?php echo LOGIN_MAX_SIZE ?>">
			<?php $ucms->cout("module.users.settings.login_size.characters.label"); ?>
			</td>
			<td><?php $ucms->cout("module.users.settings.login_size.description"); ?></td>
		</tr>
		<tr>
			<td><?php $ucms->cout("module.users.settings.password_size.label"); ?></td>
			<td>
			<?php $ucms->cout("module.users.settings.password_size.from.label"); ?>
			<input type="number" placeholder="<?php $ucms->cout("module.users.settings.password_min_size.placeholder"); ?>" style="width: 50px;" name="password_min_size" min="1" value="<?php echo PASSWORD_MIN_SIZE ?>">
			<?php $ucms->cout("module.users.settings.password_size.to.label"); ?>
			<input type="number" placeholder="<?php $ucms->cout("module.users.settings.password_max_size.placeholder"); ?>" style="width: 50px;" name="password_max_size" min="1" value="<?php echo PASSWORD_MAX_SIZE ?>">
			<?php $ucms->cout("module.users.settings.password_size.characters.label"); ?>
			</td>
			<td><?php $ucms->cout("module.users.settings.password_size.description"); ?></td>
		</tr>
		<tr>
			<td><?php $ucms->cout("module.users.settings.new_users_notification.label"); ?></td>
			<td><input type="checkbox" name="new_users_notification" value="1" <?php if(NEW_USERS_NOTIFICATION) echo "checked" ?>></td>
			<td><?php $ucms->cout("module.users.settings.new_users_notification.description"); ?></td>
		</tr>
		<?php if(NEW_USERS_NOTIFICATION){ ?>
		<tr>
			<td><?php $ucms->cout("module.users.settings.new_user_email.label"); ?></td>
			<td><input name="new_user_email" type="text" value="<?php echo NEW_USER_EMAIL; ?>"></td>
			<td><?php $ucms->cout("module.users.settings.new_user_email.description"); ?></td>
		</tr>
		<?php } ?>
		<tr>
			<td><?php $ucms->cout("module.users.settings.user_logged_in_notifications.label"); ?></td>
			<td><input type="checkbox" name="user_logged_in_notifications" value="1" <?php if(USER_LOGGED_IN_NOTIFICATIONS) echo "checked" ?>></td>
			<td><?php $ucms->cout("module.users.settings.user_logged_in_notifications.description"); ?></td>
		</tr>
		<?php if(USER_LOGGED_IN_NOTIFICATIONS){ ?>
		<tr>
			<td><?php $ucms->cout("module.users.settings.user_logged_in_email.label"); ?></td>
			<td><input name="user_logged_in_email" type="text" value="<?php echo USER_LOGGED_IN_EMAIL; ?>"></td>
			<td><?php $ucms->cout("module.users.settings.user_logged_in_email.description"); ?></td>
		</tr>
		<tr>
			<td><?php $ucms->cout("module.users.settings.observed_user_groups.label"); ?></td>
			<td>
			<?php
			$group = $udb->get_rows("SELECT `id`,`name` FROM `".UC_PREFIX."groups` ORDER BY `id` ASC");
			$observed_user_groups = explode(",", OBSERVED_USER_GROUPS);
			for($j = 0; $j < count($group); $j++){
				if($group[$j]['id'] != GUEST_GROUP_ID)
					echo "<input "
					.(in_array($group[$j]['id'], $observed_user_groups) ? 'checked' : '')
					." type=\"checkbox\" name=\"observed_user_groups[]\" value=\"".$group[$j]['id']."\"> ".$group[$j]['name']."<br><br>";
			}
			?>
			</td>
			<td><?php $ucms->cout("module.users.settings.observed_user_groups.description"); ?></td>
		</tr>
		<?php } ?>
		
		<tr>
			<td colspan="2"><input class="ucms-button-submit" type="submit" name="settings-update" value="<?php $ucms->cout("module.users.settings.update.button"); ?>"></td>
		</tr>
	</table>
</form>