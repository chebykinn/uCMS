<?php
function welcome(){
	global $ucms;
	?>
	<h2><?php $ucms->cout("updates.header"); ?></h2>
	<br><br>
	<?php $ucms->cout("updates.welcome.text"); ?>
	<br><br>
	<form action="index.php" method="get">
	<input type="hidden" name="action" value="make-config">
	<input class="button" type="submit" value="<?php $ucms->cout("updates.welcome.button"); ?>">
	</form>
	<?php
}

function select_language(){
	global $ucms;
	?>
	<h2>uCMS Installation :: Select Language</h2>
	<br><br>
	<form action="index.php" method="post">
	<select name="lang" style="width: 200px;">
		<?php
			$dir = ABSPATH.LANGUAGES_PATH;
			if ($dh = opendir($dir)) {
				while (($file = readdir($dh)) !== false) {
					if(preg_match("/.lang/", $file)){
						$name = $ucms->get_language_info('name', $dir . $file);
						$lang = preg_replace("/.lang/", "", $file);
						if($name){
							echo '<option value="'.$lang.'">'.$name.'</option>';
						}
					}
				}
			}
			?>
	</select>
	<br><br>
	<input type="hidden" name="action" value="select-language">
	<input class="button" type="submit" value="Continue">
	</form>
	<?php
}

function make_config(){
	global $ucms;
	?>
		<h2><?php $ucms->cout("updates.header"); ?> :: <?php $ucms->cout("updates.make_config.header"); ?></h2><br>
		<form action="index.php" method="post">
		<input type="hidden" name="action" value="make-config">
		<table style="margin: 0 auto; text-align: left; border-spacing: 10px;">
		<tr>
			<td><label><?php $ucms->cout("updates.make_config.dbserver.label"); ?></label></td>
			<td><input type="text" name="dbserver" value="localhost" required></td>
			<td style="font-size: 10pt;"><?php $ucms->cout("updates.make_config.dbserver.description"); ?></td>
		</tr>
		<tr>
			<td><label><?php $ucms->cout("updates.make_config.dbuser.label"); ?></label></td>
			<td><input type="text" name="dbuser" value="username" required></td>
			<td style="font-size: 10pt;"><?php $ucms->cout("updates.make_config.dbuser.description"); ?></td>
		</tr>
		<tr>
			<td><label><?php $ucms->cout("updates.make_config.dbpass.label"); ?></label></td> 
			<td><input type="text" name="dbpass" value="password"></td>
			<td style="font-size: 10pt;"><?php $ucms->cout("updates.make_config.dbpass.description"); ?></td>
		</tr>
		<tr>
			<td><label><?php $ucms->cout("updates.make_config.dbname.label"); ?></label></td>
			<td><input type="text" name="dbname" value="mydatabase" required></td>
			<td style="font-size: 10pt;"><?php $ucms->cout("updates.make_config.dbname.description"); ?></td>
		</tr>
		<tr>
			<td><label><?php $ucms->cout("updates.make_config.uc_prefix.label"); ?></label></td>
			<td><input type="text" name="uc_prefix" value="uc_" required></td>
			<td style="font-size: 10pt;"><?php $ucms->cout("updates.make_config.uc_prefix.description"); ?></td>
		</tr>
		</table><br>
		<input class="button" type="submit" value="<?php $ucms->cout("updates.make_config.button"); ?>">
		</form>
	<?php
	}

function make_tables(){
	global $ucms;
	?>
		<h2><?php $ucms->cout("updates.header"); ?></h2>
		<br><br><?php $ucms->cout("updates.make_tables.text"); ?><br>
		<br><form action="index.php" method="post">
		<input type="hidden" name="action" value="add-tables">
		<input class="button" type="submit" value="<?php $ucms->cout("updates.make_tables.button"); ?>">
		</form>
	<?php
}

function no_connect(){
	if(file_exists('../../config.php'))
		unlink('../../config.php');
	elseif(file_exists('../../../config.php'))
		unlink('../../../config.php');
	global $ucms;
	?>
	<h2><?php $ucms->cout("updates.header"); ?> :: <?php $ucms->cout("updates.no_connect.header"); ?></h2><br>
	<?php $ucms->cout("updates.no_connect.text"); ?><br><br>
	<form action="index.php" method="get">
	<input type="hidden" name="action" value="make-config">
	<input class="button" type="submit" value="<?php $ucms->cout("updates.no_connect.button"); ?>">
	</form>
	<?php
}

function fine(){
	global $ucms;
	echo '<h2>'.$ucms->cout("updates.header", true).'</h2><br>'.$ucms->cout("updates.fine.text", true).'<br><br><a class="button" href="../../" >'.$ucms->cout("updates.fine.button", true).'</a>';
}

