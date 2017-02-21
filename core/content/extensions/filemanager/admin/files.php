<?php
use uCMS\Core\Admin\ManagePage;
use uCMS\Core\Admin\ManageTable;
use uCMS\Core\Extensions\FileManager\File;
use uCMS\Core\Setting;
use uCMS\Core\Form;
use uCMS\Core\Page;
$page = new ManagePage();
$table = new ManageTable();
$page->doActions();

if( isset($_POST['check-uploads']) ){
	$files = scandir(ABSPATH.File::UPLOADS_PATH);	
	foreach($files as $file){
		$ffile = (new File())->emptyRow();
		$ffile->name = $file;
		$ffile->create();
	}
	Page::Refresh();
}

$uploadsForm = new Form('check-uploads', '', $this->tr('Check uploads'));
$uploadsForm->render();

$amount = (new File())->count();

$table->setInfo("amount", $amount);
$table->addSelectColumn('manage files');
$table->addColumn($this->tr('Name'), true, 'manage files', 0, true);
$table->addColumn($this->tr('Path'), true, 'manage files', 0, true);
$table->addColumn($this->tr('Type'), true, 'manage files', 0, true);
$table->addColumn($this->tr('Size'), true, 'manage files', 0, true);
$table->addColumn($this->tr('Uploaded By'), true, 'manage files', 0, true);
$table->addColumn($this->tr('Last modified'), true, 'manage files', 0, true);

$limit = Setting::Get('per_page');
$files = (new File())->find(array('limit' => $limit));
foreach ($files as $file) {
	$table->setInfo("idKey", $file->fid);
	$table->addRow( array(
			$file->name,
			$file->location,
			$file->type,
			$file->size,
			$file->user->name,
			$file->getDate()
		)
	);
}
$table->printTable();
?>
