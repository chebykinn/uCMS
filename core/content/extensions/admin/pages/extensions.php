<?php
use uCMS\Core\Admin\ManagePage;
use uCMS\Core\Admin\ManageTable;
use uCMS\Core\Database\Query;
use uCMS\Core\Admin\ControlPanel;
use uCMS\Core\Extensions\ExtensionHandler;
use uCMS\Core\Page;
use uCMS\Core\Setting;
$extensionsPage = new ManagePage();
$namespace = "\\uCMS\\Core\\Extensions\\";
$extensionsPage->addAction('add',     'manage extensions',  "{$namespace}ExtensionHandler::Add");
$extensionsPage->addAction('delete',  'manage extensions',  "{$namespace}ExtensionHandler::Delete");
$extensionsPage->addAction('enable',  'manage extensions',  "{$namespace}ExtensionHandler::Enable");
$extensionsPage->addAction('disable', 'manage extensions',  "{$namespace}ExtensionHandler::Disable");

$extensionsPage->doActions();

$extensionsTable = new ManageTable();
$extensions = ExtensionHandler::GetList();

$siteLink = Page::Home();
$extensionsTable->addSelectColumn('manage extensions');
$extensionsTable->addColumn($this->tr('Extension'), true, 'manage extensions', '20%', true);
$extensionsTable->addColumn($this->tr('Description'), true, 'manage extensions', 0, true );
$extensionsTable->setInfo("amount", count(ExtensionHandler::GetList()));
foreach ($extensions as $extension) {
	$dependencies = "";
	$extensionObject = ExtensionHandler::Get($extension);
	if( empty($extensionObject) ){
		continue;
	}
	if( is_array($extensionObject->getDependenciesList()) ){
		foreach ($extensionObject->getDependenciesList() as $dependency) {
			if( ExtensionHandler::IsLoaded($dependency) ){ //?
				$dependencies[] = $this->tr(ExtensionHandler::Get($dependency)->getInfo('displayname'));
			}
		}
		$dependencies = implode(", ", $dependencies);
	}
	$status = ExtensionHandler::IsLoaded($extension);
	$default = ExtensionHandler::IsDefault($extension);
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
			$this->tr($displayname)."<br><div class=\"manage-actions\">".
			($default ? "" : $extensionsTable->manageButtons())."</div>",
			$this->tr($description).$this->tr('<br><br>Version: @s | Author: @s | Site: <a href="@s">@s</a>',
			$version, $author, $site, $site, $dependencies).$this->tr($dependenciesMessage, $dependencies),

		),
		$style
	);
}

$extensionsTable->printTable();
?>