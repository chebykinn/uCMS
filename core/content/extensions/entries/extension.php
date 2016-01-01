<?php
namespace uCMS\Core\Extensions\Entries;
use uCMS\Core\Page;
use uCMS\Core\Extensions\Theme;
use uCMS\Core\Extensions\Extension;
use uCMS\Core\Extensions\ExtensionHandler;
use uCMS\Core\Block;
use uCMS\Core\Admin\ControlPanel;
use uCMS\Core\uCMS;
use uCMS\Core\Session;
use uCMS\Core\Setting;
use uCMS\Core\Database\Query;
use uCMS\Core\Installer;
use uCMS\Core\Extensions\Users\Permission;
use uCMS\Core\Extensions\Users\Group;

class Entries extends Extension{

	public function onLoad(){
		Permission::Register('manage entries', $this->tr('Manage Entries'), $this->tr('Allow user to add, edit and delete entries.'));
	}

	private function addBlocks(){
		$list = (new Block())->emptyRow();
		$list->name = "entries-list";
		$list->region = "content";
		$list->theme = Theme::DEFAULT_THEME;
		$list->visibility = Block::SHOW_MANUAL;
		$list->status = Block::ENABLED;
		$list->create();
	}

	protected function checkStage(){
		$status = parent::checkStage();
		$this->addBlocks();
		return $status;
	}

	public function onAdminAction($action){
		$title = $this->tr("Entries");
		switch ($action) {
			case 'categories':
				$title = $this->tr("Categories");
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
			'primary key' => 'eid',
			'unique keys' => ['entry' => ['alias', 'type']]
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
		$article = (new EntryType())->emptyRow();
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

		$page = (new EntryType())->emptyRow();
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
		$uncategorized = (new Term())->emptyRow();

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