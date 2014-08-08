<?php
function manage_themes(){
	global $udb, $user;
	if (isset($_POST['item']) and isset($_POST['actions'])){
		$items = array();
		$action = (int) $_POST['actions'];
		foreach ($_POST['item'] as $id) {
			$id = (int) $id;
			if($user->has_access(5, 7) and $id > 1){
				$items[] = $id;
			}
		}
		$ids = implode(',', $items);
		if (count($items) > 0) {
			switch ($action) {
				case 3:
					foreach ($items as $id) {
						delete_theme($id);
					}
					unset($_SESSION['success_del']);
					if(!isset($_SESSION['no_del'])){
						if (count($items) > 1) {
							$_SESSION['success_delm'] = true;
						}else
							$_SESSION['success_del'] = true;
					}
 					header("Location: ".UCMS_DIR."/admin/themes.php");
				break;
				
			}
		}
	}
		$call = $udb->num_rows("SELECT * FROM `".UC_PREFIX."themes`");
		$user_id = $user->get_user_id();
		$perpage = 25;
		$columns = array('name','author');
		$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
		$orderby = isset($_GET['orderby']) ? in_array($_GET['orderby'], $columns) ? $_GET['orderby'] : 'id' : 'id';
		$order = (isset($_GET['order']) and $_GET['order'] == 'DESC') ? 'ASC' : 'DESC';
		if($page <= 0) $page = 1;
		$count = $udb->num_rows("SELECT * FROM `".UC_PREFIX."themes` ORDER BY `$orderby` $order");
		$pages_count = 0;
		if($count != 0){ 
			$pages_count = ceil($count / $perpage); 
			if ($page > $pages_count):
				$page = $pages_count;
			endif; 
			$start_pos = ($page - 1) * $perpage;
			$sql = "SELECT * FROM `".UC_PREFIX."themes` ORDER BY `$orderby` $order LIMIT $start_pos, $perpage";

		}else $sql  = "SELECT * FROM `".UC_PREFIX."themes` WHERE `id` = '0'";
		$link1 = UCMS_DIR."/admin/themes.php?orderby=title&amp;order=".$order.(isset($_GET['page']) ? "&amp;page=".$_GET['page'] : "");
		$link2 = UCMS_DIR."/admin/themes.php?orderby=author&amp;order=".$order.(isset($_GET['page']) ? "&amp;page=".$_GET['page'] : "");
		$mark = $order == "ASC" ? '↑' : '↓';
		?>
		<b>Всего тем:</b> <?php echo $call; ?><br><br>
		<b>Сортировать по:</b> <a href="<?php echo $link1; ?>">Названию <?php echo $mark; ?></a> | <a href="<?php echo $link2; ?>">Автору <?php echo $mark; ?></a>
		<br><br>
		<b>Отметить все:</b> <input type="checkbox" name="select-all" value="1">
		<br><br>
		<form action="themes.php" method="post">
		<?php if($user->has_access(5, 7)){ ?>
		<select name="actions" style="width: 250px;">
			<option>Отмеченные</option>
			<option value="3">Удалить</option>
		</select>
		<?php } ?>
		<input type="submit" value="Применить" class="ucms-button-submit">
		<br>
		<?php
		if($pages_count > 1){
			echo "<br>";
			pages($page, $count, $pages_count, 15, false);
			echo '<br>';
		}?><br>
		<table class="themes">
		<?php
		$themes = $udb->get_rows($sql);
		if($themes){
			for($i = 0; $i < count($themes); $i+=5){
				echo "<tr>";
				for($j = 0; $j < 5; $j++){
					$id = $i + $j;
					if(isset($themes[$id]['id'])){
						$status = $themes[$id]['activated'] == 1 ? "Активирована" : "Установлена";
						$is_installed = $themes[$id]['activated'] == 1 ? "(<b>Текущая</b>)" : '';
						?></p>
							<td <?php if($themes[$id]['activated'] == 1) echo "style=\"border: 2px solid #0099FF;\""; ?>><p style="float:left;"><input type="checkbox" name="item[]" value="<?php echo $themes[$id]['id']; ?>"></p>
								<img src="<?php if(file_exists("../".UC_THEMES_PATH.$themes[$id]['dir']."/screenshot.png")){ echo "../".UC_THEMES_PATH.$themes[$id]['dir']."/screenshot.png"; }else echo "images/noscreen.png"; ?>" width="150" height="150" alt="screenshot">
								<?php
								echo '<ul>';
								echo "<li><span>Название: </span>".$themes[$id]['name']." $is_installed</li>";
								echo "<li><span>Версия: </span>".$themes[$id]['version']."</li>";
								echo "<li><span>Автор: </span>".$themes[$id]['author']."</li>";
								echo "<li><span>Сайт: </span><a target=\"_blank\" href=\"".$themes[$id]['site']."\">".$themes[$id]['site']."</a></li>";
								echo "<li><span>Описание: </span>".$themes[$id]['description']."</li>";
								echo '</ul>';
								?><br>
								<span class="actions"><?php if($themes[$id]['activated'] < 1){ ?><a class="install-button" href="<?php echo UCMS_DIR ?>/admin/themes.php?action=activate&amp;id=<?php echo $themes[$id]['id'];?>">Активировать</a> <?php }else{ ?><a class="install-button">Активирована</a> <?php } ?><a class="install-button" href="<?php echo UCMS_DIR ?>/admin/editor.php?type=themes&amp;action=edit&amp;dir=<?php echo $themes[$id]['dir']?>">Редактировать</a> <?php if($themes[$id]['id'] > 1){ ?><a class="delete-button" href="<?php echo UCMS_DIR ?>/admin/themes.php?action=delete&amp;id=<?php echo $themes[$id]['id'];?>">Удалить</a><?php } ?></span>
							</td>
						<?php
					}
				}
				echo "</tr>";
			}
		}else{
			?>
			<tr>
				<td colspan="9" style="text-align:center;">Тем пока нет.</td>
			</tr>
			<?php
		}
			echo '</table></form>';
}

