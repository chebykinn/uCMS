<?php
use uCMS\Core\Admin\ManagePage;
use uCMS\Core\Admin\ManageTable;
use uCMS\Core\Database\Query;
use uCMS\Core\Admin\ControlPanel;
use uCMS\Core\Extensions\Theme;
use uCMS\Core\Extensions\ThemeHandler;
use uCMS\Core\Settings;
use uCMS\Core\Page;
use uCMS\Core\Tools;
use uCMS\Core\Notification;
$page = new ManagePage();
$table = new ManageTable();
$namespace = "\\uCMS\\Core\\Extensions\\";
$page->addAction('add',     'manage themes',  "{$namespace}Theme::Add");
$page->addAction('delete',  'manage themes',  "{$namespace}Theme::Delete");
$page->addAction('enable',  'manage themes',  "enable");
$page->addAction('disable', 'manage themes',  "{$namespace}Theme::Disable");

$page->doActions();

$themes = ThemeHandler::GetList();

$table->addSelectColumn('manage themes');
$table->addColumn(tr('Theme'), true, 'manage themes', '20%', true);
$table->addColumn(tr('Description'), true, 'manage themes', 0, true );
$table->setInfo("amount", count(ThemeHandler::GetList()));
foreach ($themes as $theme) {
	$dependencies = "";
	try{
		$themeObject = new Theme($theme);
		
	}catch(\Exception $e){
		continue;
	}
	$default = ThemeHandler::IsDefault($theme);
	$style = $default ? "enabled" : "";
	$displayname = tr($themeObject->getInfo('displayname'));
	$description = tr($themeObject->getInfo('description'));
	$table->setInfo('idKey', $theme);
	$table->setInfo('status', ThemeHandler::IsCurrent($theme));
	$version = $themeObject->getVersion();
	$author = $themeObject->getInfo('author');
	$site = $themeObject->getInfo('site');
	$dependenciesMessage = !empty($dependencies) ? "<br>Depends on: @s" : "";
	$table->addRow( 
		array(
			"$displayname<br><div class=\"manage-actions\">".
			$table->manageButtons()."</div>",
			tr($description).tr('<br><br>Version: @s | Author: @s | Site: <a href="@s">@s</a>',
			$version, $author, $site, $site, $dependencies).tr($dependenciesMessage, $dependencies),

		),
		$style
	);
}

$table->printTable();

function enable($name){
	if( !ThemeHandler::IsExists($name) ){
		$error = new Notification(tr("Error: There is no such theme."));
		$error->add();
		return false;
	}
	try{
		$theme = new Theme($name);
	}catch(\Exception $e){
		return false;
	}
	$displayName = tr($theme->getInfo('displayname'));
	ThemeHandler::ChangeTheme($name);
	$success = new Notification(tr("Theme \"@s\" was successfully activated.",  $displayName));
	$success->add();
	return true;
}
?>