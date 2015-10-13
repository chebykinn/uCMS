<?php
use uCMS\Core\Admin\ManagePage;
use uCMS\Core\Admin\ManageTable;
use uCMS\Core\Database\Query;
use uCMS\Core\Admin\ControlPanel;
use uCMS\Core\Extensions\Users\User;
use uCMS\Core\Settings;
use uCMS\Core\Tools;
$usersPage = new ManagePage();
$usersTable = new ManageTable();
$query = new Query("{users}");
$usersPage->doActions();

$limit = Settings::Get('per_page');
$users = (new User())->find(array('limit' => $limit));

$usersTable->addSelectColumn('manage users');
$usersTable->addColumn(tr('User'), true, 'manage users', '20%', true);
$usersTable->addColumn(tr('Group'), true, 'manage users', 0, true);
$usersTable->addColumn(tr('Email'), true, 'manage users');
$usersTable->addColumn(tr('IP'), true, 'manage users', '20%');
$usersTable->addColumn(tr('Last login'), true, 'manage users');
$usersTable->addColumn(tr('Last visit'), true, 'manage users');
$usersTable->addColumn(tr('Registered'), true, 'manage users');
foreach ($users as $user) {
	$usersTable->setInfo('idKey', $user->uid);
	$usersTable->setInfo('status', $user->status);
	$sessions = array();
	foreach ($user->sessions as $session) {
		$sessions[] = tr('IP: @s, Created: @s, ', $session->ip, Tools::FormatTime($session->created))
		.($session->updated == 0 ? tr('Saved') : tr('Updated: @s', Tools::FormatTime($session->updated)));
	}
	$sessions = implode("<br>", $sessions);
	$lastlogin = empty($user->lastlogin) ? tr("None") : $user->lastlogin;
	$visited = empty($user->visited) ? tr("None") : $user->visited;
	$usersTable->addRow( array(
		$user->name.'<br><div class="manage-actions">'.
		$usersTable->manageButtons(array(
			"Activate|Deactivate" => 'switch-status',
			"Edit" => 'edit',
			"Delete" => 'delete'
			)).'</div>',
		tr($user->group->name),
		$user->email,
		tr("Active Sessions:<br>@s<br>Registered: @s", $sessions, $user->ip),
		Tools::FormatTime($lastlogin),
		Tools::FormatTime($visited),
		Tools::FormatTime($user->created)
		)
	);

	//uid 	name 	password 	email 	status 	gid 	theme 	avatar 	language 	ip 	created 	lastlogin 	visited 
}
$usersTable->printTable();
?>