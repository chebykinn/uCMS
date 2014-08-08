<?php
$title = "Пользователи :: ";
include "head.php";
include "sidebar.php";
require "manage-users.php"; 
echo '<div id="content">';
if(isset($_SESSION['success_add'])) {
	echo '<div class="success">Пользователь успешно добавлен.</div>';
	unset($_SESSION['success_add']);
}
if(isset($_SESSION['success_upd'])) {
	echo '<div class="success">Пользователь успешно обновлен.</div>';
	unset($_SESSION['success_upd']);
}
if(isset($_SESSION['success_del'])) {
	echo '<div class="success">Пользователь успешно удален.</div>';
	unset($_SESSION['success_del']);
}
if(isset($_SESSION['success_updm'])) {
	echo '<div class="success">Пользователи успешно обновлены.</div>';
	unset($_SESSION['success_updm']);
}
if(isset($_SESSION['success_delm'])) {
	echo '<div class="success">Пользователи успешно удалены.</div>';
	unset($_SESSION['success_delm']);
}
if(isset($_SESSION['success_actm'])) {
	echo '<div class="success">Пользователи успешно '.(!$_SESSION['success_actm'] ? "де" : "").'активированы.</div>';
	unset($_SESSION['success_actm']);
}
if(isset($_SESSION['success_act'])) {
	echo '<div class="success">Пользователь успешно '.(!$_SESSION['success_act'] ? "де" : "").'активирован.</div>';
	unset($_SESSION['success_act']);
}
if(isset($_GET['action'])){
	$action = $_GET['action'];
	$id = isset($_GET['id']) ? (int) $_GET['id'] : false;
	if($id){
		if($id == $user->get_user_id()){
			$accessLVL = 2;
		}elseif($user->get_user_group($id) == 1){
			$accessLVL = 6;
		}else{
			$accessLVL = 4;
		}
	}else{
		$accessLVL = 4;
	}
	switch ($action) {
		case 'add':
			echo '<h2>Добавить пользователя</h2><br>';
			if($user->has_access(4, 4)) add_user_form(); else header("Location: users.php");
			echo "<script type='text/javascript' src='/admin/scripts/editor.js'></script>"; 
			exit;
		
		case 'update':
			echo '<h2>Изменить пользователя</h2><br>';
			if($user->has_access(4, $accessLVL) and $id) update_user_form($id); else header("Location: users.php");
			echo "<script type='text/javascript' src='/admin/scripts/editor.js'></script>"; 
			exit;
		case 'delete':
			if($user->has_access(4, $accessLVL+1) and $id) delete_user($id); else header("Location: users.php");
			exit;
		case 'activate':
			if($user->has_access(4, $accessLVL) and $id) activate_user($id); else header("Location: users.php");
			exit;
	}
}
?>
<h2>Пользователи</h2><br>
<?php 
if($user->has_access(4, 4)){ 
	?>
	<a class="ucms-add-button" href="users.php?action=add">Добавить пользователя</a><br><br>
	<?php 
}
if(isset($_POST['update'])){
	echo '<div class="error">';
	echo 'Во время изменения данных пользователя произошли следущие ошибки:<br><br>';
	echo update_user($_POST);
	echo '</div>';
}elseif(isset($_POST['add'])){
	echo '<div class="error">';
	echo 'Во время добавления пользователя произошли следущие ошибки:<br><br>';
	echo add_user($_POST);
	echo '</div>';
}
manage_users();
include "footer.php"; ?>