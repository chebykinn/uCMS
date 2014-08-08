<?php 
$title = "Группы :: ";
include 'head.php';
include 'sidebar.php';
if(!$user->has_access(4, 7)) header("Location: index.php");
require 'manage-groups.php';
echo '<div id="content">';
	if(isset($_SESSION['success_add'])) {
		echo '<div class="success">Группа успешно добавлена.</div>';
		unset($_SESSION['success_add']);
	}
	if(isset($_SESSION['success_upd'])) {
		echo '<div class="success">Группа успешно обновлена.</div>';
		unset($_SESSION['success_upd']);
	}
	if(isset($_SESSION['success_del'])) {
		echo '<div class="success">Группа успешно удалена.</div>';
		unset($_SESSION['success_del']);
	}
			
	if(isset($_GET['action'])){
		$action = $_GET['action'];
		$id = isset($_GET['id']) ? (int) $_GET['id'] : false;
		switch ($action) {
			case 'add':
				echo '<h2>Добавить группу</h2><br>';
				if($user->has_access(4, 6)) add_group_form();
				exit;
			case 'update':
				echo '<h2>Изменить группу</h2><br>';
				if($user->has_access(4, 6) and $id) update_group_form($id);
				exit;
			case 'delete':
				if($user->has_access(4, 7) and $id) delete_group($id);
				exit;
		}
	}
	if(isset($_POST['add']) and $user->has_access(4, 6)):
		add_group($_POST);
	elseif(isset($_POST['update']) and $user->has_access(4, 6)):
		update_group($_POST);
	endif;

	?>
	<h2>Группы</h2><br>
	<a class="ucms-add-button" href="groups.php?action=add">Добавить группу</a>
	<br><br>
	<?php manage_groups(); ?>
<?php include "footer.php"; ?>