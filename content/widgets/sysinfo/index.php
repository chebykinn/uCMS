<?php
$posts_count = $udb->num_rows("SELECT * FROM `".UC_PREFIX."posts`");
$pages_count = $udb->num_rows("SELECT * FROM `".UC_PREFIX."pages`");
$comments_count = $udb->num_rows("SELECT * FROM `".UC_PREFIX."comments`");
$users_count = $udb->num_rows("SELECT * FROM `".UC_PREFIX."users`");
$categories_count = $udb->num_rows("SELECT * FROM `".UC_PREFIX."categories`");
$groups_count = $udb->num_rows("SELECT * FROM `".UC_PREFIX."groups`");
$messages_count = $udb->num_rows("SELECT * FROM `".UC_PREFIX."messages`");
$links_count = $udb->num_rows("SELECT * FROM `".UC_PREFIX."links`");
$themes_count = is_activated_module('themes') ? $theme->get_themes_count() : 0;
$widgets_count = $this->get_widgets_count();
$modules_count = get_modules_count();
$plugins_count = is_activated_module('plugins') ? $plugin->get_plugins_count() : 0;
?>
<table class="info">
	<tr>
		<td><b><?php $ucms->cout("widget.sysinfo.time"); ?></b></td>
		<td><?php echo $ucms->date_format(time(), DATE_FORMAT.", ".TIME_FORMAT); ?></td>
	</tr>
	<tr>
		<td><b><?php $ucms->cout("widget.sysinfo.login"); ?></b></td>
		<td><?php echo $user->get_user_login(); ?></td>
	</tr>
	<tr>
		<td><b><?php $ucms->cout("widget.sysinfo.site_name"); ?></b></td>
		<td><?php site_info("name"); ?></td>
	</tr>
	<tr>
		<td><b><?php $ucms->cout("widget.sysinfo.site_desc"); ?></b></td>
		<td><?php site_info("description"); ?></td>
	</tr>
	<tr>
		<td><b><?php $ucms->cout("widget.sysinfo.site_title"); ?></b></td>
		<td><?php site_info("title"); ?></td>
	</tr>
	<tr>
		<td><b><?php $ucms->cout("widget.sysinfo.themename"); ?></b></td>
		<td><?php echo THEMENAME; ?></td>
	</tr>
	<tr>
		<td><b><?php $ucms->cout("widget.sysinfo.site_domain"); ?></b></td>
		<td><?php site_info("domain"); ?></td>
	</tr>
	<?php if($user->has_access("at_least_one", 4)){ ?>
	<tr>
		<td><b><?php $ucms->cout("widget.sysinfo.ucms_dir"); ?></b></td>
		<td><?php if(!UCMS_DIR) echo '/'; else echo UCMS_DIR; ?></td>
	</tr>
	<tr>
		<td><b><?php $ucms->cout("widget.sysinfo.uc_prefix"); ?></b></td>
		<td><?php echo UC_PREFIX; ?></td>
	</tr>
	<tr>
		<td><b><?php $ucms->cout("widget.sysinfo.pages_count"); ?></b></td>
		<td><?php echo $pages_count; ?></td>
	</tr>
	<tr>
		<td><b><?php $ucms->cout("widget.sysinfo.posts_count"); ?></b></td>
		<td><?php echo $posts_count; ?></td>
	</tr>
	<tr>
		<td><b><?php $ucms->cout("widget.sysinfo.categories_count"); ?></b></td>
		<td><?php echo $categories_count; ?></td>
	</tr>
	<tr>
		<td><b><?php $ucms->cout("widget.sysinfo.comments_count"); ?></b></td>
		<td><?php echo $comments_count; ?></td>
	</tr>
	<tr>
		<td><b><?php $ucms->cout("widget.sysinfo.users_count"); ?></b></td>
		<td><?php echo $users_count; ?></td>
	</tr>
	<tr>
		<td><b><?php $ucms->cout("widget.sysinfo.groups_count"); ?></b></td>
		<td><?php echo $groups_count; ?></td>
	</tr>
	<tr>
		<td><b><?php $ucms->cout("widget.sysinfo.messages_count"); ?></b></td>
		<td><?php echo $messages_count; ?></td>
	</tr>
	<tr>
		<td><b><?php $ucms->cout("widget.sysinfo.links_count"); ?></b></td>
		<td><?php echo $links_count; ?></td>
	</tr>
	<?php if(is_activated_module('themes')){ ?>
	<tr>
		<td><b><?php $ucms->cout("widget.sysinfo.themes_count"); ?></b></td>
		<td><?php echo $themes_count; ?></td>
	</tr>
	<?php } ?>
	<tr>
		<td><b><?php $ucms->cout("widget.sysinfo.widgets_count"); ?></b></td>
		<td><?php echo $widgets_count; ?></td>
	</tr>
	<tr>
		<td><b><?php $ucms->cout("widget.sysinfo.modules_count"); ?></b></td>
		<td><?php echo $modules_count; ?></td>
	</tr>
	<?php if(is_activated_module('plugins')){ ?>
	<tr>
		<td><b><?php $ucms->cout("widget.sysinfo.plugins_count"); ?></b></td>
		<td><?php echo $plugins_count; ?></td>
	</tr>
	<?php 
		}
	} ?>
</table>