<?php
use uCMS\Core\Admin\ManagePage;
use uCMS\Core\Admin\ManageTable;
use uCMS\Core\Extensions\Menus\MenuLink;
use uCMS\Core\Settings;
use uCMS\Core\Tools;
$page = new ManagePage();
$table = new ManageTable();
$page->doActions();

$amount = (new MenuLink())->count();

$table->setInfo("amount", $amount);
$table->addSelectColumn('manage menu links');
$table->addColumn(tr('Title'), true, 'manage menu links', 0, true);
$table->addColumn(tr('Link'), true, 'manage menu links', 0, true);
$table->addColumn(tr('Menu'), true, 'manage menu links', 0, true);

$limit = Settings::Get('per_page');
$links = (new MenuLink())->find(array('limit' => $limit));
foreach ($links as $link) {
	$table->setInfo("idKey", $link->lid);
	$table->addRow( array(
			tr($link->title),
			$link->getLink(),
			$link->menu,
		)
	);
}
$table->printTable();
?>