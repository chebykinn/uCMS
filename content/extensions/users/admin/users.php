<?php
$usersPage = new ManagePage();
$usersTable = new ManageTable();
$query = new Query("{users}");

$usersPage->showNotifications();
$usersPage->doActions();
$users = $query->select('DISTINCT `{users}`.*, `{groups}`.`name`, `{sessions}`.`ip` AS `sip`', true)
         ->left()->join('{groups}')->  using('gid')
         ->left()->join('{sessions}')->using('uid')
         ->limit(100)
         ->execute();
$usersTable->setInfo('action', ControlPanel::getAction(), true);
$usersTable->setInfo('idKey', 'uid');
/**
* @todo join query
*/
foreach ($users as $user) {
	$usersTable->addRow( 
		array(
		'manage'         => $user['status'] == 1 ? "@activate@" : "@deactivate@",
		'deleteButton'   => $user['gid']    != 1 ? "| @delete@" : "",
		'uid'            => $user['uid'],
		'name'           => $user['name'],
		'email'          => $user['email'],
		'status'         => $user['status'],
		'gid'            => $user['gid'],
		'groupName'      => $user['name'],
		'avatar'         => $user['avatar'],
		'registrationIP' => $user['ip'],
		'lastloginIP'    => empty($user['sip']) ? tr("None") : $user['sip'],
		'created'        => $user['created'],
		'lastlogin'      => empty($user['lastlogin']) ? tr("None") : $user['lastlogin'],
		'visited'        => empty($user['visited']) ? tr("None") : $user['visited']
		) 
	);
	//uid 	name 	password 	email 	status 	gid 	theme 	avatar 	language 	ip 	created 	lastlogin 	visited 
}
$usersTable->addSelectColumn('manage users');
$usersTable->addColumn(tr('User'),       true, 'manage users', '#name#<br><div class="manage-actions">#manage# | @edit@ #deleteButton#</div>', '20%', true);
$usersTable->addColumn(tr('Group'),      true, 'manage users', '#groupName#', 0, true);
$usersTable->addColumn(tr('Email'),      true, 'manage users', '#email#');
$usersTable->addColumn(tr('IP'),         true, 'manage users', tr("Last login: #lastloginIP#<br> Registered: #registrationIP#"));
$usersTable->addColumn(tr('Last login'), true, 'manage users', "#lastlogin#");
$usersTable->addColumn(tr('Last visit'), true, 'manage users', "#visited#");
$usersTable->addColumn(tr('Registered'), true, 'manage users', "#created#");
$usersTable->printTable();
?>