<?php
namespace uCMS\Core\Extensions\Admin;
use uCMS\Core\Admin\ControlPanel;
use uCMS\Core\Block;
use uCMS\Core\Settings;
use uCMS\Core\Extensions\Users\Permission;
class Admin extends \uCMS\Core\Extensions\Extension implements \uCMS\Core\Extensions\IExtension{
	public function onLoad(){
		Permission::Register('access site', tr('Access Site'), 'Allow user to view site pages.');
		Permission::Register('access site in maintenance mode', tr('Access Site in maintenance mode.'), 'Allow user visit site in maintenance mode.');
		Permission::Register('access control panel', tr('Access Control Panel'), 'Allow user to view administration panel.');
		Permission::Register('manage extensions', tr('Manage Extensions'), 'Allow user to add, edit and delete extensions.');
		Permission::Register('manage themes', tr('Manage Themes'), 'Allow user to add, edit and delete themes.');
		Permission::Register('update core settings', tr('Update Core Settings'), 'Allow user to change core settings using control panel.');
	}

	public function onInstall($stage){
		Block::Add("last-added", "dashboard", "admin");
		Block::Add("stats", "dashboard", "admin");
		Block::Add("quick-actions", "dashboard", "admin");
		$defaultModels = array(
			'Articles' => array('owner' => 'entries', 'name' => '\\uCMS\\Core\\Extensions\\Entries\\Entry', 'conditions' => array('type' => 'article'), 'template' => 'templates/last.php'),
			'Comments' => array('owner' => 'comments', 'name' => '\\uCMS\\Core\\Extensions\\Comments\\Comment', 'conditions' => array(), 'template' => 'templates/last.php'),
			'Pages' => array('owner' => 'entries', 'name' => '\\uCMS\\Core\\Extensions\\Entries\\Entry', 'conditions' => array('type' => 'page'), 'template' => 'templates/last.php'),
			'Users' => array('owner' => 'users', 'name' => '\\uCMS\\Core\\Extensions\\Users\\User', 'conditions' => array(), 'template' => 'templates/last.php')
		);
		Settings::Add('last_added_models', json_encode($defaultModels));
		Settings::Add('last_added_limit', 10);
		if( $stage === 'check' ){

		}else{
			
		}
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
			case 'settings':
				if ( isset($_POST['settings']) ){
					ControlPanel::UpdateSettings();
				}
				$title = tr("Settings");
			break; 
			case 'extensions': $title = tr("Extensions");      break; 
			case 'themes':     $title = tr("Themes");          break;
			case 'tools':      $title = tr("Tools");           break;
			case 'phpinfo':    $title = tr("PHP Information"); break;
			case 'journal':    $title = tr("System Journal");  break;
			case 'update':     $title = tr("Update");          break;
			default:           $title = tr("Dashboard");       break;
		}
		ControlPanel::SetTitle($title);
	}
}
?>