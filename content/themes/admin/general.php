<?php
// Admin theme control center
require_once ADMIN_PATH.'adminPanel.php';
require_once ADMIN_PATH.'manageTable.php';
require_once ADMIN_PATH.'managePage.php';

AdminPanel::init();
$defaultActions = array('settings', 'extensions', 'phpinfo');
$defaultTitles = array('settings' => tr('Settings'), 'extensions' => tr('Extensions'), 'phpinfo' => tr('PHP Information'), '' => tr('Home'));
$currentAction = AdminPanel::getAction();
$adminActions = array_merge($defaultActions, Extensions::getUsedAdminActions());
if( !empty($currentAction) && !in_array($currentAction, $adminActions) ){
	error_404();
}
if( User::current()->can('access control panel') ){
	$adminTitle = $this->getTitle().' :: ';
	if( isset($defaultTitles[$currentAction]) ) $this->setTitle($adminTitle.$defaultTitles[$currentAction]);
	get_header();
	get_sidebar();
	switch ( $currentAction ) {
		case '':
			$this->loadTemplate('index');
		break;
	
		case 'settings':
			$extensionAction = URLManager::getKeyValue('settings');
			if( !empty($extensionAction) ){
				$settingsAction = 'settings/'.$extensionAction;
				$extension = Extensions::getExtensionByAdminAction($settingsAction);
				if( is_object($extension) ){
					$pageFile = $extension->getAdminPageFile($settingsAction);
				}
				if( !empty($extension) && !empty($pageFile) ){
					include $pageFile;
				}else{
					log_add(tr("Unable to load admin page for action: @s", $currentAction), UC_LOG_ERROR);
					URLManager::redirect(URLManager::makeLink('admin', 'settings', true));
				}
			}else{
				$this->loadTemplate('settings');
			}
		break;
	
		case 'extensions':
			$this->loadTemplate('extensions');
		break;

		case 'phpinfo':
			$this->loadTemplate('phpinfo');
		break;
		
		default:
			$extension = Extensions::getExtensionByAdminAction($currentAction);
			if( is_object($extension) ){
				$pageFile = $extension->getAdminPageFile($currentAction);
			}
			if( !empty($extension) && !empty($pageFile) ){
				include $pageFile;
			}else{
				log_add(tr("Unable to load admin page for action: @s", $currentAction), UC_LOG_ERROR);
				URLManager::redirect(URLManager::makeLink('admin'));
			}
		break;
	}
	get_footer();
}else{
	if( !User::current()->isLoggedIn() ){
		$this->loadTemplate('login');
	}else{
		$this->loadTemplate('accessdenied');
	}
}
?>