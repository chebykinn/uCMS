<?php 
$profile_info = $args[0];
global $profile_login, $user_id;
$from = NICE_LINKS ? UCMS_DIR."/user/".$user->get_user_login($user_id) : UCMS_DIR."/?action=profile&amp;id=".$user_id;
?>
<form action="<?php echo $from ?>" method="post">
	<input type="hidden" name="user-id" value="<?php echo $user_id; ?>">
	<?php if($user_id == $user->get_user_id()) $user_id = 0; ?>
	<table>
		<tr>
			<td style="padding:5px;">
				<label><?php $ucms->cout("module.users.form.edit-add.surname.label"); ?></label><br>
				<input name="surname" id="surname" type="text" value="<?php echo htmlspecialchars($user->get_user_info('surname', $user_id, $profile_info)); ?>">
			</td>
			<td style="padding:5px;">
				<label><?php $ucms->cout("module.users.form.edit-add.firstname.label"); ?></label><br>
				<input name="name" id="name" type="text" value="<?php echo htmlspecialchars($user->get_user_info('firstname', $user_id, $profile_info)); ?>">	
			</td>
		</tr>
		<tr>
			<td style="padding:5px;">
				<label><?php $ucms->cout("module.users.form.edit-add.icq.label"); ?></label><br>
				<input name="icq" id="icq" type="text" value="<?php echo htmlspecialchars($user->get_user_info('icq', $user_id, $profile_info)); ?>">	
			</td>
			<td style="padding:5px;">
				<label><?php $ucms->cout("module.users.form.edit-add.skype.label"); ?></label><br>
				<input name="skype" id="skype" type="text" value="<?php echo htmlspecialchars($user->get_user_info('skype', $user_id, $profile_info)); ?>">	
			</td>
		</tr>
		<tr>
			<td style="padding:5px; vertical-align: top;">
				<?php
					$birthdate = explode('-', htmlspecialchars($user->get_user_info('birthdate', $user_id, $profile_info)));
				?>
				<label><?php $ucms->cout("module.users.form.edit-add.birthdate.label"); ?></label><br>
				<input type="text" name="day" style="width: 30px;" value="<?php if(empty($birthdate[2])) echo date('d'); else echo $birthdate[2]; ?>" 
				placeholder="<?php $ucms->cout("module.users.form.edit-add.birthdate.day.placeholder"); ?>">
				<select name="month">
					<?php
					if(empty($birthdate[1]))
						echo "<option value=".date('m').">".$uc_months[date('m')]."</option>";
					else
						echo "<option value=".$birthdate[1].">".$uc_months[$birthdate[1]]."</option>";
					for ( $i = 1; $i <= 12; $i++ ) {
						$m = $i < 10 ? "0$i" : $i;
						echo "<option value=\"$m\">$uc_months[$m]</option>";
					}
					?>
				</select>
				<input type="text" name="year" style="width: 40px;" value="<?php if(empty($birthdate[0])) echo date('Y'); else echo $birthdate[0]; ?>" 
				placeholder="<?php $ucms->cout("module.users.form.edit-add.birthdate.year.placeholder"); ?>">
			</td>
			
			
			<td style="padding:5px;">
				<?php if(ALLOW_NICKNAMES){ ?>
				<label><?php $ucms->cout("module.users.form.edit-add.nickname.label"); ?></label><br>
				<input type="text" name="nickname" value="<?php echo htmlspecialchars($user->get_user_info('nickname', $user_id, $profile_info)); ?>">
				<?php } ?>
			</td>
		</tr>
		<tr>
			<td colspan="2" style="padding:5px;">
				<label><?php $ucms->cout("module.users.form.edit-add.pm_alert.label"); ?></label>
				<input type="checkbox" name="pm-alert" value="1" <?php if($user->get_user_info('pm_alert', $user_id, $profile_info)) echo "checked"; ?>>
			</td>
		</tr>
		<tr>
			<td colspan="2" style="padding:5px;">
				<label><?php $ucms->cout("module.users.form.edit-add.addinfo.label"); ?></label><br>
				<textarea name="addinfo" rows="10" cols="45"><?php echo htmlspecialchars($user->get_user_info('addinfo', $user_id, $profile_info)); ?></textarea>
			</td>
		</tr>
		<tr>
		<td>
			<input name="edit-add" class="ubutton" type="submit" value="<?php $ucms->cout("module.users.form.edit-add.update.button"); ?>">
		</td>
		</tr>
	</table>
</form>