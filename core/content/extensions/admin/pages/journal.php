<?php
use uCMS\Core\Debug;
use uCMS\Core\Form;
use uCMS\Core\Page;
use uCMS\Core\Notification;
use uCMS\Core\Admin\ManageTable;

if( isset($_POST['clear-journal']) ){
	try{
		Debug::ClearLog();
		$msg = new Notification($this->tr('Journal was successfully cleared.'), Notification::SUCCESS);
	}catch(\RuntimeException $e){
		$msg = new Notification($e->getMessage(), Notification::ERROR);
	}
	$msg->add();
	Page::Refresh();
}

$clearForm = new Form('clear-journal', '', $this->tr('Clear journal'));
$clearForm->render();

$table = new ManageTable();
$messages = Debug::GetLogMessages();

$permission = 'access control panel';
$table->setInfo('emptyMessage', $this->tr('There are no log messages to display'));
$table->setInfo("amount", Debug::GetLogLinesCount());
$table->addColumn($this->tr('Type'), false, $permission, '10%', true);
$table->addColumn($this->tr('Message'), false, $permission, 0, true);
$table->addColumn($this->tr('Host'), false, $permission, '10%');
$table->addColumn($this->tr('Sender'), false, $permission, '10%');
$table->addColumn($this->tr('Date'), true, $permission, '15%', true);


foreach ($messages as $message) {
	$table->addRow(array(
			$this->tr($message['type']),
			$message['text'],
			$message['host'],
			$message['sender'],
			$message['date']
		)
	);
}

$table->printTable();
?>