<?php
$register_link = NICE_LINKS ? UCMS_DIR.'/registration' : UCMS_DIR.'/?action=registration';
$reset_link = NICE_LINKS ? UCMS_DIR.'/reset' : UCMS_DIR.'/?action=reset';
$login_link = NICE_LINKS ? UCMS_DIR.'/login' : UCMS_DIR.'/?action=login';
?>
<form action="<?php echo $login_link ?>" method="post">
		<table style="margin: 0 auto;">
			<tr>					
				<td><input name="login" type="text" size="15" maxlength="<?php echo LOGIN_MAX_SIZE; ?>" placeholder="<?php $ucms->cout("module.users.form.login.login.placeholder") ?>" required></td>
			</tr>
			<tr>
				<td><input name="password" type="password" size="15" maxlength="<?php echo PASSWORD_MAX_SIZE; ?>" placeholder="<?php $ucms->cout("module.users.form.login.password.placeholder") ?>" required></td>
			</tr>
			<tr>
				<td><input name="auto" type="checkbox" value="1" ><?php $ucms->cout("module.users.form.login.remember.checkbox") ?></td>
			</tr>
			<?php $event->do_actions("user.login.form"); ?>
			<tr>
				<td><button type="submit" name="submit" class="ubutton"><?php $ucms->cout("module.users.form.login.button") ?></button></td>
			</tr>
			<?php 
			if(!UCMS_MAINTENANCE){
				if(ALLOW_REGISTRATION){ ?>
				<tr>
					<td><a href="<?php echo $register_link; ?>"><?php $ucms->cout("module.users.form.login.register.link") ?></a></td> 
				</tr><?php } ?>
				<tr>
					<td><a href="<?php echo $reset_link; ?>"><?php $ucms->cout("module.users.form.login.reset.link") ?></a> 
				</tr>
			<?php } ?>
		</table>
</form>