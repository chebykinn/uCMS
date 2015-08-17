<?php
use uCMS\Core\Debug;
use uCMS\Core\Admin\ManageTable;
$table = new ManageTable();

$permission = 'access control panel';
$table->setInfo('emptyMessage', tr('There are no log messages to display'));

$table->addColumn(tr('Type'), false, $permission, '10%', true);
$table->addColumn(tr('Message'), false, $permission, 0, true);
$table->addColumn(tr('Host'), false, $permission, '10%');
$table->addColumn(tr('Owner'), false, $permission, '10%');
$table->addColumn(tr('Date'), true, $permission, '15%', true);

$messages = Debug::GetLogMessages();

foreach ($messages as $message) {
	$table->addRow(array(
			$message['type'],
			$message['text'],
			$message['host'],
			$message['owner'],
			$message['date']
		)
	);
}

$table->printTable();
?>