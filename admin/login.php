<?php 
require '../config.php'; 
if($user->has_access(0, 3))
		header("Location: ".UCMS_DIR."/admin/index.php");
?>
<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />	
<link rel="stylesheet" href="<?php echo SITE_DOMAIN.UCMS_DIR; ?>/admin/style.css" type="text/css" media="screen"  />
<link rel="apple-touch-icon" href="/favicon.ico"/>
<script type='text/javascript' src='http://ivan4b.ru/sys/include/jquery.js'></script>
<title><?php if(UCMS_DEBUG) echo "[DEBUG] "; ?>Панель управления :: <?php site_info('name'); ?></title>
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
		<h2>Вход</h2>
	<?php
	require ABSPATH.UC_USERS_PATH.'login.php';
	$login = new login();
	$login->login_form();
	echo '</div>';
else: 
	?>
	<div class="admin-login">
		<div class="error">
			<h2>Доступ запрещен</h2>
		</div>
		<br>

	<?php
	echo '<a href="'.UCMS_DIR.'/" >Главная страница</a></div>';
endif;      
$udb->db_disconnect($con);
?>
</body>
</html>
