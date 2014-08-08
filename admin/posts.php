<?php 
$title = "Посты :: ";
include 'head.php';
include 'sidebar.php';
require 'manage-posts.php';
if(!$user->has_access(1, 2)) header("Location: index.php");
echo '<div id="content">';
if(isset($_SESSION['success_add'])) {
	echo '<div class="success">Пост успешно добавлен.</div>';
	unset($_SESSION['success_add']);
}
if(isset($_SESSION['success_upd'])) {
	echo '<div class="success">Пост успешно обновлен.</div>';
	unset($_SESSION['success_upd']);
}
if(isset($_SESSION['success_del'])) {
	echo '<div class="success">Пост успешно удален.</div>';
	unset($_SESSION['success_del']);
}
if(isset($_SESSION['success_updm'])) {
	echo '<div class="success">Посты успешно обновлены.</div>';
	unset($_SESSION['success_updm']);
}
if(isset($_SESSION['success_delm'])) {
	echo '<div class="success">Посты успешно удалены.</div>';
	unset($_SESSION['success_delm']);
}

if(isset($_GET['action'])){
	$action = $_GET['action'];
	$id = isset($_GET['id']) ? (int) $_GET['id'] : false;
	switch ($action) {
		case 'add':
			echo '<h2>Добавить пост</h2><br>';
			if($user->has_access(1, 2)) add_post_form();
			echo "<script type='text/javascript' src='/admin/scripts/editor.js'></script>"; 
			exit;
		case 'update':
			echo '<h2>Изменить пост</h2><br>';
			if($user->has_access(1, 2) and $id) update_post_form($id);
			echo "<script type='text/javascript' src='/admin/scripts/editor.js'></script>"; 
			exit;
		case 'delete':
			if($user->has_access(1, 3) and $id) delete_post($id);
			exit;
	}
}
echo '<h2>Посты</h2><br>';
if($user->has_access(2, 2)){ 
	?>
	<a class="ucms-add-button" href="posts.php?action=add">Добавить пост</a>
	<?php 
} 
?>
<br><br>
<?php
if(isset($_POST['add'])):
	add_post($_POST);
elseif(isset($_POST['update'])):
	update_post($_POST);
endif;
manage_posts();
include "footer.php"; ?>