function updated(){
	global $ucms;
	echo '<h2>'.$ucms->cout("updates.header", true).'</h2><br>'.$ucms->cout("updates.updated.text", true).'<br><br><a class="button" href="../../" >'.$ucms->cout("updates.updated.button", true).'</a>';
}


function fill_settings_form(){
	$domain = 'http://'.$_SERVER['HTTP_HOST'];
	global $ucms;
	?>
	<h2><?php $ucms->cout("updates.header"); ?> :: <?php $ucms->cout("updates.fill_settings_form.header"); ?></h2><br>
	<b><?php $ucms->cout("updates.fill_settings_form.description"); ?></b><br><br>
		<form action="index.php" method="post">
		<input type="hidden" name="action" value="fill-settings">
		<table style="margin: 0 auto; text-align: left; border-spacing: 10px;">
		<tr>
			<td><label><?php $ucms->cout("updates.fill_settings_form.site_name.label"); ?></label></td>
			<td><input type="text" name="site_name" value="<?php $ucms->cout("updates.fill_settings_form.site_name.default"); ?>" required></td>
			<td style="font-size: 10pt;"><?php $ucms->cout("updates.fill_settings_form.site_name.description"); ?></td>
		</tr>
		<tr>
			<td><label><?php $ucms->cout("updates.fill_settings_form.site_description.label"); ?></label></td>
			<td><input type="text" name="site_description" value="<?php $ucms->cout("updates.fill_settings_form.site_description.default"); ?>" required></td>
			<td style="font-size: 10pt;"><?php $ucms->cout("updates.fill_settings_form.site_description.description"); ?></td>
		</tr>
		<tr>
			<td><label><?php $ucms->cout("updates.fill_settings_form.site_title.label"); ?></label></td> 
			<td><input type="text" name="site_title" value="<?php $ucms->cout("updates.fill_settings_form.site_title.default"); ?>" required></td>
			<td style="font-size: 10pt;"><?php $ucms->cout("updates.fill_settings_form.site_title.description"); ?></td>
		</tr>
		<tr>
			<td><label><?php $ucms->cout("updates.fill_settings_form.domain.label"); ?></label></td>
			<td><input type="text" name="domain" value="<?php echo $domain; ?>" required></td>
			<td style="font-size: 10pt;"><?php $ucms->cout("updates.fill_settings_form.domain.description"); ?></td>
		</tr>
		<tr>
			<td><label><?php $ucms->cout("updates.fill_settings_form.dir.label"); ?></label></td> 
			<td><input type="text" name="dir"></td>
			<td style="font-size: 10pt;"><?php $ucms->cout("updates.fill_settings_form.dir.description", false, $domain); ?></td>
		</tr>
		</table>
		<br><input name="fill-tables" class="button" type="submit" value="<?php $ucms->cout("updates.fill_settings_form.button"); ?>">
		</form>
	<?php
}

function fill_users_form(){
	global $ucms;
	?>
	<h2><?php $ucms->cout("updates.header"); ?> :: <?php $ucms->cout("updates.fill_users_form.header"); ?></h2><br>
	<b><?php $ucms->cout("updates.fill_users_form.description"); ?></b><br><br>
		<form action="index.php" method="post">
		<input type="hidden" name="action" value="fill-users">
		<table style="margin: 0 auto; text-align: left; border-spacing: 10px;">
		<tr>
			<td><label><?php $ucms->cout("updates.fill_users_form.setup-email.label"); ?></label></td>
			<td><input type="text" name="setup-email" value="admin@<?php echo $_SERVER['HTTP_HOST']; ?>" required></td>
			<td style="font-size: 10pt;"><?php $ucms->cout("updates.fill_users_form.setup-email.description"); ?></td>
		</tr>
		<tr>
			<td><label><?php $ucms->cout("updates.fill_users_form.setup-login.label"); ?></label></td>
			<td><input type="text" name="setup-login" value="admin" required></td>
			<td style="font-size: 10pt;"><?php $ucms->cout("updates.fill_users_form.setup-login.description"); ?></td>
		</tr>
		<tr>
			<td><label><?php $ucms->cout("updates.fill_users_form.setup-password.label"); ?></label></td> 
			<td><input type="password" name="setup-password" placeholder="p@$sW00Rd!" required></td>
			<td style="font-size: 10pt;"><?php $ucms->cout("updates.fill_users_form.setup-password.description"); ?></td>
		</tr>
		</table><br>
		<input name="fill-tables" class="button" type="submit" value="<?php $ucms->cout("updates.fill_users_form.button"); ?>">
		</form>
	<?php
}

function success(){
	global $ucms;
	echo '<h2>'.$ucms->cout("updates.success.header", true).'</h2><br>'.$ucms->cout("updates.success.text", true).'<br><br><a class="button" href="../../" >'.$ucms->cout("updates.success.button", true).'</a>';
}
?>