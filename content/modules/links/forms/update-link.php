<?php
$id = $args[0];
if(!empty($id)){
	$link = $udb->get_row("SELECT * FROM `".UC_PREFIX."links` WHERE `id` = '$id'");
	$userd = $udb->get_row("SELECT `id`, `group` FROM `".UC_PREFIX."users` WHERE `id` = '$link[author]' LIMIT 1");
	if($link and count($link) > 0){
		if($userd){
			if($userd['id'] == $user->get_user_id()){
				$accessLVL = 2;
			}elseif($userd['group'] == ADMINISTRATOR_GROUP_ID){
				$accessLVL = 6;
			}else{
				$accessLVL = 4;
			}
		}else{
			$accessLVL = 4;
		}
		if($user->has_access("links", $accessLVL)){
?>
<form method="post" action="manage.php?module=links">
	<input type="hidden" name="update" value="true">
	<input type="hidden" name="id" value="<?php echo $id; ?>">
	<table class="forms">
		<tr>
			<td width="80px"><label for="title"><?php $ucms->cout("module.links.form.name.label"); ?></label></td>
			<td><input type="text" name="title" id="title" value="<?php echo htmlspecialchars($link['name']); ?>" required></td> 
		</tr>
		<tr>
			<td width="80px"><label for="url"><?php $ucms->cout("module.links.form.url.label"); ?></label></td>
			<td><input type="text" name="url" id="url" value="<?php echo htmlspecialchars($link['url']); ?>"></td> 
		</tr>
		<tr>
			<td><label for="body"><?php $ucms->cout("module.links.form.description.label"); ?></label></td> 
			<td><input type="text" name="body" id="body" value="<?php echo htmlspecialchars($link['description']); ?>"></td> 
		</tr>
		<tr>		
			<td><label for="author"><?php $ucms->cout("module.links.form.author.label"); ?></label></td>
			<td><input type="text" name="author" id="author" value="<?php echo ( (int) $link['author'] > 0 ? htmlspecialchars($user->get_user_login($link['author'])) : htmlspecialchars($link['author']) ); ?>"></td>
		</tr>
		<tr>		
			<td><label for="target"><?php $ucms->cout("module.links.form.target.label"); ?></label></td>
			<td>
			<input type="radio" name="target" id="target" value="_blank" <?php if($link['target'] == '_blank') echo "checked"; ?>>
			<?php $ucms->cout("module.links.form.target.blank.radio"); ?><br>
			<input type="radio" name="target" id="target" value="_self" <?php if($link['target'] == '_self') echo "checked"; ?>>
			<?php $ucms->cout("module.links.form.target.top.radio"); ?></td>	
		</tr>
		<tr>
			<td><?php $ucms->cout("module.links.form.publish.label"); ?></td>
			<td><input type="checkbox" value="1" id="publish" name="publish" <?php if($link['publish'] == 1) echo "checked"; ?>></td>
		</tr>
		<tr>
			<td></td>
			<td><input type="submit" name="submit" class="ucms-button-submit" value="<?php $ucms->cout("module.links.form.update.button"); ?>"></td>
		</tr>
	</table>
</form>
<?php
		}else{
			header("Location: manage.php?module=links");
		}
	}else{
		header("Location: manage.php?module=links");
	}
}
?>