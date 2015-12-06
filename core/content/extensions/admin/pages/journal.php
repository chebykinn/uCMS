<?php
use uCMS\Core\Debug;
use uCMS\Core\Form;
use uCMS\Core\Page;
use uCMS\Core\Notification;
use uCMS\Core\Admin\ManageTable;

if( isset($_POST['clear-journal']) ){
	Debug::ClearLog();
	$msg = new Notification(tr('Journal was successfully cleared.'), Notification::SUCCESS);
	$msg->add();
	Page::Refresh();
}

$clearForm = new Form('clear-journal', '', tr('Clear journal'));
$clearForm->render();

$table = new ManageTable();
$messages = Debug::GetLogMessages();

$permission = 'access control panel';
$table->setInfo('emptyMessage', tr('There are no log messages to display'));
$table->setInfo("amount", count($messages));

$table->addColumn(tr('Type'), false, $permission, '10%', true);
$table->addColumn(tr('Message'), false, $permission, 0, true);
$table->addColumn(tr('Host'), false, $permission, '10%');
$table->addColumn(tr('Owner'), false, $permission, '10%');
$table->addColumn(tr('Date'), true, $permission, '15%', true);


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