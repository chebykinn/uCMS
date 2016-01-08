<?php
use uCMS\Core\Admin\ManagePage;
use uCMS\Core\Admin\ManageTable;
use uCMS\Core\Database\Query;
use uCMS\Core\Admin\ControlPanel;
use uCMS\Core\Extensions\Users\User;
use uCMS\Core\Setting;
use uCMS\Core\uCMS;
$usersPage = new ManagePage();
$usersTable = new ManageTable();
$query = new Query("{users}");
$usersPage->doActions();

$limit = Setting::Get('per_page');
$users = (new User())->find(array('limit' => $limit));

$usersTable->addSelectColumn('manage users');
$usersTable->setInfo("amount", Setting::Get('users_amount'));

$usersTable->addColumn($this->tr('User'), true, 'manage users', '20%', true);
$usersTable->addColumn($this->tr('Group'), true, 'manage users', 0, true);
$usersTable->addColumn($this->tr('Email'), true, 'manage users');
$usersTable->addColumn($this->tr('IP'), true, 'manage users', '20%');
$usersTable->addColumn($this->tr('Last login'), true, 'manage users');
$usersTable->addColumn($this->tr('Last visit'), true, 'manage users');
$usersTable->addColumn($this->tr('Registered'), true, 'manage users');
foreach ($users as $user) {
	$usersTable->setInfo('idKey', $user->uid);
	$usersTable->setInfo('status', $user->status);
	$sessions = array();
	foreach ($user->sessions as $session) {
		$sessions[] = $this->tr('IP: @s, Created: @s, ', $session->ip, uCMS::FormatTime($session->created))
		.($session->updated == 0 ? $this->tr('Saved') : $this->tr('Updated: @s', uCMS::FormatTime($session->updated)));
	}
	$sessions = implode("<br>", $sessions);
	$lastlogin = empty($user->lastlogin) ? $this->tr("None") : uCMS::FormatTime($user->lastlogin);
	$visited = empty($user->visited) ? $this->tr("None") : uCMS::FormatTime($user->visited);
	$usersTable->addRow( array(
		$user->name.'<br><div class="manage-actions">'.
		$usersTable->manageButtons(array(
			"Activate|Deactivate" => 'switch-status',
			"Edit" => 'edit',
			"Delete" => 'delete'
			)).'</div>',
		$this->tr($user->group->name),
		$user->email,
		$this->tr("Active Sessions:<br>@s<br>Registered: @s", $sessions, $user->ip),
		$lastlogin,
		$visited,
		uCMS::FormatTime($user->created)
		)
	);

	//uid 	name 	password 	email 	status 	gid 	theme 	avatar 	language 	ip 	created 	lastlogin 	visited 
}
$usersTable->printTable();
?>