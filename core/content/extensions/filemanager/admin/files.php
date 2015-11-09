<?php
use uCMS\Core\Admin\ManagePage;
use uCMS\Core\Admin\ManageTable;
use uCMS\Core\Extensions\FileManager\File;
use uCMS\Core\Settings;
use uCMS\Core\Tools;
$page = new ManagePage();
$table = new ManageTable();
$page->doActions();

$table->addSelectColumn('manage files');
$table->addColumn(tr('Name'), true, 'manage files', 0, true);
$table->addColumn(tr('Type'), true, 'manage files', 0, true);
$table->addColumn(tr('Size'), true, 'manage files', 0, true);
$table->addColumn(tr('Uploaded By'), true, 'manage files', 0, true);
$table->addColumn(tr('Last modified'), true, 'manage files', 0, true);

$limit = Settings::Get('per_page');
$files = (new File())->find(array('limit' => $limit));
foreach ($files as $file) {
	$table->setInfo("idKey", $file->fid);
	$table->addRow( array(
			$file->name,
			$file->type,
			$file->size,
			$file->user->name,
			Tools::FormatTime($file->changed)
		)
	);
}
$table->printTable();
?>