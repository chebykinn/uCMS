<?php
use uCMS\Core\Admin\ManagePage;
use uCMS\Core\Admin\ManageTable;
use uCMS\Core\Database\Query;
use uCMS\Core\Admin\ControlPanel;
use uCMS\Core\Extensions\Theme;
use uCMS\Core\Page;
$extensionsPage = new ManagePage();
$extensionsTable = new ManageTable();
$namespace = "\\uCMS\\Core\\Extensions\\";
$extensionsPage->addAction('add',     'manage themes',  "{$namespace}Theme::Add");
$extensionsPage->addAction('delete',  'manage themes',  "{$namespace}Theme::Delete");
$extensionsPage->addAction('enable',  'manage themes',  "{$namespace}Theme::Enable");
$extensionsPage->addAction('disable', 'manage themes',  "{$namespace}Theme::Disable");

$extensionsPage->doActions();

$extensions = Theme::GetAll();

$extensionsTable->addSelectColumn('manage themes');
$extensionsTable->addColumn(tr('Extension'), true, 'manage themes', '20%', true);
$extensionsTable->addColumn(tr('Description'), true, 'manage themes', 0, true );
foreach ($extensions as $extension) {
	$dependencies = "";
	$extensionObject = Theme::Get($extension);
	if( empty($extensionObject) ){
		continue;
	}
	// if( is_array($extensionObject->getDependenciesList()) ){
	// 	foreach ($extensionObject->getDependenciesList() as $dependency) {
	// 		if( Extension::IsLoaded($dependency) ){ //?
	// 			$dependencies[] = Extension::Get($dependency)->getInfo('displayname');
	// 		}
	// 	}
	// 	$dependencies = implode(", ", $dependencies);
	// }
	// $status = Extension::IsLoaded($extension);
	$default = Theme::IsDefault($extension);
	$style = $status ? "enabled" : "";
	$displayname = $extensionObject->getInfo('displayname');
	$description = $extensionObject->getInfo('description');
	$extensionsTable->setInfo('idKey', $extension);
	$extensionsTable->setInfo('status', $status);
	$version = $extensionObject->getVersion();
	$author = $extensionObject->getInfo('author');
	$site = $extensionObject->getInfo('site');
	$dependenciesMessage = !empty($dependencies) ? "<br>Depends on: @s" : "";
	$extensionsTable->addRow( 
		array(
			"$displayname<br><div class=\"manage-actions\">".
			($default ? "" : $extensionsTable->manageButtons())."</div>",
			tr($description).tr('<br><br>Version: @s | Author: @s | Site: <a href="@s">@s</a>',
			$version, $author, $site, $site, $dependencies).tr($dependenciesMessage, $dependencies),

		),
		$style
	);
}

$extensionsTable->printTable();
?>