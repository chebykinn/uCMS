<?php
class uWidgets{

	function get_widgets_count(){
		global $udb;
		$num = $udb->num_rows("SELECT * FROM `".UC_PREFIX."widgets`");
		return $num;
	}

	function get($column, $widget_mark){
		global $udb;
		$widget_mark = $udb->parse_value($widget_mark);
		$columns = array('id', 'name', 'version', 'author', 'site', 'description', 'dir', 'activated');
		if(!in_array($column, $columns)){
			return false;
		}else{
			$widget = $udb->get_row("SELECT `$column` FROM `".UC_PREFIX."widgets` WHERE `dir` = '$widget_mark' or `id` = '$widget_mark'");	
			if($widget){
				return $widget[0];
			}else{
				return false;
			}
		}
	}

	function load($widget_mark){ //id
		global $ucms, $user, $udb, $pm, $url, $url_all, $action, $tables, $months;
		$widget_mark = $udb->parse_value($widget_mark);
		$widget_file = ABSPATH.WIDGETS_PATH.$widget_mark.'/index.php';
		$widget_data = ABSPATH.WIDGETS_PATH.$widget_mark.'/widgetinfo.txt';
		if(file_exists($widget_file) and file_exists($widget_data)){
			include $widget_file;
			return true;
		}else{
			echo "<br>Виджет не найден.<br>";
			return false;
		}
	}
}
?>