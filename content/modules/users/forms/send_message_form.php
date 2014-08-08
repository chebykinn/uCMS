<?php
$from = $args[0];
$profile_login = $args[1];
?>
<form action="<?php echo $from ?>" method="post">
	<?php if($user->is_profile()){ ?><br><b><?php $ucms->cout("module.users.pm.send_form.receiver.label"); ?></b><br>
	<input type="text" name="receiver" style="width: 200px;" required><?php }else{ ?>
	<input type="hidden" name="receiver" value="<?php echo $profile_login ?>"><?php } ?>
	<br><b><?php $ucms->cout("module.users.pm.send_form.message.label"); ?></b><br>
	<textarea name="message" style="width: 350px; height: 150px;" required></textarea>
	<br><input type="submit" value="<?php $ucms->cout("module.users.pm.send_form.button"); ?>">
</form>