<h2><?php $ucms->cout("module.comments.settings.header"); ?></h2><br>
<form action="settings.php?module=comments" method="post">
	<input type="hidden" name="comments_enabled" value="0">
	<input type="hidden" name="tree_comments" value="0">
	<input type="hidden" name="comments_notification" value="0">
	<input type="hidden" name="comments_paging" value="0">
	<input type="hidden" name="comments_on_page_default" value="50">
	<table class="forms">
		<tr>
			<td><?php $ucms->cout("module.comments.settings.comments_enabled.label"); ?></td>
			<td><input type="checkbox" name="comments_enabled" value="1" <?php if(COMMENTS_ENABLED) echo "checked"; ?>></td>
			<td><?php $ucms->cout("module.comments.settings.comments_enabled.description"); ?></td>
		</tr>
		<?php if(COMMENTS_ENABLED){ ?>
			<tr>
				<td><?php $ucms->cout("module.comments.settings.tree_comments.label"); ?></td>
				<td><input type="checkbox" name="tree_comments" value="1" <?php if(TREE_COMMENTS) echo "checked"; ?>></td>
				<td><?php $ucms->cout("module.comments.settings.tree_comments.description"); ?></td>
			</tr>
			<tr>
				<td><?php $ucms->cout("module.comments.settings.comments_paging.label"); ?></td>
				<td><input type="checkbox" name="comments_paging" value="1" <?php if(COMMENTS_PAGING) echo "checked"; ?>></td>
				<td><?php $ucms->cout("module.comments.settings.comments_paging.description"); ?></td>
			</tr>
			<?php if(COMMENTS_PAGING){ ?>
			<tr>
				<td><?php $ucms->cout("module.comments.settings.comments_on_page.label"); ?></td>
				<td><input type="number" min="1" name="comments_on_page" value="<?php echo COMMENTS_ON_PAGE; ?>"></td>
				<td><?php $ucms->cout("module.comments.settings.comments_on_page.description"); ?></td>
			</tr>
			<?php } ?>
			<tr>
				<td><?php $ucms->cout("module.comments.settings.comments_sort.label"); ?></td>
				<td><select name="comments_sort">
					<option value="DESC" <?php if(COMMENTS_SORT == 'DESC') echo "selected"; ?>><?php $ucms->cout("module.comments.settings.comments_sort.option.desc"); ?></option>
					<option value="ASC"  <?php if(COMMENTS_SORT == 'ASC') echo "selected"; ?>><?php $ucms->cout("module.comments.settings.comments_sort.option.asc"); ?></option>
				</select></td>
				<td><?php $ucms->cout("module.comments.settings.comments_sort.description"); ?></td>
			</tr>
			<tr>
				<td><?php $ucms->cout("module.comments.settings.comments_notifications.label"); ?></td>
				<td><input type="checkbox" name="comments_notification" value="1" <?php if(COMMENTS_NOTIFICATION) echo "checked"; ?>></td>
				<td><?php $ucms->cout("module.comments.settings.comments_notifications.description"); ?></td>
			</tr>
			<?php if(COMMENTS_NOTIFICATION){ ?>
			<tr>
				<td><?php $ucms->cout("module.comments.settings.comments_email.label"); ?></td>
				<td><input name="comments_email" type="text" value="<?php echo COMMENTS_EMAIL; ?>"></td>
				<td><?php $ucms->cout("module.comments.settings.comments_email.description"); ?></td>
			</tr>
			<tr>
				<td><?php $ucms->cout("module.comments.settings.comments_observed_user_groups.label"); ?></td>
				<td>
				<?php
				$u_group = $udb->get_rows("SELECT `id`,`name` FROM `".UC_PREFIX."groups` ORDER BY `id` ASC");
				$observed_user_groups = explode(",", COMMENTS_OBSERVED_USER_GROUPS);
				for($j = 0; $j < count($u_group); $j++){
					$perms = $group->get_group_permissions($u_group[$j]['id']);
					if($perms['comments'] > 1)
						echo "<input "
						.(in_array($u_group[$j]['id'], $observed_user_groups) ? 'checked' : '')
						." type=\"checkbox\" name=\"comments_observed_user_groups[]\" value=\"".$u_group[$j]['id']."\"> ".$u_group[$j]['name']."<br><br>";
				}
				?></td>
				<td><?php $ucms->cout("module.comments.settings.comments_observed_user_groups.description"); ?></td>
			</tr>
			<?php } ?> 
			<tr>
				<td><?php $ucms->cout("module.comments.settings.comments_moderation.label"); ?></td>
				<td>
				<?php
				$u_group = $udb->get_rows("SELECT `id`,`name` FROM `".UC_PREFIX."groups` ORDER BY `id` ASC");
				$comments_moderation = explode(",", COMMENTS_MODERATION);
	
				for($j = 0; $j < count($u_group); $j++){
					$perms = $group->get_group_permissions($u_group[$j]['id']);
					if($perms['comments'] > 1)
						echo "<input "
						.(in_array($u_group[$j]['id'], $comments_moderation) ? 'checked' : '')
						." type=\"checkbox\" name=\"comments_moderation[]\" value=\"".$u_group[$j]['id']."\"> ".$u_group[$j]['name']."<br><br>";
				}
				?></td>
				<td><?php $ucms->cout("module.comments.settings.comments_moderation.description"); ?></td>
			</tr>
		<?php } ?>
		<tr>
			<td colspan="2"><input class="ucms-button-submit" type="submit" name="settings-update" value="<?php $ucms->cout("module.comments.settings.apply.button"); ?>"></td>
		</tr>
	</table>
</form>