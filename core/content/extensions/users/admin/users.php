<?php
use uCMS\Core\Admin\ManagePage;
use uCMS\Core\Admin\ManageTable;
use uCMS\Core\Database\Query;
use uCMS\Core\Admin\ControlPanel;
$usersPage = new ManagePage();
$usersTable = new ManageTable();
$query = new Query("{users}");
$usersPage->doActions();
$users = $query->select('DISTINCT `{users}`.*, `{groups}`.`name` as `group`, `{sessions}`.`ip` AS `sip`', true)
         ->left()->join('{groups}')->  using('gid')
         ->left()->join('{sessions}')->using('uid')
         ->limit(100)
         ->execute();
/**
* @todo join query
*/
$usersTable->addSelectColumn('manage users');
$usersTable->addColumn(tr('User'), true, 'manage users', '20%', true);
$usersTable->addColumn(tr('Group'), true, 'manage users', 0, true);
$usersTable->addColumn(tr('Email'), true, 'manage users');
$usersTable->addColumn(tr('IP'), true, 'manage users');
$usersTable->addColumn(tr('Last login'), true, 'manage users');
$usersTable->addColumn(tr('Last visit'), true, 'manage users');
$usersTable->addColumn(tr('Registered'), true, 'manage users');
foreach ($users as $user) {
	$usersTable->setInfo('idKey', $user['uid']);
	$usersTable->setInfo('status', $user['status']);
	$lastloginIP = empty($user['sip']) ? tr("None") : $user['sip'];
	$lastlogin = empty($user['lastlogin']) ? tr("None") : $user['lastlogin'];
	$visited = empty($user['visited']) ? tr("None") : $user['visited'];
	$usersTable->addRow( array(
		$user['name'].'<br><div class="manage-actions">'.
		$usersTable->manageButtons(array(
			"Activate|Deactivate" => 'switch-status',
			"Edit" => 'edit',
			"Delete" => 'delete'
			)).'</div>',
		$user['group'],
		$user['email'],
		tr("Last login: @s<br>Registered: @s", $lastloginIP, $user['ip']),
		$lastlogin,
		$visited,
		$user['created']
		)
	);

	//uid 	name 	password 	email 	status 	gid 	theme 	avatar 	language 	ip 	created 	lastlogin 	visited 
}
$usersTable->printTable();
?>