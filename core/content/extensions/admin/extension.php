<?php
namespace uCMS\Core\Extensions\Admin;
use uCMS\Core\Admin\ControlPanel;
use uCMS\Core\Block;
use uCMS\Core\Settings;
use uCMS\Core\Installer;
use uCMS\Core\Page;
use uCMS\Core\Extensions\Users\Permission;
use uCMS\Core\Extensions\Users\Group;
use uCMS\Core\Database\Query;
class Admin extends \uCMS\Core\Extensions\Extension{
	const PERMISSIONS_AMOUNT = 6;
	public function onLoad(){
		Permission::Register('access site', tr('Access Site'), tr('Allow user to view site pages.'));
		Permission::Register('access site in maintenance mode', tr('Access Site in maintenance mode.'), tr('Allow user visit site in maintenance mode.'));
		Permission::Register('access control panel', tr('Access Control Panel'), tr('Allow user to view administration panel.'));
		Permission::Register('manage extensions', tr('Manage Extensions'), tr('Allow user to add, edit and delete extensions.'));
		Permission::Register('manage themes', tr('Manage Themes'), tr('Allow user to add, edit and delete themes.'));
		Permission::Register('update core settings', tr('Update Core Settings'), tr('Allow user to change core settings using control panel.'));
	}
	
	protected function checkStage(){
		$status = parent::checkStage();
		Block::Add("last-added", "dashboard-right-side", "admin", 0, Block::SHOW_LISTED, ControlPanel::ACTION.'/'.Page::INDEX_ACTION);
		Block::Add("stats", "dashboard-left-side", "admin", 0, Block::SHOW_LISTED, ControlPanel::ACTION.'/'.Page::INDEX_ACTION);
		Block::Add("quick-actions", "dashboard-left-side", "admin", 1, Block::SHOW_LISTED, ControlPanel::ACTION.'/'.Page::INDEX_ACTION);
		$defaultModels = [
			'Articles' => [
				'owner' => 'entries',
				'name' => '\\uCMS\\Core\\Extensions\\Entries\\Entry', 
				'conditions' => ['type' => 'article'],
				'template' => 'templates/last.php'
			],
			'Comments' => [
				'owner' => 'comments',
				'name' => '\\uCMS\\Core\\Extensions\\Comments\\Comment',
				'conditions' => [],
				'template' => 'templates/last.php'
			],
			'Pages' => [
				'owner' => 'entries',
				'name' => '\\uCMS\\Core\\Extensions\\Entries\\Entry',
				'conditions' => ['type' => 'page'],
				'template' => 'templates/last.php'
			],
			'Users' => [
				'owner' => 'users',
				'name' => '\\uCMS\\Core\\Extensions\\Users\\User',
				'conditions' => [],
				'template' => 'templates/last.php'
			]
		];
		Settings::Update('last_added_models', json_encode($defaultModels));
		return $status;
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
			case 'blocks':     $title = tr("Blocks");          break;
			default:           $title = tr("Dashboard");       break;
		}
		ControlPanel::SetTitle($title);
	}
}
?>