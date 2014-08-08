<?php 
$title = "Категории :: ";
include 'head.php';
include 'sidebar.php';
if(!$user->has_access(1, 6)) header("Location: index.php");
require 'manage-posts.php';
?>
<div id="content">
<h2>Категории</h2><br>
<?php
	if(isset($_SESSION['success_add'])) {
		echo '<div class="success">Категория успешно добавлена.</div>';
		unset($_SESSION['success_add']);
	}
	if(isset($_SESSION['success_upd'])) {
		echo '<div class="success">Категория успешно изменена.</div>';
		unset($_SESSION['success_upd']);
	}
	if(isset($_SESSION['success_updm'])) {
		echo '<div class="success">Категории успешно изменены.</div>';
		unset($_SESSION['success_updm']);
	}
	if(isset($_SESSION['success_del'])) {
		echo '<div class="success">Категория успешно удалена.</div>';
		unset($_SESSION['success_del']);
	}
	if(isset($_SESSION['success_delm'])) {
		echo '<div class="success">Категории успешно удалены.</div>';
		unset($_SESSION['success_delm']);
	}

	if($user->has_access(1, 4)){
		?>
		<table>
			<tr>
				<td>
					<h3>Добавить категорию:</h3>
					<form class="forms" action="categories.php" method="post">
					<input type="hidden" name="add" value="add"><br>
					<label for="name"><b>Название:</b></label><br>
					<input type="text" name="name" style="width: 200px; height: 20px;"><br>
					<label for="name"><b>Ссылка:</b></label><br>
					<input type="text" name="alias" style="width: 200px; height: 20px;"><br><br>
					<input type="submit" value="Добавить" class="ucms-button-submit">
					</form>
				</td>
				<?php if(isset($_GET['update'])){ 
					$id = (int) $_GET['update'];
					$category = $udb->get_row("SELECT * FROM `".UC_PREFIX."categories` WHERE `id` = '$id'");
					if($category){
					?>
				<td>
					<h3>Изменить категорию:</h3>
					<form class="forms" action="categories.php" method="post">
					<input type="hidden" name="id" value="<?php echo $id; ?>">
					<input type="hidden" name="update" value="update"><br>
					<label for="name"><b>Название:</b></label><br>
					<input type="text" name="name" style="width: 200px; height: 20px;" value="<?php echo $category['name']; ?>"><br>
					<label for="name"><b>Ссылка:</b></label><br>
					<input type="text" name="alias" style="width: 200px; height: 20px;" value="<?php echo $category['alias']; ?>"><br><br>
					<input type="submit" value="Изменить" class="ucms-button-submit">
					</form>
				</td>
				<?php 
					}
				} ?>
			</tr>
		</table>
		<?php
		if(isset($_POST['add'])):
			add_category($_POST);
		elseif(isset($_POST['update'])):
			update_category($_POST);
		endif;
		if(isset($_GET['delete'])):
			delete_category($_GET['delete']);
		endif;
		manage_categories();
	}
include "footer.php"; ?>
