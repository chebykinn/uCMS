<?php
namespace uCMS\Core\Extensions\Entries;
use uCMS\Core\Page;
use uCMS\Core\Extensions\Theme;
use uCMS\Core\Extensions\ExtensionInterface;
use uCMS\Core\Extensions\ExtensionHandler;
use uCMS\Core\Block;
use uCMS\Core\Admin\ControlPanel;
use uCMS\Core\uCMS;
use uCMS\Core\Session;
use uCMS\Core\Settings;
use uCMS\Core\Database\Query;
use uCMS\Core\Installer;
use uCMS\Core\Extensions\Users\Permission;
use uCMS\Core\Extensions\Users\Group;

class Entries extends \uCMS\Core\Extensions\Extension implements \uCMS\Core\Extensions\ExtensionInterface {

	public function onLoad(){
		Permission::Register('manage entries', tr('Manage Entries'), tr('Allow user to add, edit and delete entries.'));
	}

	protected function checkStage(){
		$status = parent::checkStage();
		Block::Add("entries-list", "content", "ucms", -1, Block::SHOW_MANUAL);
		return $status;
	}

	public function onAdminAction($action){
		$title = tr("Entries");
		switch ($action) {
			case 'categories':
				$title = tr("Categories");
			break;
		}
		ControlPanel::SetTitle($title);
	}

	protected function getSchemas(){
		$schemas['entries'] = [
			'fields' => [
				'eid' => [
					'type' => 'serial',
					'not null' => true
				],
				'uid' => [
					'type' => 'int',
					'not null' => true
				],
				'type' => [
					'type' => 'varchar',
					'size' => 'big',
					'not null' => true
				],
				'status' => [
					'type' => 'int',
					'size' => 'tiny',
					'default' => 0
				],
				'comments' => [
					'type' => 'int',
					'size' => 'tiny',
					'default' => 0
				],
				'title' => [
					'type' => 'varchar',
					'size' => 'big',
					'not null' => true
				],
				'alias' => [
					'type' => 'varchar',
					'size' => 'big',
					'not null' => true
				],
				'content' => [
					'type' => 'text',
					'size' => 'big',
					'not null' => true
				],
				'language' => [
					'type' => 'varchar',
					'size' => 'big',
					'not null' => true
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
			'primary key' => 'eid'
		];

		$schemas['entry_access'] = [
			'fields' => [
				'eid' => [
					'type' => 'int',
					'not null' => true
				],
				'uid' => [
					'type' => 'int',
					'not null' => true
				],
				'gid' => [
					'type' => 'int',
					'not null' => true
				],
				'allow' => [
					'type' => 'int',
					'size' => 'tiny',
					'not null' => true
				],
			],
			'primary key' => ['eid', 'uid']
		];

		$schemas['entry_types'] = [
			'fields' => [
				'type' => [
					'type' => 'varchar',
					'size' => 'big',
					'not null' => true
				],
				'name' => [
					'type' => 'varchar',
					'size' => 'big',
					'not null' => true
				],
				'description' => [
					'type' => 'text',
					'size' => 'big',
					'not null' => true
				],
				'owner' => [
					'type' => 'varchar',
					'size' => 'big',
					'not null' => true
				],
				'alias' => [
					'type' => 'varchar',
					'size' => 'big',
					'not null' => true
				],
				'comments' => [
					'type' => 'int',
					'size' => 'tiny',
					'default' => 0
				],
				'uid' => [
					'type' => 'int',
					'default' => 0
				],
				'gid' => [
					'type' => 'int',
					'default' => 0
				],
				'permission' => [
					'type' => 'varchar',
					'size' => 'big',
					'default' => ''
				],
				'terms' => [
					'type' => 'int',
					'size' => 'tiny',
					'default' => 0
				],
				'menu' => [
					'type' => 'varchar',
					'size' => 'big',
					'default' => ''
				]
			],
			'primary key' => 'type',
			'unique keys' => ['name' => 'name']
		];

		$schemas['terms'] = [
			'fields' => [
				'tid' => [
					'type' => 'serial',
					'not null' => true
				],
				'name' => [
					'type' => 'varchar',
					'size' => 'big',
					'not null' => true
				],
				'alias' => [
					'type' => 'varchar',
					'size' => 'big',
					'not null' => true
				],
				'description' => [
					'type' => 'text',
					'size' => 'big',
					'not null' => true
				],
				'position' => [
					'type' => 'int',
					'default' => 0
				],
				'type' => [
					'type' => 'varchar',
					'size' => 'big',
					'not null' => true
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
			'primary key' => 'tid'
		];

		$schemas['term_hierarchy'] = [
			'fields' => [
				'tid' => [
					'type' => 'int',
					'not null' => true
				],
				'parent' => [
					'type' => 'int',
					'not null' => true
				],
			],
			'primary key' => ['tid', 'parent']
		];

		$schemas['term_taxonomy'] = [
			'fields' => [
				'eid' => [
					'type' => 'int',
					'not null' => true
				],
				'tid' => [
					'type' => 'int',
					'not null' => true
				],
			],
			'primary key' => ['eid', 'tid']
		];
		return $schemas;
	}

	protected function fillTable($table){
		switch ($table) {
			case 'entry_types':
				$this->addDefaultTypes();
			break;
			
			case 'terms':
				$this->addDefaultTerm();
			break;
		}
		return false;
	}

	private function addDefaultTypes(){
		$article = (new EntryType())->clean();
		$article->type        = 'article';
		$article->name        = 'Article';
		$article->description = 'Default entry type for articles that will be displayed on home page.';
		$article->owner       = 'entries';
		$article->alias       = 'home';
		$article->comments    = 1;
		$article->uid         = 0;
		$article->gid         = 0;
		$article->permission  = '';
		$article->terms       = 1;
		$article->menu        = '';

		$page = (new EntryType())->clean();
		$page->type        = 'page';
		$page->name        = 'Page';
		$page->description = 'Default entry type for pages that will not be listed, but shown in navigation menu.';
		$page->owner       = 'entries';
		$page->alias       = 'home';
		$page->comments    = 0;
		$page->uid         = 0;
		$page->gid         = 0;
		$page->permission  = '';
		$page->terms       = 0;
		$page->menu        = 'navigation';

		$article->create();
		$page->create();
	}

	private function addDefaultTerm(){
		$uncategorized = (new Term())->clean();

		$uncategorized->tid = 1;
		$uncategorized->name = 'Uncategorized';
		$uncategorized->alias = 'uncategorized';
		$uncategorized->description = 'Default category for entries that don\'t have one.';
		$uncategorized->position = 0;
		$uncategorized->type = 'Category';
		$uncategorized->created = time();
		$uncategorized->changed = time();

		$uncategorized->create();
	}
}
?>