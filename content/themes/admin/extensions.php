<?php
$extensionsPage = new ManagePage();
echo '<h2>'.tr('Extensions').'</h2><br>';

Notification::ShowPending();

$extensionsPage->addAction('add',     'manage extensions',  'Extensions::add');
$extensionsPage->addAction('delete',  'manage extensions',  'Extensions::delete');
$extensionsPage->addAction('enable',  'manage extensions',  'Extensions::enable');
$extensionsPage->addAction('disable', 'manage extensions',  'Extensions::disable');

$extensionsPage->doActions();

$extensionsTable = new ManageTable();
$extensions = Extensions::GetAll();

$extensionsTable->setInfo('action', ControlPanel::GetAction(), true);
$extensionsTable->setInfo('idKey', 'name');

foreach ($extensions as $extension) {
	$dependencies = array();
	$extensionObject = Extensions::Get($extension);
	if( empty($extensionObject) ){
		continue;
	}
	if( is_array(Extensions::Get($extension)->getDependenciesList()) ){
		foreach (Extensions::Get($extension)->getDependenciesList() as $dependency) {
			if( Extensions::IsLoaded($dependency) ){ //?
				$dependencies[] = Extensions::Get($dependency)->getInfo('displayname');
			}
		}
		$dependencies = implode(", ", $dependencies);
	}
	$style = Extensions::IsLoaded($extension) ? "enabled" : "";
	$extensionsTable->addRow( 
		array(
		'manage'           => Extensions::IsLoaded($extension) ? "@disable@" : "@enable@",
		'manageButtons'    => !Extensions::IsDefault($extension) ? "#manage# | @delete@" : "",
		'dependenciesList' => !empty($dependencies) ? "<br>Depends on: #dependencies#" : "",
		'name'             => $extension, 
		'displayname'      => Extensions::Get($extension)->getInfo('displayname'), 
		'description'      => Extensions::Get($extension)->getInfo('description'), 
		'version'          => Extensions::Get($extension)->getVersion(),
		'author'           => Extensions::Get($extension)->getInfo('author'),
		'site'             => Extensions::Get($extension)->getInfo('site'),
		'dependencies'     => $dependencies
		),
		$style
	);
}

$siteLink = Page::FromAction('redirect', '#site#');
$extensionsTable->addSelectColumn('manage users');
$extensionsTable->addColumn(tr('Extension'),   true,  'manage extensions', '#displayname#<br><div class="manage-actions">#manageButtons#</div>', '20%', true);
$extensionsTable->addColumn(tr('Description'), true,  'manage extensions',
							tr('#description#
								<br><br>Version: #version# | Author: #author# | Site: <a href="@s">#site#</a>
								#dependenciesList#
								', $siteLink), 0, true );
$extensionsTable->printTable();
?>