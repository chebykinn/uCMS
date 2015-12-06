<?php
namespace uCMS\Core\Admin;
use uCMS\Core\Extensions\Users\User;
use uCMS\Core\Page;
use uCMS\Core\Form;
class ManageTable{
	private $action;
	private $rows;
	private $perpage;
	private $order;
	private $sort;
	private $columns;
	private $canSelect = false;
	private $info;
	private $form;

	public function __construct(){
		// $this->owner = $owner;
		$this->data = array();
		$this->columns = array();
		$this->info = array();
		$this->rows = array();
		$this->info['emptyMessage'] = tr('No elements to display');
		$this->info['notAllowed'] = tr("You don't have permissions to view this content");
		$this->form = new Form("tableForm");
	}

	public function addFilter($column){

	}

	public function addAction($action){

	}

	public function manageButtons($list = array('Enable|Disable' => 'switch-status',
		'Edit' => 'edit', 'Delete' => 'delete'), $delimeter = " | "){
		$buttons = array('switch-status', 'edit', 'delete');
		$content = "";
		$action = "";
		if ( is_array($list) ){
			$i = 0;
			$size = count($list);
			foreach ($list as $name => $button) {
				$i++;
				if( !in_array($button, $buttons) ){
					continue;
				}
				$action = $button;
				if( $button == 'switch-status' ){
					$status = (bool) $this->getInfo('status');
					$action = $status ? 'disable' : 'enable';
					$names = explode('|', $name);
					if( isset($names[0]) && isset($names[1]) ){
						$name = $names[(int)$status];
					}
				}
				$id = $this->getInfo('idKey');
				$link = Page::ControlPanel(ControlPanel::GetAction()."/$action/$id");
				$del = ($i === $size) ? "" : $delimeter;
				$content .= "<a class=\"$action-button\" href=\"$link\">".$name."</a>$del\n";
			}
		}
		return $content;
	}

	public function setInfo($field, $value){
		$this->info[$field] = $value;
	}

	public function getInfo($field){
		if( isset($this->info[$field]) ) return $this->info[$field]; 
	}

	public function addRow($data, $rowStyleClass = ''){
		$id = count($this->rows)-1;
		if( $this->canSelect ){
			array_unshift($data, 
				'<input type="checkbox" name="select[]" value="'.$this->getInfo('idKey').'">');
		}
		$this->rows[$id]['data'] = $data;
		$this->rows[$id]['style'] = $rowStyleClass;
	}

	public function addColumn($name, $sort, $permission, $size = 0, $alwaysShow = false){
		if( !empty($name) ){
			$this->columns[] = array("name" => $name, 'sort' => $sort, 'permission' => $permission, 'size' => $size, 'show' => $alwaysShow);
		}
	}

	public function addSelectColumn($permission){
		$this->addColumn('<input type="checkbox" name="select-all">', false, $permission, '10px', true);
		$this->canSelect = true;
	}

	public function printTable($paginal = true, $class = 'manage'){
		$user = User::Current();
		$amountOfAllowed = 0;
		echo '<div class="manage-table">';
		echo '<span class="label">Amount:</span> '.intval($this->getInfo('amount'));
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
				$i = 0;
				foreach ($this->columns as $column) {
					if( !$user->can($column['permission']) ){
						continue;
					}
					$hiddenColumn = $column['show'] ? ' class="always-show"' : '';
					echo '<td'.$hiddenColumn.'>';
					if( isset($row['data'][$i]) ){
						echo $row['data'][$i];
					}
					echo '</td>';
					$i++;
				}
				echo '</tr>';
			}
		}else{
			$size = count($this->columns);
			$message = $amountOfAllowed > 0 ? $this->getInfo('emptyMessage') : $this->getInfo('notAllowed');
			echo '<tr><td colspan="'.$size.'">'.$message.'</td></tr>';
		}
		echo '</table>';
		echo '</div>';
	}

	public function printPages($amount, $displayNumber){

	}
}
?>