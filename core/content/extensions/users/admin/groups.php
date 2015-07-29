<?php
use uCMS\Core\Admin\ManagePage;
use uCMS\Core\Admin\ManageTable;
use uCMS\Core\Database\Query;
use uCMS\Core\Admin\ControlPanel;
$groupsPage = new ManagePage();
$groupsTable = new ManageTable();

$query = new Query('{groups}');
$permissions = $query->select("`{groups}`.*, `{group_permissions}`.`name` AS `permission`", true)->left()->join('{group_permissions}')->using('gid')
		  ->orderBy('gid', 'asc')->limit(100)->execute();
$countsQuery = new Query('{users}');
$counts = $countsQuery->select("`gid`, COUNT(`uid`) AS `count`", true)->groupBy("gid")->orderBy('gid', 'asc')->limit(100)->execute();
$groups = array();
$lastGroup = "";
$i = -1;
foreach ($permissions as $permission) {
	// varDump($permission);
	if( $permission['gid'] != $lastGroup ){
		$i++;
		$groups[$i]['gid'] = $permission['gid'];
		$groups[$i]['name'] = $permission['name'];
		$groups[$i]['position'] = $permission['position'];
		$groups[$i]['count'] = 0;
		foreach ($counts as $count) {
			if( $count['gid'] == $permission['gid'] ){
				$groups[$i]['count'] = $count['count'];
				break;
			}
		}
	}
	$groups[$i]['permission'][] = $permission['permission'];
	$lastGroup = $permission['gid'];
}
$groupsTable->addSelectColumn('manage users');
$groupsTable->addColumn(tr('Name'), true, 'manage users', 0, true);
$groupsTable->addColumn(tr('Permissions'), true, 'manage users', 0, true);
$groupsTable->addColumn(tr('Users count'), true, 'manage users');
foreach ($groups as $group) {
	$groupsTable->setInfo('idKey', $group['gid']);
	$groupsTable->addRow(
		array(
			"{$group['name']}<br><div class=\"manage-actions\">".$groupsTable->manageButtons(array('Edit' => 'edit',
				'Delete' => 'delete'))."</div>",
			implode("<br>", $group['permission']),
			$group['count']
		)
	);

}
$groupsTable->printTable();
?>