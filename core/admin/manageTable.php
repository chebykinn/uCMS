<?php
class ManageTable{
	private $action;
	private $data;
	private $perpage;
	private $order;
	private $sort;
	private $columns;
	private $snippets;
	private $info;

	public function __construct(){
		// $this->owner = $owner;
		$this->data = array();
		$this->columns = array();
		$this->info = array();
		$this->setSnippets();
	}

	public function setSnippets(){
		//$editLink = $url->makeLink('admin', 'extensions/');
		$this->snippets['edit'] = '<a href="/admin/%action%/edit/%idKey%">'.tr('Edit').'</a>';
		$this->snippets['activate'] = '<a href="/admin/%action%/activate/%idKey%">'.tr('Activate').'</a>';
		$this->snippets['delete'] = '<a class="delete-button" href="/admin/%action%/delete/%idKey%">'.tr('Delete').'</a>';
		$this->snippets['manage'] = '<div class="manage-actions">'.$this->snippets['edit'].' | '.$this->snippets['delete'].'</div>';
		$this->snippets['select'] = '<input type="checkbox" name="select[]" value="%idKey%">';
		$this->snippets['selectAll'] = '<input type="checkbox" name="select_all">';
	}

	public function setInfo($field, $value, $raw = false){
		$this->info[$field] = $raw ? $value : "#$value#";
	}


	public function addRow($columnsAndValues){
		$this->data[] = $columnsAndValues;
	}

	public function addColumn($name, $sort, $permission, $content, $size = 0){
		// # - means data column, @ means snippet for content, % means info
		if( !empty($name) ){
			foreach ($this->snippets as $key => $value) {
				$name = str_replace("@$key@", $value, $name);
			}
			$this->columns[] = array("name" => $name, 'sort' => $sort, 'permission' => $permission, 'content' => $content, 'size' => $size);
		}
	}

	public function printTable($paginal = true, $class = 'manage'){
		$user = User::current();
		echo '<table class="'.$class.'">';
		echo '<tr>';
			foreach ($this->columns as $column) {
				if( !$user->can($column['permission']) ){
					continue;
				}
				$size = $column['size'] > 0 ? 'style="width: '.$column['size'].'"' : "";
				echo '<th '.$size.'>'.$column['name'].'</th>';
			}
		echo '</tr>';
		foreach ($this->data as $row) {
			echo '<tr>';
			foreach ($this->columns as $column) {
				if( !$user->can($column['permission']) ){
					continue;
				}
				echo '<td>';
				$content = $column['content'];
				foreach ($this->snippets as $key => $value) {
					$content = str_replace("@$key@", $value, $content);
				}
				foreach ($this->info as $field => $value) {
					$content = str_replace("%$field%", $value, $content);
				}
				foreach ($row as $key => $value) {
					$content = str_replace("#$key#", $value, $content);
				}
				echo $content;
				echo '</td>';
			}
			echo '</tr>';
		}
		echo '</table>';
	}

	public function printPages($amount, $displayNumber){

	}
}
?>