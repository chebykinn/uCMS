<?php
use uCMS\Core\Admin\ManagePage;
use uCMS\Core\Admin\ManageTable;
use uCMS\Core\Extensions\Menus\MenuLink;
use uCMS\Core\Extensions\Menus\Menu;
use uCMS\Core\Setting;
$page = new ManagePage();
$table = new ManageTable();
$page->doActions();

$amount = (new Menu())->count();

$table->setInfo("amount", $amount);
$table->addSelectColumn('manage menu links');
$table->addColumn($this->tr('Title'), true, 'manage menu links', 0, true);
$table->addColumn($this->tr('Links'), true, 'manage menu links', 0, true);

$limit = Setting::Get('per_page');
$menus = (new Menu())->find(array('limit' => $limit));
foreach ($menus as $menu) {
	$table->setInfo("idKey", $menu->lid);
	ob_start();
	$menu->render();
	$links = ob_get_clean();
	$table->addRow( array(
			$this->tr($menu->title).'<br>'.$this->tr($menu->description),
			$links
		)
	);
}
$table->printTable();
?>