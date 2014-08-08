<?php
require 'config.php';
include 'head.php';
include 'sidebar.php';
if(!$user->has_access("system")) header("Location: index.php");
$extentions = array("module", "plugin", "widget", "theme");
$extention = "general";
$type = "system";
foreach ($extentions as $ext) {
	if(!empty($_GET[$ext])){
		$extention = $_GET[$ext];
		if($extention != "general")
			$type = $ext;
	}
}

if($type == 'module' and empty($module_accessID)) header("Location: index.php");

if($user->has_access("system")){
	switch ($type) {
		case 'module':
			if(file_exists(get_module("path", $_GET['module'])."settings-process.php") and $user->has_access($module_accessID, $module_accessLVL) ){
				include get_module("path", $_GET['module'])."settings-process.php";
			}
		break;

		case 'plugin':
			if(file_exists($plugin->get("path", $_GET['plugin'])."settings-process.php") and $user->has_access("plugins", 3) and $plugin->is_activated_plugin($_GET['plugin'])){
				include $plugin->get("path", $_GET['plugin'])."settings-process.php";
			}
		break;

		case 'widget':
			if(file_exists($widget->get("path", $_GET['widget'])."settings-process.php") and $user->has_access("widgets", 3) and $widget->is_activated_widget($_GET['widget'])){
				include $widget->get("path", $_GET['widget'])."settings-process.php";
			}
		break;

		case 'theme':
			if(file_exists($theme->get("path", $_GET['theme'])."settings-process.php") and $user->has_access("themes", 3) ){
				include $theme->get("path", $_GET['theme'])."settings-process.php";
			}
		break;
	}
	
	$ucms->settings();
	if(!empty($_GET['module']) and $_GET['module'] == 'general'){
?>
	<h2><?php $ucms->cout("admin.settings.header"); ?></h2><br>
	<form action="settings.php?module=general" method="post">
		<input type="hidden" name="nice_links" value=0>
		<input type="hidden" name="modules_enabled" value=0>
		<input type="hidden" name="ucms_maintenance" value=0>
		<input type="hidden" name="embedding_allowed" value=0>
		<input type="hidden" name="site_domain_default" value="<?php echo SITE_DOMAIN ?>">
		<table class="forms">
		<tr>
		<td><?php $ucms->cout("admin.settings.site_name.label"); ?></td>
		<td><input type="text" name="site_name" value="<?php echo htmlspecialchars(SITE_NAME) ?>"></td>
		<td><?php $ucms->cout("admin.settings.site_name.description"); ?></td>
		</tr>
		<tr>
		<td><?php $ucms->cout("admin.settings.site_description.label"); ?></td>
		<td><input type="text" name="site_description" value="<?php echo htmlspecialchars(SITE_DESCRIPTION) ?>"></td>
		<td><?php $ucms->cout("admin.settings.site_description.description"); ?></td>
		</tr>
		<tr>
		<td><?php $ucms->cout("admin.settings.site_additional_name.label"); ?></td>
		<td><input type="text" name="site_title" value="<?php echo htmlspecialchars(SITE_TITLE) ?>"></td>
		<td><?php $ucms->cout("admin.settings.site_additional_name.description"); ?></td>
		</tr>
		<tr>
		<td><?php $ucms->cout("admin.settings.site_author.label"); ?></td>
		<td><input type="text" name="site_author" value="<?php echo htmlspecialchars(SITE_AUTHOR) ?>"></td>
		<td><?php $ucms->cout("admin.settings.site_author.description"); ?></td>
		</tr>
		<tr>
		<td><?php $ucms->cout("admin.settings.language.label"); ?></td>
		<td><select name="system_language" style="width: 200px;">
			<?php
			$dir = ABSPATH.LANGUAGES_PATH;
			if ($dh = opendir($dir)) {
				while (($file = readdir($dh)) !== false) {
					if(preg_match("/.lang/", $file)){
						$name = $ucms->get_language_info('name', $dir . $file);
						$lang = preg_replace("/.lang/", "", $file);
						if($name){
							echo '<option value="'.$lang.'" '.(SYSTEM_LANGUAGE == $lang ? "selected" : '').'>'.$name.'</option>';
						}
					}
				}
			}
			?>
		</select></td>
		<td><?php $ucms->cout("admin.settings.language.description"); ?></td>
		</tr>
		<tr>
		<td><?php $ucms->cout("admin.settings.ucms_maintenance.label"); ?></td>
		<td><input type="checkbox" name="ucms_maintenance" value=1 <?php if(UCMS_MAINTENANCE) echo "checked" ?>></td>
		<td><?php $ucms->cout("admin.settings.ucms_maintenance.description"); ?></td>
		</tr>
		<tr>
		<td><?php $ucms->cout("admin.settings.enable_modules.label"); ?></td>
		<td><input type="checkbox" name="modules_enabled" value=1 <?php if(MODULES_ENABLED) echo "checked" ?>></td>
		<td><?php $ucms->cout("admin.settings.enable_modules.description"); ?></td>
		</tr>
		<tr>
		<td><?php $ucms->cout("admin.settings.nice_links.label"); ?></td>
		<td><input type="checkbox" name="nice_links" value=1 <?php if(NICE_LINKS) echo "checked" ?>></td>
		<td><?php $ucms->cout("admin.settings.nice_links.description"); ?></td>
		</tr>
		<tr>
		<td><?php $ucms->cout("admin.settings.embedding_allowed.label"); ?></td>
		<td><input type="checkbox" name="embedding_allowed" value=1 <?php if(EMBEDDING_ALLOWED) echo "checked" ?>></td>
		<td><?php $ucms->cout("admin.settings.embedding_allowed.description"); ?></td>
		</tr>
		<tr>
		<td><?php $ucms->cout("admin.settings.domain.label"); ?></td>
		<td><input type="text" name="site_domain" value="<?php echo htmlspecialchars(SITE_DOMAIN) ?>"></td>
		<td><?php $ucms->cout("admin.settings.domain.description"); ?></td>
		</tr>
		<tr>
		<td><?php $ucms->cout("admin.settings.ucms_dir.label"); ?></td>
		<td><input type="text" name="ucms_dir" value="<?php echo htmlspecialchars(UCMS_DIR) ?>"></td>
		<td><?php $ucms->cout("admin.settings.ucms_dir.description"); ?></td>
		</tr>
		<tr>
		<td><?php $ucms->cout("admin.settings.timezone.label"); ?></td>
		<td><select name="ucms_timezone" style="width: 200px;">
			<?php
			include "../content/languages/timezones-".SYSTEM_LANGUAGE.".php";
			$strings = file("timezones.txt");
			$c = 0;
			foreach($strings as $string){
				if(preg_match("/@/", $string)){
					$string = trim(preg_replace("/@/", "", $string));
					if($c > 0) echo "</optgroup>";
					echo "<optgroup label=\"".$ucms->cout($string, true)."\">";
				}else{
					$string = trim($string);
					echo "<option value=\"$string\" ".(UCMS_TIMEZONE === $string ? "selected" : "").">".$names[$c]."</option>";
				}
				$c++;
				if(!isset($names[$c])) echo "</optgroup>";
			}
			?>
		</select></td>
		<td><?php $ucms->cout("admin.settings.timezone.description"); ?></td>
		</tr>
		<tr>
		<td><?php $ucms->cout("admin.settings.date_format.label");?></td>
		<td>
		<input type="radio" name="date_format" value="%Y-%m-%d" <?php if(DATE_FORMAT == "%Y-%m-%d") echo "checked"?>> <?php echo $ucms->date_format(time(), "%Y-%m-%d"); ?><br><br>
		<input type="radio" name="date_format" value="%d.%m.%Y" <?php if(DATE_FORMAT == "%d.%m.%Y") echo "checked"?>> <?php echo $ucms->date_format(time(), "%d.%m.%Y"); ?><br><br>
		<input type="radio" name="date_format" value="%Y/%m/%d" <?php if(DATE_FORMAT == "%Y/%m/%d") echo "checked"?>> <?php echo $ucms->date_format(time(), "%Y/%m/%d"); ?><br><br>
		<input type="radio" name="date_format" value="%m/%d/%Y" <?php if(DATE_FORMAT == "%m/%d/%Y") echo "checked"?>> <?php echo $ucms->date_format(time(), "%m/%d/%Y"); ?><br><br>
		<input type="radio" name="date_format" value="%d/%m/%Y" <?php if(DATE_FORMAT == "%d/%m/%Y") echo "checked"?>> <?php echo $ucms->date_format(time(), "%d/%m/%Y"); ?><br><br>
		<?php if(SYSTEM_LANGUAGE == 'ru_RU'){ ?><input type="radio" name="date_format" value="rus" <?php if(DATE_FORMAT == "rus") echo "checked"?>> <?php echo $ucms->date_format(time(), "rus"); ?><br><br><?php } ?>
		<?php $ucms->cout("admin.settings.date_format.manual.label"); ?><input type="text" name="date_format_manual" value="<?php echo htmlspecialchars(DATE_FORMAT) ?>">
		</td>
		<td><?php $ucms->cout("admin.settings.date_format.description"); ?></td>
		</tr>
		<tr>
		<td><?php $ucms->cout("admin.settings.time_format.label");?></td>
		<td>
		<input type="radio" name="time_format" value="%H:%M"    <?php if(TIME_FORMAT == "%H:%M") echo "checked"?>>    <?php echo $ucms->date_format(time(), "%H:%M"); ?><br><br>
		<input type="radio" name="time_format" value="%H:%M:%S" <?php if(TIME_FORMAT == "%H:%M:%S") echo "checked"?>>    <?php echo $ucms->date_format(time(), "%H:%M:%S"); ?><br><br>
		<?php if($ucms->date_format(time(), "%p") != ""){ ?>
		<input type="radio" name="time_format" value="%I:%M %p" <?php if(TIME_FORMAT == "%I:%M %p") echo "checked"?>> <?php echo $ucms->date_format(time(), "%I:%M %p"); ?><br><br><?php } ?>
		<?php $ucms->cout("admin.settings.time_format.manual.label"); ?><input type="text" name="time_format_manual" value="<?php echo htmlspecialchars(TIME_FORMAT) ?>">
		</td>
		<td><?php $ucms->cout("admin.settings.time_format.description"); ?></td>
		</tr>
		<tr>
		<td><?php $ucms->cout("admin.settings.phpmyadmin.label"); ?></td>
		<td><input type="text" name="phpmyadmin_link" value="<?php echo htmlspecialchars(PHPMYADMIN_LINK) ?>"></td>
		<td><?php $ucms->cout("admin.settings.phpmyadmin.description"); ?></td>
		</tr>
		<tr>
			<td><?php $ucms->cout("admin.settings.admin_email.label"); ?></td>
			<td><input name="admin_email" type="text" value="<?php echo htmlspecialchars(ADMIN_EMAIL); ?>"></td>
			<td><?php $ucms->cout("admin.settings.admin_email.description"); ?></td>
		</tr>
		<tr>
			<td colspan="2"><input class="ucms-button-submit" type="submit" name="settings-update" value="<?php $ucms->cout("admin.settings.submit.button"); ?>"></td>
		</tr>
		
		</table>
		
		
	</form>

<?php 
	}elseif($type == 'module' && file_exists(get_module("path", $_GET['module'])."settings.php") and $user->has_access($module_accessID, $module_accessLVL) ){
		require get_module("path", $_GET['module'])."settings.php";
	}elseif($type == 'plugin' && file_exists($plugin->get("path", $_GET['plugin'])."settings.php") and $user->has_access("plugins", 3) and $plugin->is_activated_plugin($_GET['plugin'])){
		require $plugin->get("path", $_GET['plugin'])."settings.php";
	}elseif($type == 'theme' && file_exists($theme->get("path", $_GET['theme'])."settings.php") and $user->has_access("widgets", 3) and $widget->is_activated_widget($_GET['widget'])){
		require $theme->get("path", $_GET['theme'])."settings.php";
	}elseif($type == 'widget' && file_exists($widget->get("path", $_GET['widget'])."settings.php") and $user->has_access("themes", 3) ){
		require $widget->get("path", $_GET['widget'])."settings.php";
	}else{
		header("Location: index.php");
	}
} 
include "footer.php"; 
?>