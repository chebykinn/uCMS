<?php
$action = NICE_LINKS ? UCMS_DIR."/reset" : UCMS_DIR."/?action=reset";
?>
<form action="<?php echo $action ?>" method="post" style="margin: 0 auto; text-align; center;">
<table class="reset" style="margin: 0 auto; text-align; center;">
<?php if(!UNIQUE_EMAILS) { ?>
<tr>
	<td><?php $ucms->cout("module.users.form.reset.login.label"); ?><br><input type="text" name="login" required maxlength="<?php echo LOGIN_MAX_SIZE; ?>"><br><br></td>
</tr><?php } ?>
<tr>
	<td><?php $ucms->cout("module.users.form.reset.email.label"); ?><br><input type="email" name="email" required><br><br></td>
</tr>
<tr>	
	<td><input type="submit" name="submit" value="<?php $ucms->cout("module.users.form.reset.send.button"); ?>" class="ubutton"></td>
</tr>
</table> 
</form>