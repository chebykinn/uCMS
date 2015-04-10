<?php
// Admin theme control center
if( User::current()->can('access control panel') ){
	include INCLUDE_PATH.'admin.php';
	$url = new URLManager();
	AdminPanel::init();
	$adminTitle = $this->getTitle().' :: ';
	//check permissions
	switch ( $url->getCurrentAdminAction() ) {
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
		
		default:
			if( !in_array($url->getCurrentAdminAction(), uCMS::getInstance()->getExtentions()->getUsedAdminActions()) ){
				error_404();
			}else{
				$this->loadTemplate('extention');
			}
		break;
	}
}else{
	if( !User::current()->isLoggedIn() ){
		$this->loadTemplate('login');
	}else{
		$this->loadTemplate('accessdenied');
	}
}
?>