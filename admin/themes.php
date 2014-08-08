<?php 
$title = "Внешний вид :: ";
include 'head.php';
include 'sidebar.php';
if(!$user->has_access(5, 7)) header("Location: index.php");
include 'manage-themes.php';
echo '<div id="content">';
	if(isset($_SESSION['success_upd'])){
		$success_upd = $_SESSION['success_upd'];
		echo "<div class=\"success\">Тема \"".$success_upd."\" успешно обновлена.</div>";
		unset($_SESSION['success_upd']);
	}

	if(isset($_SESSION['success_del'])){
		$success_del = $_SESSION['success_del'];
		if(!is_bool($success_del))
			echo "<div class=\"success\">Тема \"".$success_del."\" успешно удалена.</div>";
		else 
			echo "<div class=\"success\">Тема успешно удалена.</div>";
		unset($_SESSION['success_del']);
	}

	if(isset($_SESSION['success_delm'])){
		echo "<div class=\"success\">Темы успешно удалены.</div>";
		unset($_SESSION['success_delm']);
	}

	if(isset($_SESSION['success_add'])){
		$success = $_SESSION['success_add'];
		echo "<div class=\"success\">Тема \"".$success."\" успешно установлена.</div>";
		unset($_SESSION['success_add']);
	}

	if(isset($_SESSION['success_act'])){
		$success = $_SESSION['success_act'];
		echo "<div class=\"success\">Тема \"".$success."\" успешно активирована.</div>";
		unset($_SESSION['success_act']);
	}

			?>
			<h2>Внешний вид</h2><br>
			<h3>Добавить тему:</h3>
			<form action="themes.php" method="post" enctype="multipart/form-data">
			<input type="hidden" name="add">
			<input type="file" name="themearch">
			<input type="submit" class="ucms-button-submit" value="OK">
			</form>
			<?php
			if($user->has_access(5, 7) and THEMES_MODULE){
				if(isset($_POST['add']))
					add_theme($_POST);
				else if(isset($_GET['action'])){
					$action = $_GET['action'];
					$id = isset($_GET['id']) ? (int) $_GET['id'] : false;
					switch ($action) {
						case 'activate':
							if($id) activate_theme($id);
							break;
						case 'delete':
							if($id) delete_theme($id);
							break;
					}
				}
				echo "<br><br>";
				manage_themes();
			}

include "footer.php"; ?>