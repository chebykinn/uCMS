<?php 
$title = "Ссылки :: ";
include 'head.php';
include 'sidebar.php';
require 'manage-links.php'; 
if(!$user->has_access(5, 7)) header("Location: index.php");
echo '<div id="content">';
if(isset($_SESSION['success_add'])) {
	echo '<div class="success">Ссылка успешно добавлена.</div>';
	unset($_SESSION['success_add']);
}
if(isset($_SESSION['success_upd'])) {
	echo '<div class="success">Ссылка успешно обновлена.</div>';
	unset($_SESSION['success_upd']);
}
if(isset($_SESSION['success_del'])) {
	echo '<div class="success">Ссылка успешно удалена.</div>';
	unset($_SESSION['success_del']);
}
if(isset($_SESSION['success_updm'])) {
	echo '<div class="success">Ссылки успешно обновлены.</div>';
	unset($_SESSION['success_updm']);
}
if(isset($_SESSION['success_delm'])) {
	echo '<div class="success">Ссылки успешно удалены.</div>';
	unset($_SESSION['success_delm']);
}
if(isset($_GET['action'])){
	$action = $_GET['action'];
	$id = isset($_GET['id']) ? (int) $_GET['id'] : false;
	switch ($action) {
		case 'add':
			echo '<h2>Добавить ссылку</h2><br>';
			if($user->has_access(5, 7)) add_link_form();
			echo "<script type='text/javascript' src='/admin/scripts/editor.js'></script>"; 
			exit;
		
		case 'update':
			echo '<h2>Изменить ссылку</h2><br>';
			if($user->has_access(5, 7) and $id) update_link_form($id);
			echo "<script type='text/javascript' src='/admin/scripts/editor.js'></script>"; 
			exit;
		case 'delete':
			if($user->has_access(5, 7) and $id) delete_link($id);
			exit;
	}
}
?>
<h2>Ссылки</h2><br>
<a class="ucms-add-button" href="links.php?action=add">Добавить ссылку</a><br><br>
<?php
if(isset($_POST['add'])):
	add_link($_POST);
elseif(isset($_POST['update'])):
	update_link($_POST);
endif;
if(isset($_GET['delete'])):
	delete_link($_GET['delete']);
endif;
manage_links();
include "footer.php"; ?>
