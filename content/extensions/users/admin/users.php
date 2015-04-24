<?php
$usersTable = new ManageTable();
$query = new Query("{users}");
$users = $query->select('*', true)->execute();
$usersTable->setInfo('action', AdminPanel::getAction(), true);
$usersTable->setInfo('idKey', 'uid');

foreach ($users as $user) {

	$usersTable->addRow( 
		array(
		'uid'    => $user['uid'],
		'name'   => $user['name'],
		'email'  => $user['email'],
		'status' => $user['status'],
		'gid'    => $user['gid'],
		'avatar' => $user['avatar']
		) 
	);
	//uid 	name 	password 	email 	status 	gid 	theme 	avatar 	language 	ip 	created 	lastlogin 	visited 
}
$usersTable->addColumn("@selectAll@", true, 'manage users', '@select@', '10px');
$usersTable->addColumn(tr('User'), true, 'manage users', '#name#<br><div class="manage-actions">@activate@ | @edit@ | @delete@</div>', '20%');
$usersTable->addColumn(tr('Group'), true, 'manage users', '#gid#');
$usersTable->printTable();
?>