function delete_theme($id){
	global $udb, $ucms;
	if(!$id){
		return false; 
	}
	$id = (int) $id;
	if($id > 1){
		$theme = $udb->get_row("SELECT `name`, `dir` FROM `".UC_PREFIX."themes` WHERE `id` = '$id'");
		if($theme){
			$theme_name = $theme['name'];
			$theme_dir = $theme['dir'];
			$theme_dir2 = "../".UC_THEMES_PATH."$theme_dir/";
			$delete = $udb->query("DELETE FROM `".UC_PREFIX."themes` WHERE `dir` = '$theme_dir' LIMIT 1");
			if($delete){
				$ucms->remove_dir($theme_dir2);
				if(THEMEDIR == $theme_dir){
					$udb->query("UPDATE `".UC_PREFIX."settings` SET `value` = '".UC_DEFAULT_THEME_NAME."' WHERE `id` = 9;");
					$udb->query("UPDATE `".UC_PREFIX."settings` SET `value` = '".UC_DEFAULT_THEME_DIR."' WHERE `id` = 8;");
					$udb->query("UPDATE `".UC_PREFIX."themes` SET `activated` = '1' WHERE `dir` = '".UC_DEFAULT_THEME_DIR."'");
				}
				$_SESSION['success_del'] = $theme_name;
				header("Location: ".UCMS_DIR."/admin/themes.php");
			}else
				echo "<div class=\"error\">Произошла ошибка.</div>";
		}else{
			header("Location: ".UCMS_DIR."/admin/themes.php");
		}
	}else{
		$_SESSION['no_del'] = true;
	}
}

function activate_theme($id){
	if(!$id){
		return false;
	}else{
		global $udb;
		$id = (int) $id;
		$theme = $udb->get_row("SELECT `name`, `dir` FROM `".UC_PREFIX."themes` WHERE `id` = '$id'");
		if($theme){
			$udb->query("UPDATE `".UC_PREFIX."themes` SET `activated` = 0");
			$upd = $udb->query("UPDATE `".UC_PREFIX."themes` SET `activated` = 1 WHERE `id` = '$id'");
			$theme_name = $theme['name'];
			$theme_dir = $theme['dir'];
			$udb->query("UPDATE `".UC_PREFIX."settings` SET `value` = '$theme_name' WHERE `id` = 9;");
			$udb->query("UPDATE `".UC_PREFIX."settings` SET `value` = '$theme_dir' WHERE `id` = 8;");
			if($upd){
				$_SESSION['success_act'] = $theme_name;
				header("Location: ".UCMS_DIR."/admin/themes.php");
			}
		}else{
			echo '<div class="error">Тема не найдена.</div>';
		}
	}
}

