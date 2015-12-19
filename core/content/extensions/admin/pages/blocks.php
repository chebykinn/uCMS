<?php
use uCMS\Core\Admin\ManagePage;
use uCMS\Core\Admin\ManageTable;
use uCMS\Core\Database\Query;
use uCMS\Core\Admin\ControlPanel;
use uCMS\Core\Extensions\Theme;
use uCMS\Core\Extensions\ThemeHandler;
use uCMS\Core\Settings;
use uCMS\Core\Page;
use uCMS\Core\Tools;
use uCMS\Core\Notification;
use uCMS\Core\Block;
$page = new ManagePage();
$table = new ManageTable();

$page->doActions();

$blocks = (new Block())->find(['limit' => Settings::Get(Settings::PER_PAGE), 'orders' => ['theme' => 'ASC']]);

$table->addSelectColumn('manage blocks');
$table->addColumn(tr('Name'), true, 'manage blocks', 0, true);
$table->addColumn(tr('Theme'), true, 'manage blocks', 0, true);
$table->addColumn(tr('Region'), true, 'manage blocks', 0, true);
$table->setInfo("amount", (new Block)->count());
foreach ($blocks as $block) {
	$table->setInfo('idKey', $block->bid);
	$table->addRow(
		[
			$block->name,
			$block->theme,
			$block->region
		]
	);
}

$table->printTable();

?>