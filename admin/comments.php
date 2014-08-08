<?php 
$title = "Комментарии :: ";
include 'head.php';
include 'sidebar.php';
require "manage-comments.php"; 
if(!$user->has_access(2, 2)) header("Location: index.php");
echo '<div id="content">';
if($user->has_access(2)){
	if(isset($_SESSION['success_add'])) {
		echo '<div class="success">Комментарий успешно одобрен.</div>';
		unset($_SESSION['success_add']);
	}
	if(isset($_SESSION['success_upd'])) {
		echo '<div class="success">Комментарий успешно обновлен.</div>';
		unset($_SESSION['success_upd']);
	}
	if(isset($_SESSION['success_del'])) {
		echo '<div class="success">Комментарий успешно удален.</div>';
		unset($_SESSION['success_del']);
	}
	if(isset($_SESSION['success_addm'])) {
		echo '<div class="success">Комментарии успешно одобрены.</div>';
		unset($_SESSION['success_add']);
	}
	if(isset($_SESSION['success_updm'])) {
		echo '<div class="success">Комментарии успешно обновлены.</div>';
		unset($_SESSION['success_upd']);
	}
	if(isset($_SESSION['success_delm'])) {
		echo '<div class="success">Комментарии успешно удалены.</div>';
		unset($_SESSION['success_del']);
	}
	if(isset($_GET['action'])){
			$action = $_GET['action'];
			$id = isset($_GET['id']) ? (int) $_GET['id'] : false;
			switch ($action) {
				case 'update':
					echo '<h2>Изменить комментарий</h2><br>';
					if($user->has_access(2, 3) and $id) update_comment_form($id);
					echo "<script type='text/javascript' src='/admin/scripts/editor.js'></script>"; 
					exit;
				case 'delete':
					if($user->has_access(2, 3) and $id) delete_comment($id);
					exit;
				case 'approve':
					if($user->has_access(2, 4) and $id) approve_comment($id);
					exit;
			}
		}
	if(isset($_POST['update']))
		update_comment($_POST);
	if(isset($_GET['delete']))
		delete_comment($_GET['delete']);	
	echo '<h2>Комментарии</h2><br>';
	manage_comments();
}
include "footer.php"; ?>