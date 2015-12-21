<?php
use uCMS\Core\Admin\ManagePage;
use uCMS\Core\Admin\ManageTable;
use uCMS\Core\Extensions\Menus\MenuLink;
use uCMS\Core\Extensions\Menus\Menu;
use uCMS\Core\Settings;
use uCMS\Core\Tools;
$page = new ManagePage();
$table = new ManageTable();
$page->doActions();

$amount = (new Menu())->count();

$table->setInfo("amount", $amount);
$table->addSelectColumn('manage menu links');
$table->addColumn(tr('Title'), true, 'manage menu links', 0, true);
$table->addColumn(tr('Links'), true, 'manage menu links', 0, true);

$limit = Settings::Get('per_page');
$menus = (new Menu())->find(array('limit' => $limit));
foreach ($menus as $menu) {
	$table->setInfo("idKey", $menu->lid);
	ob_start();
	$menu->render();
	$links = ob_get_clean();
	$table->addRow( array(
			tr($menu->title).'<br>'.tr($menu->description),
			$links
		)
	);
}
$table->printTable();
?>