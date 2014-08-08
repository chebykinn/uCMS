<h2><?php $ucms->cout("module.pages.settings.header"); ?></h2><br>
<form action="settings.php?module=pages" method="post">
	<table class="forms">
		<input type="hidden" name="tree_pages" value="0">
		<input type="hidden" name="page_sef_link_default" value="pages/@alias@">
		<input type="hidden" name="pages_notification" value="0">
		<?php if(NICE_LINKS){ ?>
		<tr>
		<td><?php $ucms->cout("module.pages.settings.sef_link.label"); ?></td>
		<td><input type="text" name="page_sef_link" value="<?php echo PAGE_SEF_LINK ?>"></td>
		<td><?php $ucms->cout("module.pages.settings.sef_link.description"); ?></td>
		</tr>
		<?php } ?>
		<tr>
			<td><?php $ucms->cout("module.pages.settings.notify.label"); ?></td>
			<td><input type="checkbox" name="pages_notification" value="1" <?php if(PAGES_NOTIFICATION) echo "checked"; ?>></td>
			<td><?php $ucms->cout("module.pages.settings.notify.description"); ?></td>
		</tr>
		<?php if(PAGES_NOTIFICATION){ ?>
		<tr>
			<td><?php $ucms->cout("module.pages.settings.email.label"); ?></td>
			<td><input name="pages_email" type="text" value="<?php echo PAGES_EMAIL; ?>"></td>
			<td><?php $ucms->cout("module.pages.settings.description"); ?></td>
		</tr>
		<tr>
			<td><?php $ucms->cout("module.pages.settings.observed_groups.label"); ?></td>
			<td>
			<?php
			$group = $udb->get_rows("SELECT `id`,`name` FROM `".UC_PREFIX."groups` ORDER BY `id` ASC");
			$observed_user_groups = explode(",", PAGES_OBSERVED_USER_GROUPS);
			for($j = 0; $j < count($group); $j++){
				if($group[$j]['id'] != GUEST_GROUP_ID)
					echo "<input "
					.(in_array($group[$j]['id'], $observed_user_groups) ? 'checked' : '')
					." type=\"checkbox\" name=\"pages_observed_user_groups[]\" value=\"".$group[$j]['id']."\"> ".$group[$j]['name']."<br><br>";
			}
			?>
			</td>
			<td><?php $ucms->cout("module.pages.settings.observed_groups.description"); ?></td>
		</tr>
		<?php } ?>
		<tr>
			<td colspan="2"><input class="ucms-button-submit" type="submit" name="settings-update" value="<?php $ucms->cout("module.pages.settings.update.button"); ?>"></td>
		</tr>
	</table>
</form>