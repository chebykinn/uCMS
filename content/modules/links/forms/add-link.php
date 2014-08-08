<form method="post" action="manage.php?module=links">
	<input type="hidden" name="add" value="true">
	<table class="forms">
		<tr>
			<td width="80px"><label for="title"><?php $ucms->cout("module.links.form.name.label"); ?></label></td>
			<td><input type="text" name="title" id="title" required></td> 
		</tr>
		<tr>
			<td width="80px"><label for="url"><?php $ucms->cout("module.links.form.url.label"); ?></label></td>
			<td><input type="text" name="url" id="url"></td> 
		</tr>
		<tr>
			<td><label for="body"><?php $ucms->cout("module.links.form.description.label"); ?></label></td> 
			<td><input type="text" name="body" id="body"></td> 
		</tr>
		<tr>		
			<td><label for="author"><?php $ucms->cout("module.links.form.author.label"); ?></label></td>
			<td><input type="text" name="author" id="author" value="<?php echo $user->get_user_login(); ?>"></td>
		</tr>
		<tr>		
			<td><label for="target"><?php $ucms->cout("module.links.form.target.label"); ?></label></td>
			<td>
			<input type="radio" name="target" id="target" value="_blank" checked>
			<?php $ucms->cout("module.links.form.target.blank.radio"); ?><br>
			<input type="radio" name="target" id="target" value="_self">
			<?php $ucms->cout("module.links.form.target.top.radio"); ?></td>	
		</tr>
		<tr>
			<td><?php $ucms->cout("module.links.form.publish.label"); ?></td>
			<td><input type="checkbox" value="1" id="publish" name="publish" checked></td>
		</tr>
		<tr>
			<td></td>
			<td><input type="submit" name="submit" class="ucms-button-submit" value="<?php $ucms->cout("module.links.form.add.button"); ?>"></td>
		</tr>
	</table>
</form>