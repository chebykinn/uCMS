<?php
use uCMS\Core\Admin\ManagePage;
use uCMS\Core\Admin\ManageTable;
use uCMS\Core\Database\Query;
use uCMS\Core\Extensions\Entries\Entry;
use uCMS\Core\Admin\ControlPanel;
$page = new ManagePage();
$table = new ManageTable();
$page->doActions();
$query = new Query("SELECT 
	{entries}.eid,
	{entries}.uid AS author,
	{entries}.type,
	{entries}.status,
	{entries}.comments,
	{entries}.title,
	{entries}.alias,
	{entries}.content,
	{entries}.language,
	{entries}.created,
	{entries}.changed,

	{entry_access}.uid AS accessUid,
	{entry_access}.gid AS accessGid,
	{entry_access}.allow AS accessAllow,

	{entries}.type AS 'EntryType:type',
	{entry_types}.name AS 'EntryType:name',
	{entry_types}.comments AS 'EntryType:comments',
	{entry_types}.uid AS 'EntryType:uid',
	{entry_types}.gid AS 'EntryType:gid',
	{entry_types}.permission AS 'EntryType:permission',
	{entry_types}.terms AS 'EntryType:terms',

	{term_taxonomy}.tid AS 'Term:tid',
	{terms}.name AS 'Term:name',
	{terms}.type AS 'Term:type',

	{entries}.uid AS 'User:uid',
	{users}.name AS 'User:name'

	                 FROM {entries}
	                 LEFT JOIN {users} USING(uid)
                     LEFT JOIN {entry_access} USING(eid)
                     LEFT JOIN {entry_types} USING(type)
                     LEFT JOIN {term_taxonomy} USING(eid)
                     LEFT JOIN {terms} ON {term_taxonomy}.tid = {terms}.tid");
$entries = $query->execute();
$entry = Entry::FromArray($entries[0]);
$table->addSelectColumn('manage entries');
$table->addColumn(tr('Title'), true, 'manage entries', '20%', true);
$table->addColumn(tr('Type'), true, 'manage entries', '20%', true);
$table->addColumn(tr('Author'), true, 'manage entries', '20%', true);
$table->addColumn(tr('Terms'), true, 'manage entries', '20%');
$table->addColumn(tr('Comments'), true, 'manage entries', '20%');
$table->addColumn(tr('Created'), true, 'manage entries', true);
\uCMS\Core\Debug::PrintVar($entry);
foreach ($entries as $entry) {
	$entry = Entry::FromArray($entry);
	$table->setInfo("idKey", $entry->getID());
	$table->setInfo('status', $entry->getStatus());
	$table->addRow( array(
		$entry->getTitle().'<br><div class="manage-actions">'.
		$table->manageButtons(array(
			"Publish|Draft" => 'switch-status',
			"Edit" => 'edit',
			"Delete" => 'delete'
			)).'</div>',
		$entry->getType()->getName(),
		$entry->getAuthor()->getName(),
		"",
		"",
		$entry->getDate()
		)
	);
}
$table->printTable();
?>