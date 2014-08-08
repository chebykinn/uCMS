<?php 
include "config.php";
if($user->has_access("at_least_one", 3)){
	header("Location: ".UCMS_DIR."/admin/index.php");
	exit;
}
?>
<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />	
<link rel="stylesheet" href="<?php echo SITE_DOMAIN.UCMS_DIR; ?>/admin/style.css" type="text/css" media="screen">
<link rel="apple-touch-icon" href="<?php echo SITE_DOMAIN.UCMS_DIR; ?>/favicon.ico"/>
<script type='text/javascript' src='<?php echo SITE_DOMAIN.UCMS_DIR; ?>/sys/include/jquery.js'></script>
<title><?php if(UCMS_DEBUG) echo "[DEBUG] "; $ucms->cout("template.login.header"); echo " :: "; $ucms->cout("admin.title"); ?> :: <?php site_info('name'); ?></title>
</head>
<body>
	<style type="text/css">
	input[type=password]{
		height: 25px;
		width: 400px;
	}
	</style>
<?php 
if(!$user->logged()):
	?>
	<div class="admin-login">
		<h2><?php $ucms->cout("template.login.header"); ?></h2>
	<?php
	$login->login_form();
	echo '</div>';
else: 
	?>
	<div class="admin-login">
		<div class="error">
			<h2><?php $ucms->cout("admin.error.access_denied"); ?></h2>
		</div>
		<br>

	<?php
	echo '<a href="'.UCMS_DIR.'/" >'.$ucms->cout("admin.main_page.link", true).'</a></div>';
endif;      
$udb->db_disconnect($con);
?>
</body>
</html>