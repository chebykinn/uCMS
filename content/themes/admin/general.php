<?php
// Admin theme control center
require_once ADMIN_PATH.'controlPanel.php';
require_once ADMIN_PATH.'manageTable.php';
require_once ADMIN_PATH.'managePage.php';

ControlPanel::Init();
$defaultTitles = array(
	''           => tr('Home'),
	'home'       => tr('Home'),
	'settings'   => tr("Settings"), 
	'extensions' => tr("Extensions"), 
	'themes'     => tr("Themes"), 
	'widgets'    => tr("Widgets"), 
	'tools'      => tr("Tools"),
	'phpinfo'    => tr("PHP Information"),
	'journal'    => tr("System Journal")
);
$defaultActions = array_keys($defaultTitles);
$currentAction = ControlPanel::GetAction();
$adminActions = array_merge($defaultActions, Extensions::GetUsedAdminActions());
if( !empty($currentAction) && !in_array($currentAction, $adminActions) ){
	error_404();
}
if( User::Current()->can('access control panel') ){
	$adminTitle = $this->getTitle().' :: ';
	if( isset($defaultTitles[$currentAction]) ) $this->setTitle($adminTitle.$defaultTitles[$currentAction]);
	get_header();
	get_sidebar();
	switch ( $currentAction ) {
		case '':
		case 'home':
			$this->loadTemplate('index');
		break;
	
		case 'settings':
			$extensionAction = URLManager::GetKeyValue('settings');
			if( !empty($extensionAction) ){
				$settingsAction = 'settings/'.$extensionAction;
				$extension = Extensions::GetExtensionByAdminAction($settingsAction);
				if( is_object($extension) ){
					$pageFile = $extension->getAdminPageFile($settingsAction);
				}
				if( !empty($extension) && !empty($pageFile) ){
					include $pageFile;
				}else{
					log_add(tr("Unable to load admin page for action: @s", $settingsAction), UC_LOG_ERROR);
					URLManager::Redirect(URLManager::MakeLink('admin', 'settings', true));
				}
			}else{
				$this->loadTemplate('settings');
			}
		break;
		
		default:
			if( in_array($currentAction, $defaultActions) ){
				$this->loadTemplate($currentAction);
			}else{
				$extension = Extensions::getExtensionByAdminAction($currentAction);
				if( is_object($extension) ){
					$pageFile = $extension->getAdminPageFile($currentAction);
				}
				if( !empty($extension) && !empty($pageFile) ){
					include $pageFile;
				}else{
					log_add(tr("Unable to load admin page for action: @s", $currentAction), UC_LOG_ERROR);
					URLManager::Redirect(URLManager::MakeLink('admin'));
				}
			}
		break;
	}
	get_footer();
}else{
	if( !User::Current()->isLoggedIn() ){
		$this->loadTemplate('login');
	}else{
		$this->loadTemplate('access_denied');
	}
}
?>