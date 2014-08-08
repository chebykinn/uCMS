<?php
$title = "Виджеты :: ";
include "head.php";
include "sidebar.php";
if(!$user->has_access(5, 7)) header("Location: index.php");
include 'manage-widgets.php';
echo '<div id="content">';
	if(isset($_SESSION['success_upd'])){
		$success_upd = $_SESSION['success_upd'];
		echo "<div class=\"success\">Виджет \"".$success_upd."\" успешно обновлён.</div>";
		unset($_SESSION['success_upd']);
	}

	if(isset($_SESSION['success_del'])){
		$success_del = $_SESSION['success_del'];
		if(!is_bool($success_del))
			echo "<div class=\"success\">Виджет \"".$success_del."\" успешно удалён.</div>";
		else 
			echo "<div class=\"success\">Виджет успешно удалён.</div>";
		unset($_SESSION['success_del']);
	}

	if(isset($_SESSION['success_delm'])){
		echo "<div class=\"success\">Виджеты успешно удалены.</div>";
		unset($_SESSION['success_delm']);
	}

	if(isset($_SESSION['success_add'])){
		$success = $_SESSION['success_add'];
		echo "<div class=\"success\">Виджет \"".$success."\" успешно установлен.</div>";
		unset($_SESSION['success_add']);
	}

	if(isset($_SESSION['success_act'])){
		$success = $_SESSION['success_act'];
		echo "<div class=\"success\">Виджет \"".$success."\" успешно активирован.</div>";
		unset($_SESSION['success_act']);
	}

	if(isset($_SESSION['success_deact'])){
		$success = $_SESSION['success_deact'];
		echo "<div class=\"success\">Виджет \"".$success."\" успешно отключен.</div>";
		unset($_SESSION['success_deact']);
	}
?>
	<h2>Виджеты</h2><br>
	<h3>Добавить виджет:</h3>
	<form action="widgets.php" method="post" enctype="multipart/form-data">
	<input type="hidden" name="add">
	<input type="file" name="widgetarch">
	<input type="submit" class="ucms-button-submit" value="OK">
	</form>
	<?php
	if($user->has_access(5, 7) and WIDGETS_MODULE){
		if(isset($_POST['add']))
			add_widget($_POST);
		else if(isset($_GET['action'])){
			$action = $_GET['action'];
			$id = isset($_GET['id']) ? (int) $_GET['id'] : false;
			switch ($action) {
				case 'activate':
					if($id) activate_widget($id);
					break;
				case 'delete':
					if($id) delete_widget($id);
					break;
			}
		}
		echo "<br><br>";
		manage_widgets();
	}
include "footer.php"; ?>