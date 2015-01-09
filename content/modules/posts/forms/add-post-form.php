<?php
include ABSPATH.'/admin/text-editor.php';
$editor = new editor();
?>
<form method="post" action="manage.php?module=posts">
	<input type="hidden" name="add" value="true">
	<input type="hidden" name="author" value="<?php echo $user->get_user_id(); ?>">	
	<?php $editor->text_input(); ?>
	<table class="forms" style="width:100%">
		<tr>
			<td width="80px"><label for="title"><?php $ucms->cout("module.posts.form.title.label"); ?></label></td>
			<td><input type="text" name="title" id="title" required></td> 
		</tr>
		<tr>
			<td width="80px"><label for="alias"><?php $ucms->cout("module.posts.form.alias.label"); ?></label></td>
			<td><input type="text" name="alias" id="alias"></td> 
		</tr>
		<tr>
			<td></td>
			<td><?php $editor->controls(); ?></td>
		</td></tr>
		<tr>
			<td><label for="editor"><?php $ucms->cout("module.posts.form.editor.label"); ?></label></td> 
			<td><?php $editor->main(); ?></td>
			<tr>		
				<td><label for="keywords"><?php $ucms->cout("module.posts.form.keywords.label"); ?></label></td>
				<td><input type="text" name="keywords" id="keywords"></td>
			</tr>
		</tr>
		<tr>
			<td><?php $ucms->cout("module.posts.form.category.label"); ?></td>
			<td><select name="category" size="1">
			<?php
			$catlist = "SELECT * FROM `".UC_PREFIX."categories`";
			$l_categories = $udb->get_rows($catlist);
			for($i = 0; $i < count($l_categories); $i++){
				echo '<option value="'.$l_categories[$i]['id'].'">'.$l_categories[$i]['name'].'</option>';
			}
			?>
			</select></td>

		</tr>
		<?php if($user->has_access("posts", 4)){ ?>
		<tr>
			<td><?php $ucms->cout("module.posts.form.author.label"); ?></td>
			<td><input type="text" value="<?php echo $user->get_user_login(); ?>" id="author" name="author"></td>
		</tr>
		
		<tr>
			<td><?php $ucms->cout("module.posts.form.date.label"); ?></td>
			<td>
				<input type="number" name="day" style="width:50px;" min="1" max="31" placeholder="<?php $ucms->cout("module.posts.form.day.placeholder"); ?>" value="<?php echo date("d") ?>">
				<select name="month" style="width:100px;">
					<?php
					
					echo "<option value=".date('m').">".$uc_months[date('m')]."</option>";
					for ($i = 1; $i <= 12; $i++) {
						$m = $i < 10 ? "0$i" : $i;
						echo "<option value=\"$m\">$uc_months[$m]</option>";
					}
					?>
				</select>
				<input type="number" name="year" min="1900" max="<?php echo date("Y") ?>" style="width:50px;" placeholder="<?php $ucms->cout("module.posts.form.year.placeholder"); ?>" value="<?php echo date("Y") ?>">
				<?php $ucms->cout("module.posts.form.time.label"); ?>
				<input type="text" name="hour" style="width: 15px; height: 15px;" value="<?php echo date("H"); ?>"> :
				<input type="text" name="minute" style="width: 15px; height: 15px;" value="<?php echo date("i"); ?>"> :
				<input type="text" name="second" style="width: 15px; height: 15px;" value="<?php echo date("s"); ?>">
			</td>
		</tr>
		<tr>
			<td><?php $ucms->cout("module.posts.form.pin.label"); ?></td>
			<td><input type="checkbox" value="2" id="pin" name="pin"></td>
		</tr>
		<?php } ?>
		<tr>
			<td><?php $ucms->cout("module.posts.form.publish.label"); ?></td>
			<td><input type="checkbox" value="1" id="publish" name="publish" checked></td>
		</tr>
		<tr>
			<td><?php $ucms->cout("module.posts.form.comment.label"); ?></td>
			<td><input type="checkbox" value="-1" id="comment" name="comment"></td>
		</tr>
		<tr>
			<td></td>
			<td><input type="submit" name="submit" class="ucms-button-submit" onclick="makePublish()" value="<?php $ucms->cout("module.posts.form.add.button"); ?>"></td>
		</tr>
	</table>
</form>