<?php
namespace uCMS\Core\Admin;
class ManageTable{
	private $action;
	private $rows;
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
		$this->rows = array();
		$this->setSnippets();
	}

	public function setSnippets(){
		//$editLink = $url->makeLink('admin', 'extensions/');
		/** 
		* @todo change admin action to constant
		*/
		$this->setInfo('emptyMessage', tr('No elements'), true);
		$this->setInfo('notAllowed', tr("You don't have permissions to view this content"), true);
		$this->snippets['edit'] = '<a href="/admin/%action%/edit/%idKey%">'.tr('Edit').'</a>';
		$this->snippets['activate'] = '<a href="/admin/%action%/activate/%idKey%">'.tr('Activate').'</a>';
		$this->snippets['deactivate'] = '<a href="/admin/%action%/deactivate/%idKey%">'.tr('Deactivate').'</a>';
		$this->snippets['enable'] = '<a href="/admin/%action%/enable/%idKey%">'.tr('Enable').'</a>';
		$this->snippets['disable'] = '<a href="/admin/%action%/disable/%idKey%">'.tr('Disable').'</a>';
		$this->snippets['delete'] = '<a class="delete-button" href="/admin/%action%/delete/%idKey%">'.tr('Delete').'</a>';
		$this->snippets['manage'] = '<div class="manage-actions">'.$this->snippets['edit'].' | '.$this->snippets['delete'].'</div>';
		$this->snippets['select'] = '<input type="checkbox" name="select[]" value="%idKey%">';
		$this->snippets['selectAll'] = '<input type="checkbox" name="select-all">';
	}

	public function setInfo($field, $value, $raw = false){
		$this->info[$field] = $raw ? $value : "#$value#";
	}

	public function getInfo($field){
		if( isset($this->info[$field]) ) return $this->info[$field]; 
	}

	public function addRow($columnsAndValues, $rowStyleClass = ''){
		$id = count($this->rows)-1;
		$this->rows[$id]['data'] = $columnsAndValues;
		$this->rows[$id]['style'] = $rowStyleClass;
	}

	public function addColumn($name, $sort, $permission, $content, $size = 0, $alwaysShow = false){
		// # - means data column, @ means snippet for content, % means info
		if( !empty($name) ){
			foreach ($this->snippets as $key => $value) {
				$name = str_replace("@$key@", $value, $name);
			}
			$this->columns[] = array("name" => $name, 'sort' => $sort, 'permission' => $permission, 'content' => $content, 'size' => $size, 'show' => $alwaysShow);
		}
	}

	public function addSelectColumn($permission){
		$this->addColumn("@selectAll@", false, $permission, '@select@', '10px', true);
	}

	public function printTable($paginal = true, $class = 'manage'){
		$user = User::Current();
		$amountOfAllowed = 0;
		echo '<table class="'.$class.'">';
		echo '<tr>';
			foreach ($this->columns as $column) {
				if( !$user->can($column['permission']) ){
					continue;
				}
				$size = $column['size'] > 0 ? ' style="width: '.$column['size'].'"' : "";
				$hiddenColumn = $column['show'] ? ' class="always-show"' : '';
				echo '<th'.$size.$hiddenColumn.'>'.$column['name'].'</th>';
				$amountOfAllowed++;
			}
		echo '</tr>';

		if( !empty($this->rows) && $amountOfAllowed > 0 ){
			foreach ($this->rows as $row) {
				echo '<tr'.( !empty($row['style']) ? ' class="'.$row['style'].'"' : "" ).'>';
				foreach ($this->columns as $column) {
					if( !$user->can($column['permission']) ){
						continue;
					}
					$hiddenColumn = $column['show'] ? ' class="always-show"' : '';
					echo '<td'.$hiddenColumn.'>';
					$content = $column['content'];
					$depth = 3;
					for($i = 0; $i < $depth; $i++){
						foreach ($this->snippets as $key => $value) {
							$content = str_replace("@$key@", $value, $content);
						}
						foreach ($this->info as $field => $value) {
							$content = str_replace("%$field%", $value, $content);
						}
						foreach ($row['data'] as $key => $value) {
							$content = str_replace("#$key#", $value, $content);
						}
					}
					echo $content;
					echo '</td>';
				}
				echo '</tr>';
			}
		}else{
			$size = count($this->columns);
			$message = $amountOfAllowed > 0 ? $this->getInfo('emptyMessage') : $this->getInfo('notAllowed');
			echo '<tr><td colspan="'.$size.'">'.$message.'</td></tr>';
		}
		echo '</table>';
	}

	public function printPages($amount, $displayNumber){

	}
}
?>