<?php
namespace uCMS\Core\Extensions\Menus;
use uCMS\Core\Extensions\Extension;
use uCMS\Core\Extensions\Users\Permission;
use uCMS\Core\Admin\ControlPanel;
use uCMS\Core\Block;

class Menus extends Extension{
	public function onLoad(){
		Permission::Register('manage menu links', tr('Manage Menu Links'), tr('Allow user to add, edit and delete menu links.'));
	}

	public function onAdminAction($action){
		ControlPanel::SetTitle(tr("Menu"));
	}

	protected function checkStage(){
		Block::Add('menu', 'header', 'ucms');
		return parent::checkStage();
	}

	protected function getSchemas(){
		$schemas['menus'] = [
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
		$homePageLink = (new MenuLink())->clean();
		$homePageLink->menu = 'navigation';
		$homePageLink->title = 'Home';
		$homePageLink->link = '';
		$homePageLink->status = 1;
		$homePageLink->owner = 'core';

		$dashboardAddUser = (new MenuLink())->clean();
		$dashboardAddUser->menu = 'dashboard';
		$dashboardAddUser->title = 'Add user';
		$dashboardAddUser->link = 'admin/users/add';
		$dashboardAddUser->status = 1;
		$dashboardAddUser->owner = 'admin';

		$dashboardAddEntry = (new MenuLink())->clean();
		$dashboardAddEntry->menu = 'dashboard';
		$dashboardAddEntry->title = 'Add entry';
		$dashboardAddEntry->link = 'admin/entries/add';
		$dashboardAddEntry->status = 1;
		$dashboardAddEntry->owner = 'admin';

		$homePageLink->create();
		$dashboardAddUser->create();
		$dashboardAddEntry->create();
		return false;
	}
}
?>