<?php
// Admin theme control center
$defaultActions = array('settings', 'extentions', 'phpinfo');
$url = new URLManager();
$currentAction = $url->getCurrentAdminAction();
$adminActions = array_merge($defaultActions, Extentions::getUsedAdminActions());
if( !empty($currentAction) && !in_array($currentAction, $adminActions) ){
	error_404();
}
if( User::current()->can('access control panel') ){
	include INCLUDE_PATH.'admin.php';
	AdminPanel::init();
	$adminTitle = $this->getTitle().' :: ';
	//check permissions
	get_header();
	get_sidebar();
	switch ( $currentAction ) {
		case '':
			$this->setTitle($adminTitle.tr('Home'));
			$this->loadTemplate('index');
		break;
	
		case 'settings':
			$this->setTitle($adminTitle.tr('Settings'));
			$this->loadTemplate('settings');
		break;
	
		case 'extentions':
			$this->setTitle($adminTitle.tr('Extentions'));
			$this->loadTemplate('extentions');
		break;

		case 'phpinfo':
			$this->loadTemplate('phpinfo');
		break;
		
		default:
			$this->loadTemplate('extention');
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