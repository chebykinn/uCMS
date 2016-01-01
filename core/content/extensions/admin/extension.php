<?php
namespace uCMS\Core\Extensions\Admin;
use uCMS\Core\Admin\ControlPanel;
use uCMS\Core\Block;
use uCMS\Core\Setting;
use uCMS\Core\Installer;
use uCMS\Core\Page;
use uCMS\Core\Extensions\Users\Permission;
use uCMS\Core\Extensions\Users\Group;
use uCMS\Core\Database\Query;
class Admin extends \uCMS\Core\Extensions\Extension{

	public function onLoad(){
		Permission::Register('access site', $this->tr('Access Site'), $this->tr('Allow user to view site pages.'));
		Permission::Register('access site in maintenance mode', $this->tr('Access Site in maintenance mode.'), $this->tr('Allow user visit site in maintenance mode.'));
		Permission::Register('access control panel', $this->tr('Access Control Panel'), $this->tr('Allow user to view administration panel.'));
		Permission::Register('manage extensions', $this->tr('Manage Extensions'), $this->tr('Allow user to add, edit and delete extensions.'));
		Permission::Register('manage themes', $this->tr('Manage Themes'), $this->tr('Allow user to add, edit and delete themes.'));
		Permission::Register('manage blocks', $this->tr('Manage Blocks'), $this->tr('Allow user to add, edit and delete blocks.'));
		Permission::Register('update core settings', $this->tr('Update Core settings'), $this->tr('Allow user to change core settings using control panel.'));
	}

	private function addBlocks(){
		$actions = ControlPanel::ACTION.'/'.Page::INDEX_ACTION;

		$lastAdded = (new Block())->emptyRow();
		$stats = (new Block())->emptyRow();

		$lastAdded->name = "last-added";
		$stats->name = "stats";

		$lastAdded->visibility = $stats->visibility = Block::SHOW_LISTED;
		$lastAdded->actions = $stats->actions = $actions;
		$lastAdded->theme = $stats->theme = ControlPanel::THEME;
		$lastAdded->status = $stats->status = Block::ENABLED;
		$stats->region = "dashboard-left-side";

		$lastAdded->region = "dashboard-right-side";

		$lastAdded->create();
		$stats->create();
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
		Setting::Update('last_added_models', json_encode($defaultModels));
	}
	
	protected function checkStage(){
		$status = parent::checkStage();
		$this->addBlocks();
		return $status;
	}

	public function onAdminAction($action){
		switch ($action) {
			case 'home':       $title = $this->tr('Dashboard');       break;
			case 'settings':
				if ( isset($_POST['settings']) ){
					ControlPanel::UpdateSettings();
				}
				$title = $this->tr("Settings");
			break; 
			case 'extensions': $title = $this->tr("Extensions");      break; 
			case 'themes':     $title = $this->tr("Themes");          break;
			case 'tools':      $title = $this->tr("Tools");           break;
			case 'phpinfo':    $title = $this->tr("PHP Information"); break;
			case 'journal':    $title = $this->tr("System Journal");  break;
			case 'update':     $title = $this->tr("Update");          break;
			case 'blocks':     $title = $this->tr("Blocks");          break;
			default:           $title = $this->tr("Dashboard");       break;
		}
		ControlPanel::SetTitle($title);
	}
}
?>