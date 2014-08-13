<h2><?php $ucms->cout("module.posts.settings.header"); ?></h2><br>
<form action="settings.php?module=posts" method="post">
	<input type="hidden" name="post_sef_link_default" value="@alias@">
	<input type="hidden" name="category_sef_prefix_default" value="category">
	<input type="hidden" name="tag_sef_prefix_default" value="tag">
	<input type="hidden" name="posts_notification" value="0">
	<table class="forms">
		<?php if(NICE_LINKS){ ?>
		<tr>
		<td><?php $ucms->cout("module.posts.settings.post_sef_link.name"); ?></td>
		<td><input type="text" name="post_sef_link" value="<?php echo POST_SEF_LINK ?>"></td>
		<td><?php $ucms->cout("module.posts.settings.post_sef_link.description"); ?></td>
		</tr>
		<tr>
		<td><?php $ucms->cout("module.posts.settings.category_sef_prefix.name"); ?></td>
		<td><input type="text" name="category_sef_prefix" value="<?php echo CATEGORY_SEF_PREFIX ?>"></td>
		<td><?php $ucms->cout("module.posts.settings.category_sef_prefix.description"); ?></td>
		</tr>
		<tr>
		<td><?php $ucms->cout("module.posts.settings.tag_sef_prefix.name"); ?></td>
		<td><input type="text" name="tag_sef_prefix" value="<?php echo TAG_SEF_PREFIX ?>"></td>
		<td><?php $ucms->cout("module.posts.settings.tag_sef_prefix.description"); ?></td>
		</tr>
		<?php } ?>
		<tr>
		<td><?php $ucms->cout("module.posts.settings.posts_theme_file.name"); ?></td>
		<td><input type="text" name="posts_theme_file" value="<?php echo POSTS_THEME_FILE ?>"></td>
		<td><?php $ucms->cout("module.posts.settings.posts_theme_file.description"); ?></td>
		</tr>
		<?php if(POSTS_THEME_FILE != 'index'){ ?>
		<tr>
		<td><?php $ucms->cout("module.posts.settings.posts_list_title.name"); ?></td>
		<td><input type="text" name="posts_list_title" value="<?php echo POSTS_LIST_TITLE ?>"></td>
		<td><?php $ucms->cout("module.posts.settings.posts_list_title.description"); ?></td>
		</tr>
		<?php } ?>
		</tr>
		<tr>
			<td><?php $ucms->cout("module.posts.settings.posts_sort.name"); ?></td>
			<td><select name="posts_sort">
				<option value="DESC" <?php if(COMMENTS_SORT == 'DESC') echo "selected"; ?>><?php $ucms->cout("module.posts.settings.posts_sort.desc.option"); ?></option>
				<option value="ASC"  <?php if(COMMENTS_SORT == 'ASC') echo "selected"; ?>><?php $ucms->cout("module.posts.settings.posts_sort.asc.option"); ?></option>
			</select></td>
			<td><?php $ucms->cout("module.posts.settings.posts_sort.description"); ?></td>
		</tr>
		<tr>
		<td><?php $ucms->cout("module.posts.settings.posts_on_page.name"); ?></td>
		<td><input type="number" name="posts_on_page" min="1" value="<?php echo POSTS_ON_PAGE; ?>"></td>
		<td><?php $ucms->cout("module.posts.settings.posts_on_page.description"); ?></td>
		</tr>
		<tr>
			<td><?php $ucms->cout("module.posts.settings.posts_notification.name"); ?></td>
			<td><input type="checkbox" name="posts_notification" value="1" <?php if(POSTS_NOTIFICATION) echo "checked"; ?>></td>
			<td><?php $ucms->cout("module.posts.settings.posts_notification.description"); ?></td>
		</tr>
		<?php if(POSTS_NOTIFICATION){ ?>
		<tr>
			<td><?php $ucms->cout("module.posts.settings.posts_email.name"); ?></td>
			<td><input name="posts_email" type="text" value="<?php echo POSTS_EMAIL; ?>"></td>
			<td><?php $ucms->cout("module.posts.settings.posts_email.description"); ?></td>
		</tr>
		<tr>
			<td><?php $ucms->cout("module.posts.settings.posts_observed_user_groups.name"); ?></td>
			<td>
			<?php
			$group = $udb->get_rows("SELECT `id`,`name` FROM `".UC_PREFIX."groups` ORDER BY `id` ASC");
			$observed_user_groups = explode(",", POSTS_OBSERVED_USER_GROUPS);
			for($j = 0; $j < count($group); $j++){
				if($group[$j]['id'] != GUEST_GROUP_ID)
					echo "<input "
					.(in_array($group[$j]['id'], $observed_user_groups) ? 'checked' : '')
					." type=\"checkbox\" name=\"posts_observed_user_groups[]\" value=\"".$group[$j]['id']."\"> ".$group[$j]['name']."<br><br>";
			}
			?>
			</td>
			<td><?php $ucms->cout("module.posts.settings.posts_observed_user_groups.description"); ?></td>
		</tr>
		<?php } ?>
		<tr>
			<td colspan="2"><input class="ucms-button-submit" type="submit" name="settings-update" value="<?php $ucms->cout("module.posts.settings.edit.button"); ?>"></td>
		</tr>
	</table>
</form>