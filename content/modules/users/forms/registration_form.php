<?php
$from = NICE_LINKS ? UCMS_DIR."/registration" : UCMS_DIR."/?action=registration";
?>
<form action="<?php echo $from; ?>" method="post" style="margin: 0 auto;" enctype="multipart/form-data">
	<input type="hidden" name="action" value="registration">
<table style="margin: 0 auto;" class="registration">
	<tr>
		<td><label><b><?php $ucms->cout("module.users.form.registration.login.label"); ?></b><span style="color:#ff0000;">*</span></label></td>
		<td><input autocomplete="off" name="login" type="text" size="15"  maxlength="<?php echo LOGIN_MAX_SIZE; ?>" required <?php if(isset($_POST['login'])) echo 'value='.htmlspecialchars($_POST['login'])?> ></td>
	</tr> 
	<tr>

		<td><label><b><?php $ucms->cout("module.users.form.registration.password.label"); ?></b><span style="color:#ff0000;">*</span></label></td>
		<td><input autocomplete="off" name="password" type="password" size="15"  maxlength="<?php echo PASSWORD_MAX_SIZE; ?>" required></td>
	</tr>
	<tr>
		<td><label><b><?php $ucms->cout("module.users.form.registration.email.label"); ?></b><span style="color:#ff0000;">*</span></label></td> 
		<td><input autocomplete="off" name="email" type="email" size="15" maxlength="100" required <?php if(isset($_POST['email'])) echo 'value='.htmlspecialchars($_POST['email'])?> ></td>
	</tr>
	<?php if(USER_AVATARS){ ?>
	<tr>
		<td><label><b><?php $ucms->cout("module.users.form.registration.avatar.label"); ?></b></label></td> 
		<td><input type="file" name="avatar"></td>
	</tr>
	<?php }
	$event->do_actions("user.registration.form"); ?>
</table>
<br>
<div style="text-align: center;">
	<input type="submit" name="submit" value="<?php $ucms->cout("module.users.form.registration.register.button"); ?>" class="ubutton">
</div>
</form>