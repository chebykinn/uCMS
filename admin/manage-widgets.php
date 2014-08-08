<?php
function manage_widgets(){
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
						delete_widget($id);
					}
					if(!isset($_SESSION['no_del'])){
						if (count($items) > 1) {
							$_SESSION['success_delm'] = true;
						}else 
							$_SESSION['success_del'] = true;
					}
 					header("Location: ".UCMS_DIR."/admin/widgets.php");
				break;
				
			}
		}
	}
		$call = $udb->num_rows("SELECT * FROM `".UC_PREFIX."widgets`");
		$user_id = $user->get_user_id();
		$perpage = 25;
		$columns = array('name','author');
		$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
		$orderby = isset($_GET['orderby']) ? in_array($_GET['orderby'], $columns) ? $_GET['orderby'] : 'id' : 'id';
		$order = (isset($_GET['order']) and $_GET['order'] == 'DESC') ? 'ASC' : 'DESC';
		if($page <= 0) $page = 1;
		$count = $udb->num_rows("SELECT * FROM `".UC_PREFIX."widgets` ORDER BY `$orderby` $order");
		$pages_count = 0;
		if($count != 0){ 
			$pages_count = ceil($count / $perpage); 
			if ($page > $pages_count):
				$page = $pages_count;
			endif; 
			$start_pos = ($page - 1) * $perpage;
			$sql = "SELECT * FROM `".UC_PREFIX."widgets` ORDER BY `$orderby` $order LIMIT $start_pos, $perpage";

		}else $sql  = "SELECT * FROM `".UC_PREFIX."widgets` WHERE `id` = '0'";
		$link1 = UCMS_DIR."/admin/widgets.php?orderby=title&amp;order=".$order.(isset($_GET['page']) ? "&amp;page=".$_GET['page'] : "");
		$link2 = UCMS_DIR."/admin/widgets.php?orderby=author&amp;order=".$order.(isset($_GET['page']) ? "&amp;page=".$_GET['page'] : "");
		$mark = $order == "ASC" ? '↑' : '↓';
		?>
		<b>Всего виджетов:</b> <?php echo $call; ?><br><br>
		<b>Сортировать по:</b> <a href="<?php echo $link1; ?>">Названию <?php echo $mark; ?></a> | <a href="<?php echo $link2; ?>">Автору <?php echo $mark; ?></a>
		<br><br>
		<b>Отметить все:</b> <input type="checkbox" name="select-all" value="1">
		<br><br>
		<form action="widgets.php" method="post">
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
		$widgets = $udb->get_rows($sql);
		if($widgets){
			for($i = 0; $i < count($widgets); $i+=5){
				echo "<tr>";
				for($j = 0; $j < 5; $j++){
					$id = $i + $j;
					if(isset($widgets[$id]['id'])){
						$status = $widgets[$id]['activated'] == 1 ? "Активирован" : "Установлен";
						?></p>
							<td <?php if($widgets[$id]['activated'] == 1) echo "style=\"border: 2px solid #0099FF;\""; ?>><p style="float:left;"><input type="checkbox" name="item[]" value="<?php echo $widgets[$id]['id']; ?>"></p><br>
								<?php
								echo '<ul>';
								echo "<li><span>Название: </span>".$widgets[$id]['name']."</li>";
								echo "<li><span>Версия: </span>".$widgets[$id]['version']."</li>";
								echo "<li><span>Автор: </span>".$widgets[$id]['author']."</li>";
								echo "<li><span>Сайт: </span><a target=\"_blank\" href=\"".$widgets[$id]['site']."\">".$widgets[$id]['site']."</a></li>";
								echo "<li><span>Описание: </span>".$widgets[$id]['description']."</li>";
								echo '</ul>';
								?><br>
								<span class="actions"><a class="install-button" href="<?php echo UCMS_DIR ?>/admin/widgets.php?action=activate&amp;id=<?php echo $widgets[$id]['id'];?>"><?php if($widgets[$id]['activated'] < 1){ ?>Включить<?php }else{ ?>Отключить<?php } ?></a> <a class="install-button" href="<?php echo UCMS_DIR ?>/admin/editor.php?type=widgets&amp;action=edit&amp;dir=<?php echo $widgets[$id]['dir']?>">Редактировать</a> <?php if($widgets[$id]['id'] > 9){ ?><a class="delete-button" href="<?php echo UCMS_DIR ?>/admin/widgets.php?action=delete&amp;id=<?php echo $widgets[$id]['id'];?>">Удалить</a><?php } ?></span>
							</td>
						<?php
					}
				}
				echo "</tr>";
			}
		}else{
			?>
			<tr>
				<td colspan="9" style="text-align:center;">Виджетов пока нет.</td>
			</tr>
			<?php
		}
			echo '</table></form>';
}

function delete_widget($id){
	global $udb, $ucms;
	if(!$id){
		return false; 
	}
	$id = (int) $id;
	if($id > 9){
		$widget = $udb->get_row("SELECT `name`, `dir` FROM `".UC_PREFIX."widgets` WHERE `id` = '$id'");
		if($widget){
			$widget_name = $widget['name'];
			$widget_dir = $widget['dir'];
			$widget_dir2 = "../".WIDGETS_PATH."$widget_dir/";
			$delete = $udb->query("DELETE FROM `".UC_PREFIX."widgets` WHERE `dir` = '$widget_dir' LIMIT 1");
			if($delete){
				$ucms->remove_dir($widget_dir2);
				$_SESSION['success_del'] = $widget_name;
				header("Location: ".UCMS_DIR."/admin/widgets.php");
			}else
				echo "<div class=\"error\">Произошла ошибка.</div>";
		}else{
			header("Location: ".UCMS_DIR."/admin/widgets.php");
		}
	}else{
		$_SESSION['no_del'] = true;
	}
}

