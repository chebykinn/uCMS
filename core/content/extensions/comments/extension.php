<?php
namespace uCMS\Core\Extensions\Comments;
use uCMS\Core\Extensions\Extension;
use uCMS\Core\Extensions\ExtensionHandler;
use uCMS\Core\Extensions\ExtensionInterface;
use uCMS\Core\Admin\ControlPanel;
use uCMS\Core\Installer;
use uCMS\Core\Extensions\Users\Permission;
use uCMS\Core\Extensions\Users\Group;
use uCMS\Core\Database\Query;
class Comments extends Extension{
	public function onLoad(){
		Permission::Register('manage comments', $this->tr('Manage Comments'), $this->tr('Allow user to add, edit and delete comments.'));
	}

	public function onAdminAction($action){
		ControlPanel::SetTitle($this->tr("Comments"));
	}

	protected function getSchemas(){
		$schemas['comments'] = [
			'fields' => [
				'cid' => [
					'type' => 'serial',
					'not null' => true
				],
				'eid' => [
					'type' => 'int',
					'not null' => true
				],
				'parent' => [
					'type' => 'int',
					'default' => 0
				],
				'uid' => [
					'type' => 'int',
					'not null' => true
				],
				'content' => [
					'type' => 'text',
					'size' => 'big',
					'not null' => true
				],
				'status' => [
					'type' => 'int',
					'size' => 'tiny',
					'not null' => true
				],
				'title' => [
					'type' => 'varchar',
					'size' => 'big',
					'not null' => true
				],
				'name' => [
					'type' => 'varchar',
					'size' => 'big',
					'not null' => true
				],
				'email' => [
					'type' => 'varchar',
					'size' => 'big',
					'not null' => true
				],
				'ip' => [
					'type' => 'varchar',
					'not null' => true
				],
				'rating' => [
					'type' => 'int',
					'default' => 0
				],
				'site' => [
					'type' => 'varchar',
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
			'primary key' => 'cid'
		];
		return $schemas;
	}
}
?>