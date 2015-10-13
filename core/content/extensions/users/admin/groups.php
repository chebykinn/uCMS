<?php
use uCMS\Core\Admin\ManagePage;
use uCMS\Core\Admin\ManageTable;
use uCMS\Core\Database\Query;
use uCMS\Core\Admin\ControlPanel;
use uCMS\Core\Extensions\Users\Group;
use uCMS\Core\Settings;
$groupsPage = new ManagePage();
$groupsTable = new ManageTable();

$limit = Settings::Get('per_page');
$groups = (new Group())->find(array('limit' => $limit));
$groupsTable->addSelectColumn('manage users');
$groupsTable->addColumn(tr('Name'), true, 'manage users', 0, true);
$groupsTable->addColumn(tr('Permissions'), true, 'manage users', 0, true);
$groupsTable->addColumn(tr('Users count'), true, 'manage users');
foreach ($groups as $group) {
	$groupsTable->setInfo('idKey', $group->gid);
	$permissions = array();
	foreach ($group->permissions as $permission) {
		$permissions[] = $permission->name;
	}
	$groupsTable->addRow(
		array(
			tr($group->name)."<br><div class=\"manage-actions\">".$groupsTable->manageButtons(array(
				'Edit' => 'edit',
				'Delete' => 'delete'))."</div>",
			implode("<br>", $permissions),
			0
		)
	);

}
$groupsTable->printTable();
?>