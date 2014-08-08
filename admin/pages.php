<?php 
$title = "Страницы :: ";
include 'head.php';
include 'sidebar.php';
if(!$user->has_access(3, 2)) header("Location: index.php");
require "manage-pages.php";
echo '<div id="content">';
if(isset($_SESSION['success_add'])) {
	echo '<div class="success">Страница успешно добавлена.</div>';
	unset($_SESSION['success_add']);
}
if(isset($_SESSION['success_upd'])) {
	echo '<div class="success">Страница успешно обновлена.</div>';
	unset($_SESSION['success_upd']);
}
if(isset($_SESSION['success_updm'])) {
	echo '<div class="success">Страницы успешно обновлены.</div>';
	unset($_SESSION['success_updm']);
}
if(isset($_SESSION['success_del'])) {
	echo '<div class="success">Страница успешно удалена.</div>';
	unset($_SESSION['success_del']);
}
if(isset($_SESSION['success_delm'])) {
	echo '<div class="success">Страницы успешно удалены.</div>';
	unset($_SESSION['success_delm']);
}
if(isset($_GET['action'])){
	$action = $_GET['action'];
	$id = isset($_GET['id']) ? (int) $_GET['id'] : false;
	switch ($action) {
		case 'add':
			echo '<h2>Добавить страницу</h2><br>';
			if($user->has_access(2, 2)) add_page_form();
			echo "<script type='text/javascript' src='/admin/scripts/editor.js'></script>"; 
			exit;
		
		case 'update':
			echo '<h2>Изменить страницу</h2><br>';
			if($user->has_access(2, 2) and $id) update_page_form($id);
			echo "<script type='text/javascript' src='/admin/scripts/editor.js'></script>"; 
			exit;
		case 'delete':
			if($user->has_access(2, 3) and $id) delete_page($id);
			exit;
	}
}
echo '<h2>Страницы</h2><br>';
if($user->has_access(2, 2)){ 
	?>
	<a class="ucms-add-button" href="pages.php?action=add">Добавить страницу</a>
	<?php 
} 
if(isset($_POST['add']) and $user->has_access(2, 2)):
	add_page($_POST);
elseif(isset($_POST['update']) and $user->has_access(2, 2)):
	update_page($_POST);
endif;
?>
<br><br>
<?php manage_pages(); ?>
<?php include "footer.php"; ?>