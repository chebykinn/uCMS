<?php 
include ABSPATH.'/admin/text-editor.php';
$editor = new editor();
$id = $udb->parse_value($args[0]);
$sql = "SELECT * FROM `".UC_PREFIX."pages` WHERE `id` = '$id'";
$page = $udb->get_row($sql);
$pages = $udb->get_rows("SELECT `id`, `title`, `parent` FROM `".UC_PREFIX."pages` ORDER BY `parent` ASC");
if($page and count($page) > 0){
	?>
	<form method="post" action="manage.php?module=pages">
		<input type="hidden" name="update" value="true" >
		<input type="hidden" name="id" value="<?=$page['id']?>" >
		<?php $editor->text_input(htmlspecialchars(stripcslashes($page['body']))); ?>
		<table class="forms" style="width:100%">
			<tr>
				<td width="80px"><label for="title"><?php $ucms->cout("module.pages.editor.title.label") ?></label></td>
				<td><input type="text" name="title" id="title" value="<?=htmlspecialchars($page['title'])?>"></td> 
			</tr>
			<tr>
				<td width="80px"><label for="alias"><?php $ucms->cout("module.pages.editor.alias.label") ?></label></td>
				<td><input type="text" name="alias" id="alias" value="<?=htmlspecialchars($page['alias'])?>"></td> 
			</tr>
			<tr>
				<td></td>
				<td><?php $editor->controls(); ?></td>
			</tr>
			<tr>
				<td><label for="editor"><?php $ucms->cout("module.pages.editor.body.label") ?></label></td> 
				<td><?php $editor->main(); ?></td>
			</tr>
			<?php if($user->has_access("pages", 4)){ ?>
			<tr>
				<td><?php $ucms->cout("module.pages.editor.author.label") ?></td>
				<td><input type="text" value="<?php 
					if((int) $page['author'] == 0)
						echo $page['author'];
					else
						echo $user->get_user_login($page['author']); 
				?>" id="author" name="author"></td>
			</tr>
			<tr>
				<td><?php $ucms->cout("module.pages.editor.date.label") ?></td>
				<td>
					<?php
					$date = explode(" ", $page['date']);
					$time = explode(":", $date[1]);
					$date = explode("-", $date[0]);
					?>
					<input type="number" name="day" style="width:50px;" min="1" max="31" placeholder="<?php $ucms->cout("module.pages.editor.day.placeholder") ?>" value="<?php echo $date[2]; ?>">
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
					<input type="number" name="year" min="1900" max="<?php echo date("Y") ?>" style="width:50px;" placeholder="<?php $ucms->cout("module.pages.editor.year.placeholder") ?>" value="<?php echo $date[0]; ?>">
					<?php $ucms->cout("module.pages.editor.date.at.label") ?>
					<input type="text" name="hour" style="width: 15px; height: 15px;" value="<?php echo $time[0]; ?>"> :
					<input type="text" name="minute" style="width: 15px; height: 15px;" value="<?php echo $time[1]; ?>"> :
					<input type="text" name="second" style="width: 15px; height: 15px;" value="<?php echo $time[2]; ?>">
				</td>
			</tr>
			<?php } ?>
			<tr>
				<td><?php $ucms->cout("module.pages.editor.parent.label") ?></td>
				<td>
					<select name="parent">
						<option value="0"><?php $ucms->cout("module.pages.editor.no_parent.option") ?></option>
							<?php
							$parent = '';
							echo add_pages_tree($pages, 0, 0, $page['parent']);
							?>
						</select>
				</td>
			</tr>
			<tr>
				<td><?php $ucms->cout("module.pages.editor.sort.label") ?></td>
				<td><input type="number" value="<?php echo $page['sort']; ?>" id="sort" name="sort"></td>
			</tr>
			<tr>
				<td><?php $ucms->cout("module.pages.editor.publish.label") ?></td>
				<td><input type="checkbox" value="1" id="publish" name="publish" <?php if($page['publish'] == 1) echo "checked"; ?>></td>
			</tr>
			<tr>
				<td></td>
				<td><input type="submit" name="submit" class="ucms-button-submit" onclick='makePublish()' value="<?php $ucms->cout("module.pages.editor.update.button") ?>" /></td>
			</tr>
		</table>
	</form>
<?php
}else{
	header("Location: manage.php?module=pages");
}