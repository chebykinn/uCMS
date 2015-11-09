<?php
use uCMS\Core\Admin\ManagePage;
use uCMS\Core\Admin\ManageTable;
use uCMS\Core\Extensions\Menus\MenuLink;
use uCMS\Core\Settings;
use uCMS\Core\Tools;
$page = new ManagePage();
$table = new ManageTable();
$page->doActions();

$table->addSelectColumn('manage menu links');
$table->addColumn(tr('Title'), true, 'manage menu links', 0, true);
$table->addColumn(tr('Menu'), true, 'manage menu links', 0, true);

$limit = Settings::Get('per_page');
$links = (new MenuLink())->find(array('limit' => $limit));
foreach ($links as $link) {
	$table->setInfo("idKey", $link->lid);
	$table->addRow( array(
			$link->title,
			$link->menu,
		)
	);
}
$table->printTable();
?>