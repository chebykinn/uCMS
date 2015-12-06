<?php
use uCMS\Core\Admin\ManagePage;
use uCMS\Core\Admin\ManageTable;
use uCMS\Core\Settings;
use uCMS\Core\Tools;
use uCMS\Core\Extensions\Entries\Term;
$page = new ManagePage();
$table = new ManageTable();
$page->doActions();
$table->addSelectColumn('manage entries');
$table->setInfo("amount", Settings::Get('categories_amount'));

$table->addColumn(tr('Name'), true, 'manage entries', 0, true);
$table->addColumn(tr('Type'), true, 'manage entries', 0, true);
$table->addColumn(tr('Created'), true, 'manage entries', 0, true);
$limit = Settings::Get('per_page');
$categories = (new Term())->find(array('limit' => $limit));
foreach ($categories as $category) {
	$table->setInfo('idKey', $category->tid);
	$table->addRow(
		array(
			tr($category->name).'<br>'.tr($category->description).'<br><div class="manage-actions">'.
				$table->manageButtons(array(
				"Edit" => 'edit',
				"Delete" => 'delete'
				)).'</div>',
			tr($category->type),
			Tools::FormatTime($category->created)
		)
	);
}
$table->printTable();
?>