function add_theme($p){
	global $ucms, $udb;
	$template = $_FILES['themearch']['tmp_name'];
	if(!empty($template)){
		$name = preg_replace('#(.zip)#', '', $_FILES['themearch']['name']);
		$zip = new ZipArchive();
		$file = $_FILES['themearch']['tmp_name'];
		$res = $zip->open($file);
		if($res === TRUE){
			if(is_dir("../".UC_THEMES_PATH."$name")){
				$strs = file("../".UC_THEMES_PATH."$name/themeinfo.txt");
				$vers1 = preg_replace("#(Версия: )#", '', $strs[1]);
				$zip->extractTo("../".UC_THEMES_PATH."$name-1");
				$zip->close();
				if(file_exists("../".UC_THEMES_PATH."$name-1/themeinfo.txt")){
					$strs2 = file("../".UC_THEMES_PATH."$name-1/themeinfo.txt");
					$vers2 = preg_replace("#(Версия: )#", '', $strs2[1]);
					if($vers2 != $vers1){
						$ucms->remove_dir("../".UC_THEMES_PATH."$name");
						rename("../".UC_THEMES_PATH."$name-1", "../".UC_THEMES_PATH."$name");
					}else{
						$ucms->remove_dir("../".UC_THEMES_PATH."$name-1");
					}
				}else{
					$ucms->remove_dir("../".UC_THEMES_PATH."$name-1");
				}
			}else{
		  	 	$zip->extractTo("../".UC_THEMES_PATH."$name");
		   		$zip->close();
		   	}
		   	$themeinfo = "../".UC_THEMES_PATH."$name/themeinfo.txt";
			if(file_exists($themeinfo)){
				$strings = file($themeinfo);
				foreach($strings as $string){
					$theme_array[] = preg_replace("#(Название: |Версия: |Автор: |Сайт: |Описание: )#", '', $string);
				}
				$theme_array[] = $name;
				$theme_array[0] = $udb->parse_value(preg_replace("#(\\r\\n|\\r|\\n)#", "", $theme_array[0]));
				$theme_array[1] = $udb->parse_value(preg_replace("#(\\r\\n|\\r|\\n)#", "", $theme_array[1]));
				$theme_array[2] = $udb->parse_value(preg_replace("#(\\r\\n|\\r|\\n)#", "", $theme_array[2]));
				$theme_array[3] = $udb->parse_value(preg_replace("#(\\r\\n|\\r|\\n)#", "", $theme_array[3]));
				$theme_array[4] = $udb->parse_value(preg_replace("#(\\r\\n|\\r|\\n)#", "", $theme_array[4]));
				$test = $udb->get_row("SELECT `name`, `version` FROM `".UC_PREFIX."themes` WHERE `name` = '$theme_array[0]' LIMIT 1");
				$ver1 = (double) $test['version'];
				$ver2 = (double) $theme_array[1];
				if(!$test){
					$add = $udb->query("INSERT INTO `".UC_PREFIX."themes` VALUES(NULL, '$theme_array[0]', '$theme_array[1]', '$theme_array[2]', '$theme_array[3]', '$theme_array[4]', '$theme_array[5]', '0')");
					//echo "INSERT INTO `".UC_PREFIX."themes` VALUES(NULL, '$theme_array[0]', '$theme_array[1]', '$theme_array[2]', '$theme_array[3]', '$theme_array[4]', '$theme_array[5]', '0')";
					if($add){
						$_SESSION['success_add'] = $udb->parse_value($theme_array[0]);
						header("Location: ".UCMS_DIR."/admin/themes.php");
					}else{
						echo "<div class=\"error\">Произошла ошибка.</div>";
					}
				}else if($ver1 != $ver2){
					$upd = $udb->query("UPDATE `".UC_PREFIX."themes` SET `name` = '$theme_array[0]', `version` = '$theme_array[1]', `author` = '$theme_array[2]', `site` = '$theme_array[3]', `description` = '$theme_array[4]' WHERE `name` = '$test[name]'");
					if($upd){
						$_SESSION['success_upd'] = $udb->parse_value($theme_array[0]);
						header("Location: ".UCMS_DIR."/admin/themes.php");
					}else{
						echo "<div class=\"error\">Произошла ошибка.</div>";
					}
				}else{
					echo "<div class=\"error\">Ошибка: Данная тема уже установлена.</div>";
				}
			}else{
				echo "<div class=\"error\">Ошибка: В архиве нет поддерживаемой темы.</div>";
				$ucms->remove_dir("../".UC_THEMES_PATH."$name");
			}
		}else{
   			echo "<div class=\"error\">Произошла ошибка при открытии архива.</div>";
		}
	}
}

?>