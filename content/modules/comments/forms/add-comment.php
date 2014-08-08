<?php
$id = $args[0];
$back_link = $args[1];
?>
<div style="clear: both;">
	<?php alert_added(); ?>
	<form method="post" action="<?php echo $back_link ?>">
		<input type="hidden" name="post" value="<?php echo $id ?>">
		<input type="hidden" name="parent" value="0">
		<table style="width:100%;">
			<tr>
				<td><b><label for="comment"><?php $ucms->cout("module.comments.add-form.comment.label"); ?></label></b></td> 
			</tr>
			<?php
				if(!$user->logged()){
					echo "
					<tr>
						<td><label>".$ucms->cout("module.comments.add-form.guest.name.label", true)."<span style=\"color:#ff0000;\">*</span></label></td> 
					</tr>
					<tr>
						<td><input name=\"guest-name\" type=\"text\" value=\"".$ucms->cout("module.comments.add-form.guest.name", true)."\" required></td> 
					</tr>
					<tr>
						<td><label>".$ucms->cout("module.comments.add-form.guest.email.label", true)."<span style=\"color:#ff0000;\">*</span></label></td> 
					</tr>
					<tr>
						<td><input name=\"guest-email\" type=\"email\" required></td> 
					</tr>";
				}
			?>
			
			<tr>
				<td><br><textarea name="comment" id="comment" cols="80" rows="10" tabindex="4"></textarea></td> 
			</tr>
			<?php $event->do_actions("comment.add.form"); ?>
			<tr>
				<td><input type="submit" name="submit" value="<?php $ucms->cout("module.comments.add-form.submit.button"); ?>"></td>
			</tr>
		</table>	
	</form>
</div>