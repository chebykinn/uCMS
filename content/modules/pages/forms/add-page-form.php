<?php
include ABSPATH.'/admin/text-editor.php';
$editor = new editor();
global $user, $uc_months, $udb;
$pages = $udb->get_rows("SELECT `id`, `title`, `parent` FROM `".UC_PREFIX."pages` ORDER BY `parent` ASC");
?>
<form method="post" action="manage.php?module=pages">
	<input type="hidden" name="add" value="true">
	<?php $editor->text_input(); ?>
	<table class="forms" style="width:100%">	
		<tr>
			<td width="80px"><label for="title"><?php $ucms->cout("module.pages.editor.title.label") ?></label></td>
			<td><input type="text" name="title" id="title"></td> 
		</tr>
		<tr>
			<td width="80px"><label for="alias"><?php $ucms->cout("module.pages.editor.alias.label") ?></label></td>
			<td><input type="text" name="alias" id="alias"></td> 
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
			<td><input type="text" value="<?php echo $user->get_user_login(); ?>" id="author" name="author"></td>
		</tr>
		<tr>
			<td><?php $ucms->cout("module.pages.editor.date.label") ?></td>
			<td>
				<input type="number" name="day" style="width:50px;" min="1" max="31" placeholder="<?php $ucms->cout("module.pages.editor.day.placeholder") ?>" value="<?php echo date("d") ?>">
				<select name="month" style="width:100px;">
					<?php
					
					echo "<option value=".date('m').">".$uc_months[date('m')]."</option>";
					for ($i = 1; $i <= 12; $i++) {
						$m = $i < 10 ? "0$i" : $i;
						echo "<option value=\"$m\">$uc_months[$m]</option>";
					}
					?>
				</select>
				<input type="number" name="year" min="1900" max="2014" style="width:50px;" placeholder="<?php $ucms->cout("module.pages.editor.year.placeholder") ?>" value="<?php echo date("Y") ?>">
				<?php $ucms->cout("module.pages.editor.date.at.label") ?>
				<input type="text" name="hour" style="width: 15px; height: 15px;" value="<?php echo date("H"); ?>"> :
				<input type="text" name="minute" style="width: 15px; height: 15px;" value="<?php echo date("i"); ?>"> :
				<input type="text" name="second" style="width: 15px; height: 15px;" value="<?php echo date("s"); ?>">
			</td>
		</tr>
		<?php } ?>
		<tr>
			<td><?php $ucms->cout("module.pages.editor.parent.label") ?></td>
			<td><select name="parent">
				<option value="0"><?php $ucms->cout("module.pages.editor.no_parent.option") ?></option>
				<?php
				$parent = '';
				echo add_pages_tree($pages, 0, 0);
				?>
			</select></td>
		</tr>
		<tr>
			<td><?php $ucms->cout("module.pages.editor.sort.label") ?></td>
			<td><input type="number" value="0" id="sort" name="sort"></td>
		</tr>
		<tr>
			<td><?php $ucms->cout("module.pages.editor.publish.label") ?></td>
			<td><input type="checkbox" value="1" id="publish" name="publish" checked></td>
		</tr>
		<tr>
			<td></td>
			<td><input type="submit" name="submit" class="ucms-button-submit" onclick="makePublish()" value="<?php $ucms->cout("module.pages.editor.add.button") ?>"></td>
		</tr>
	</table>
</form>