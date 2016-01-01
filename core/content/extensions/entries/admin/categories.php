<?php
use uCMS\Core\Admin\ManagePage;
use uCMS\Core\Admin\ManageTable;
use uCMS\Core\Setting;
use uCMS\Core\Tools;
use uCMS\Core\Extensions\Entries\Term;
$page = new ManagePage();
$table = new ManageTable();
$page->doActions();
$table->addSelectColumn('manage entries');
$table->setInfo("amount", Setting::Get('categories_amount'));

$table->addColumn($this->tr('Name'), true, 'manage entries', 0, true);
$table->addColumn($this->tr('Type'), true, 'manage entries', 0, true);
$table->addColumn($this->tr('Created'), true, 'manage entries', 0, true);
$limit = Setting::Get('per_page');
$categories = (new Term())->find(array('limit' => $limit));
foreach ($categories as $category) {
	$table->setInfo('idKey', $category->tid);
	$table->addRow(
		array(
			$this->tr($category->name).'<br>'.$this->tr($category->description).'<br><div class="manage-actions">'.
				$table->manageButtons(array(
				"Edit" => 'edit',
				"Delete" => 'delete'
				)).'</div>',
			$this->tr($category->type),
			Tools::FormatTime($category->created)
		)
	);
}
$table->printTable();
?>