function activate_widget($id){
	if(!$id){
		return false;
	}else{
		global $udb;
		$id = (int) $id;
		$widget = $udb->get_row("SELECT `name`, `dir`, `activated` FROM `".UC_PREFIX."widgets` WHERE `id` = '$id'");
		if($widget){
			$widget_name = $widget['name'];
			if($widget['activated'] == 0){
				$upd = $udb->query("UPDATE `".UC_PREFIX."widgets` SET `activated` = 1 WHERE `id` = '$id'");
				$_SESSION['success_act'] = $widget_name;
			}elseif($widget['activated'] == 1){
				$upd = $udb->query("UPDATE `".UC_PREFIX."widgets` SET `activated` = 0 WHERE `id` = '$id'");
				$_SESSION['success_deact'] = $widget_name;
			}

			if($upd){
				header("Location: ".UCMS_DIR."/admin/widgets.php");
			}else
				echo "<div class=\"error\">Произошла ошибка.</div>";
		}else{
			echo '<div class="error">Тема не найдена.</div>';
		}
	}
}

function add_widget($p){
	global $ucms, $udb;
	$template = $_FILES['widgetarch']['tmp_name'];
	if(!empty($template)){
		$name = preg_replace('#(.zip)#', '', $_FILES['widgetarch']['name']);
		$zip = new ZipArchive();
		$file = $_FILES['widgetarch']['tmp_name'];
		$res = $zip->open($file);
		if($res === TRUE){
			if(is_dir("../".WIDGETS_PATH."$name")){
				$strs = file("../".WIDGETS_PATH."$name/widgetinfo.txt");
				$vers1 = preg_replace("#(Версия: )#", '', $strs[1]);
				$zip->extractTo("../".WIDGETS_PATH."$name-1");
				$zip->close();
				if(file_exists("../".WIDGETS_PATH."$name-1/widgetinfo.txt")){
					$strs2 = file("../".WIDGETS_PATH."$name-1/widgetinfo.txt");
					$vers2 = preg_replace("#(Версия: )#", '', $strs2[1]);
					if($vers2 != $vers1){
						$ucms->remove_dir("../".WIDGETS_PATH."$name");
						rename("../".WIDGETS_PATH."$name-1", "../".WIDGETS_PATH."$name");
					}else{
						$ucms->remove_dir("../".WIDGETS_PATH."$name-1");
					}
				}else{
					$ucms->remove_dir("../".WIDGETS_PATH."$name-1");
				}
			}else{
		  	 	$zip->extractTo("../".WIDGETS_PATH."$name");
		   		$zip->close();
		   	}
		   	$widgetinfo = "../".WIDGETS_PATH."$name/widgetinfo.txt";
			if(file_exists($widgetinfo)){
				$strings = file($widgetinfo);
				foreach($strings as $string){
					$widget_array[] = preg_replace("#(Название: |Версия: |Автор: |Сайт: |Описание: )#", '', $string);
				}
				$widget_array[] = $name;
				$widget_array[0] = $udb->parse_value(preg_replace("#(\\r\\n|\\r|\\n)#", "", $widget_array[0]));
				$widget_array[1] = $udb->parse_value(preg_replace("#(\\r\\n|\\r|\\n)#", "", $widget_array[1]));
				$widget_array[2] = $udb->parse_value(preg_replace("#(\\r\\n|\\r|\\n)#", "", $widget_array[2]));
				$widget_array[3] = $udb->parse_value(preg_replace("#(\\r\\n|\\r|\\n)#", "", $widget_array[3]));
				$widget_array[4] = $udb->parse_value(preg_replace("#(\\r\\n|\\r|\\n)#", "", $widget_array[4]));
				$test = $udb->get_row("SELECT `name`, `version` FROM `".UC_PREFIX."widgets` WHERE `name` = '$widget_array[0]' LIMIT 1");
				$ver1 = (double) $test['version'];
				$ver2 = (double) $widget_array[1];
				if(!$test){
					$add = $udb->query("INSERT INTO `".UC_PREFIX."widgets` VALUES(NULL, '$widget_array[0]', '$widget_array[1]', '$widget_array[2]', '$widget_array[3]', '$widget_array[4]', '$widget_array[5]', '0')");
					//echo "INSERT INTO `".UC_PREFIX."widgets` VALUES(NULL, '$widget_array[0]', '$widget_array[1]', '$widget_array[2]', '$widget_array[3]', '$widget_array[4]', '$widget_array[5]', '0')";
					if($add){
						$_SESSION['success_add'] = $udb->parse_value($widget_array[0]);
						header("Location: ".UCMS_DIR."/admin/widgets.php");
					}else{
						echo "<div class=\"error\">Произошла ошибка.</div>";
					}
				}else if($ver1 != $ver2){
					$upd = $udb->query("UPDATE `".UC_PREFIX."widgets` SET `name` = '$widget_array[0]', `version` = '$widget_array[1]', `author` = '$widget_array[2]', `site` = '$widget_array[3]', `description` = '$widget_array[4]' WHERE `name` = '$test[name]'");
					if($upd){
						$_SESSION['success_upd'] = $udb->parse_value($widget_array[0]);
						header("Location: ".UCMS_DIR."/admin/widgets.php");
					}else{
						echo "<div class=\"error\">Произошла ошибка.</div>";
					}
				}else{
					echo "<div class=\"error\">Ошибка: Данный виджет уже установлен.</div>";
				}
			}else{
				echo "<div class=\"error\">Ошибка: В архиве нет поддерживаемого виджета.</div>";
				$ucms->remove_dir("../".WIDGETS_PATH."$name");
			}
		}else{
   			echo "<div class=\"error\">Произошла ошибка при открытии архива.</div>";
		}
	}
}
?>