<?php
use uCMS\Core\Admin\ManagePage;
use uCMS\Core\Admin\ManageTable;
use uCMS\Core\Database\Query;
use uCMS\Core\Admin\ControlPanel;
use uCMS\Core\Extensions\Extension;
use uCMS\Core\Page;
$extensionsPage = new ManagePage();

$extensionsPage->addAction('add',     'manage extensions',  'Extension::add');
$extensionsPage->addAction('delete',  'manage extensions',  'Extension::delete');
$extensionsPage->addAction('enable',  'manage extensions',  'Extension::enable');
$extensionsPage->addAction('disable', 'manage extensions',  'Extension::disable');

$extensionsPage->doActions();

$extensionsTable = new ManageTable();
$extensions = Extension::GetAll();

$extensionsTable->setInfo('action', ControlPanel::GetAction(), true);
$extensionsTable->setInfo('idKey', 'name');

foreach ($extensions as $extension) {
	$dependencies = array();
	$extensionObject = Extension::Get($extension);
	if( empty($extensionObject) ){
		continue;
	}
	if( is_array(Extension::Get($extension)->getDependenciesList()) ){
		foreach (Extension::Get($extension)->getDependenciesList() as $dependency) {
			if( Extension::IsLoaded($dependency) ){ //?
				$dependencies[] = Extension::Get($dependency)->getInfo('displayname');
			}
		}
		$dependencies = implode(", ", $dependencies);
	}
	$style = Extension::IsLoaded($extension) ? "enabled" : "";
	$extensionsTable->addRow( 
		array(
		'manage'           => Extension::IsLoaded($extension) ? "@disable@" : "@enable@",
		'manageButtons'    => !Extension::IsDefault($extension) ? "#manage# | @delete@" : "",
		'dependenciesList' => !empty($dependencies) ? "<br>Depends on: #dependencies#" : "",
		'name'             => $extension, 
		'displayname'      => Extension::Get($extension)->getInfo('displayname'), 
		'description'      => Extension::Get($extension)->getInfo('description'), 
		'version'          => Extension::Get($extension)->getVersion(),
		'author'           => Extension::Get($extension)->getInfo('author'),
		'site'             => Extension::Get($extension)->getInfo('site'),
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