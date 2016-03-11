<?php
namespace uCMS\Core\Extensions\Menus;
use uCMS\Core\Extensions\Extension;
use uCMS\Core\Extensions\Theme;
use uCMS\Core\Extensions\Users\Permission;
use uCMS\Core\Admin\ControlPanel;
use uCMS\Core\Block;
use uCMS\Core\Page;

class Menus extends Extension{
	public function onLoad(){
		Permission::Register('manage menu links', $this->tr('Manage Menu Links'), $this->tr('Allow user to add, edit and delete menu links.'), $this);
	}

	public function onAdminAction($action){
		ControlPanel::SetTitle($this->tr("Menu"));
	}

	private function addBlocks(){
		$navMenu = (new Block($this))->emptyRow();
		$navMenu->name = "navigation";
		$navMenu->region = "navigation";
		$navMenu->theme = Theme::DEFAULT_THEME;
		$navMenu->status = Block::ENABLED;
		$navMenu->create();
		$quickActions = (new Block($this))->emptyRow();
		$quickActions->name = "quick-actions";
		$quickActions->region = "dashboard-left-side";
		$quickActions->actions = ControlPanel::ACTION.'/'.Page::INDEX_ACTION;
		$quickActions->position = 1;
		$quickActions->theme = ControlPanel::THEME;
		$quickActions->status = Block::ENABLED;
		$quickActions->visibility = Block::SHOW_LISTED;
		$quickActions->create();
	}

	protected function checkStage(){
		$this->addBlocks();
		return parent::checkStage();
	}

	protected function getSchemas(){
		$schemas['menus'] = [
			'fields' => [
				'menu' => [
					'type' => 'varchar',
					'size' => 'big',
					'not null' => true
				],
				'title' => [
					'type' => 'varchar',
					'size' => 'big',
					'not null' => true
				],
				'description' => [
					'type' => 'text',
					'size' => 'big'
				]
			],
			'primary key' => 'menu'
		];

		$schemas['menu_links'] = [
			'fields' => [
				'lid' => [
					'type' => 'serial',
					'not null' => true
				],
				'parent' => [
					'type' => 'int',
					'default' => 0
				],
				'menu' => [
					'type' => 'varchar',
					'size' => 'big',
					'not null' => true
				],
				'title' => [
					'type' => 'varchar',
					'size' => 'big',
					'not null' => true
				],

				'link' => [
					'type' => 'varchar',
					'size' => 'big',
					'not null' => true
				],
				'status' => [
					'type' => 'int',
					'size' => 'tiny',
					'default' => 0
				],
				'external' => [
					'type' => 'int',
					'size' => 'tiny',
					'default' => 0
				],
				'owner' => [
					'type' => 'varchar',
					'size' => 'big',
					'not null' => true
				],
				'permission' => [
					'type' => 'varchar',
					'size' => 'big'
				],
				'uid' => [
					'type' => 'int',
					'default' => 0
				],
				'position' => [
					'type' => 'int',
					'default' => 0
				],
				'created' => [
					'type' => 'int',
					'default' => time()
				],
				'changed' => [
					'type' => 'int',
					'default' => time()
				]
			],
			'primary key' => 'lid'
		];
		return $schemas;
	}

	protected function fillTable($table){
		switch ($table) {
			case 'menus':
				$navMenu = (new Menu())->emptyRow();
				$dashMenu = (new Menu())->emptyRow();
				$navMenu->menu = "navigation";
				$navMenu->title = "Navigation";
				$navMenu->description = "Default main navigation menu.";
				$navMenu->create();
		
				$dashMenu->menu = "quick-actions";
				$dashMenu->title = "Quick Actions";
				$dashMenu->description = "Menu for quick actions block, displayed at the Control Panel's dashboard.";
				$dashMenu->create();	
			break;
			
			case 'menu_links':
				$homePageLink = (new MenuLink())->emptyRow();
				$homePageLink->menu = 'navigation';
				$homePageLink->title = 'Home';
				$homePageLink->link = '';
				$homePageLink->status = 1;
				$homePageLink->owner = 'core';

				$dashboardAddUser = (new MenuLink())->emptyRow();
				$dashboardAddUser->menu = 'quick-actions';
				$dashboardAddUser->title = 'Add user';
				$dashboardAddUser->link = 'admin/users/add';
				$dashboardAddUser->status = 1;
				$dashboardAddUser->owner = 'admin';

				$dashboardAddEntry = (new MenuLink())->emptyRow();
				$dashboardAddEntry->menu = 'quick-actions';
				$dashboardAddEntry->title = 'Add entry';
				$dashboardAddEntry->link = 'admin/entries/add';
				$dashboardAddEntry->status = 1;
				$dashboardAddEntry->owner = 'admin';

				$homePageLink->create();
				$dashboardAddUser->create();
				$dashboardAddEntry->create();
			break;
		}
	}
}
?>