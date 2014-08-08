<?php
$comment = $args[0];
?>
<form method="post" action="manage.php?module=comments">
	<input type="hidden" name="update" value="true" >
	<input type="hidden" name="referer" value="<?php echo $ucms->get_back_url(); ?>" >
	<input type="hidden" name="id" value="<?php echo $comment['id']; ?>" >
	<table class="forms">
		<tr>
			<td><label for="body"><?php $ucms->cout("module.comments.form.update.comment.label"); ?></label></td> 
			<td><textarea style="width: 500px; height: 250px;" name="comment" id="comment" ><?php echo $comment['comment']; ?></textarea></td>
		</tr>
		<?php if($user->has_access("comments", 4)){ ?>
			<tr>
				<td><?php $ucms->cout("module.comments.form.update.author.label"); ?></td>
				<td><input type="text" value="<?php 
					if((int) $comment['author'] == 0)
						echo $comment['author'];
					else
						echo htmlspecialchars($user->get_user_login($comment['author'])); 
				?>" id="author" name="author"></td>
			</tr>
			<tr>
				<td><?php $ucms->cout("module.comments.form.update.date.label"); ?></td>
				<td>
						<?php
						$date = explode(" ", $comment['date']);
						$time = explode(":", $date[1]);
						$date = explode("-", $date[0]);
						?>
					<input type="number" min="1" max="31" value="<?php echo $date[2]; ?>" name="day" style="width:100px;">
					<select name="month" style="width:100px;">
						<?php
						echo "<option value=".$date[1].">".$uc_months[$date[1]]."</option>";
						echo "<option value=".date('m').">".$uc_months[date('m')]."</option>";
						for ($i = 1; $i <= 12; $i++) {
							$m = $i < 10 ? "0$i" : $i;
							echo "<option value=\"$m\">$uc_months[$m]</option>";
						}
						?>
					</select>
					<input type="number" min="1900" max="<?php echo date("Y"); ?>" value="<?php echo $date[0]; ?>" name="year" style="width:100px;">
					<?php $ucms->cout("module.comments.form.update.time_at.label"); ?>
					<input type="number" min="0" max="23" name="hour" style="width: 40px; height: 15px;" value="<?php echo $time[0]; ?>"> :
					<input type="number" min="0" max="59" name="minute" style="width: 40px; height: 15px;" value="<?php echo $time[1]; ?>"> :
					<input type="number" min="0" max="59" name="second" style="width: 40px; height: 15px;" value="<?php echo $time[2]; ?>">
				</td>
			</tr>
			<?php }
			$event->do_actions("comment.update.form"); 
			?>
		<tr>
		<td></td>
			<td><input type="submit" name="submit" class="ucms-button-submit" value="<?php $ucms->cout("module.comments.form.update.submit.button"); ?>"></td>
		</tr>
	</table>
</form>