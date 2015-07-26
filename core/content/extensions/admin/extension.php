<?php
namespace uCMS\Core\Extensions\Admin;
use uCMS\Core\Admin\ControlPanel;
use uCMS\Core\Block;
class Admin extends \uCMS\Core\Extensions\Extension implements \uCMS\Core\Extensions\IExtension{
	public function onLoad(){
	}

	public function onInstall(){
		Block::Add("stats", "dashboard", "admin");
		
	}

	public function onUninstall(){

	}

	public function onShutdown(){

	}

	public function onAction($action){
	
	}

	public function onAdminAction($action){
		switch ($action) {
			case 'home':       $title = tr('Dashboard');       break;
			case 'settings':   $title = tr("Settings");        break; 
			case 'extensions': $title = tr("Extensions");      break; 
			case 'themes':     $title = tr("Themes");          break;
			case 'tools':      $title = tr("Tools");           break;
			case 'phpinfo':    $title = tr("PHP Information"); break;
			case 'journal':    $title = tr("System Journal");  break;
			default:           $title = tr("Dashboard");       break;
		}
		ControlPanel::SetTitle($title);
	}
}
?>