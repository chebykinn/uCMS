<?php
$extensionsTable = new ManageTable();
$extensions = Settings::get('extensions');
$extensions = explode(',', $extensions);

$extensionsTable->setInfo('action', AdminPanel::getAction(), true);
$extensionsTable->setInfo('idKey', 'name');

foreach ($extensions as $extension) {
	$dependencies = array();
	if( is_array(Extensions::get($extension)->getDependenciesList()) ){
		foreach (Extensions::get($extension)->getDependenciesList() as $dependency) {
			if( Extensions::isLoaded($dependency) ){
				$dependencies[] = Extensions::get($dependency)->getInfo('displayname');
			}
		}
		$dependencies = implode(", ", $dependencies);
	}

	$extensionsTable->addRow( 
		array(
		'name'         => $extension, 
		'displayname'  => Extensions::get($extension)->getInfo('displayname'), 
		'description'  => Extensions::get($extension)->getInfo('description'), 
		'version'      => Extensions::get($extension)->getVersion(),
		'author'       => Extensions::get($extension)->getInfo('author'),
		'site'         => Extensions::get($extension)->getInfo('site'),
		'dependencies' => $dependencies
		) 
	);
}

$siteLink = URLManager::makeLink('redirect', '#site#');
$extensionsTable->addColumn("@selectAll@", true, 'manage extensions', '@select@', '10px');
$extensionsTable->addColumn(tr('Extension'), true, 'manage extensions', '#displayname#<br><div class="manage-actions">@activate@ | @delete@</div>', '20%');
$extensionsTable->addColumn(tr('Description'), true, 'manage extensions',
							tr('@s<br><br>Version: @s | Author: @s | Site: <a href="'.$siteLink.'">@s</a>', 
								'#description#', '#version#', '#author#', '#site#').
							tr('<br>Depends on: @s', '#dependencies#' ) );
$extensionsTable->printTable();
?>