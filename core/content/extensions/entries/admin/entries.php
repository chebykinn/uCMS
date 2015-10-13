<?php
use uCMS\Core\Admin\ManagePage;
use uCMS\Core\Admin\ManageTable;
use uCMS\Core\Database\Query;
use uCMS\Core\Settings;
use uCMS\Core\Extensions\Entries\Entry;
use uCMS\Core\Extensions\Entries\EntryType;
use uCMS\Core\Admin\ControlPanel;
$page = new ManagePage();
$table = new ManageTable();
$page->doActions();
$table->addSelectColumn('manage entries');
$table->addColumn(tr('Title'), true, 'manage entries', '20%', true);
$table->addColumn(tr('Type'), true, 'manage entries', true);
$table->addColumn(tr('Author'), true, 'manage entries', true);
$table->addColumn(tr('Terms'), true, 'manage entries');
$table->addColumn(tr('Comments'), true, 'manage entries');
$table->addColumn(tr('Created'), true, 'manage entries', '15%');

$sort['changed'] = 'desc';
$sort['eid'] = 'desc';
$limit = Settings::Get("entries_per_page");
//array('where' => array('column' => 'type', 'operator' => '=', 'value' => 'article')
$entries = (new Entry())->find(array('limit' => $limit, 'orders' => array('created' => 'desc')));

foreach ($entries as $entry) {
	$table->setInfo("idKey", $entry->eid);
	$table->setInfo('status', $entry->status);
	$table->addRow( array(
		'<a href="'.$entry->getLink().'">'.$entry->title.'</a><br><div class="manage-actions">'.
		$table->manageButtons(array(
			"Publish|Draft" => 'switch-status',
			"Edit" => 'edit',
			"Delete" => 'delete'
			)).'</div>',
		$entry->entryType->name,
		$entry->author->name,
		"",
		"",
		$entry->getDate()
		)
	);
}
$table->printTable();
?>