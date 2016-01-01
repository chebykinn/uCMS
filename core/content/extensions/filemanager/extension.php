<?php
namespace uCMS\Core\Extensions\FileManager;
use uCMS\Core\Admin\ControlPanel;
use uCMS\Core\Extensions\Users\Permission;
use uCMS\Core\Extensions\Users\Group;
use uCMS\Core\Database\Query;
use uCMS\Core\Installer;
use uCMS\Core\Page;
use uCMS\Core\Localization\Language;
use uCMS\Core\Extensions\ExtensionHandler;
use uCMS\Core\Extensions\ThemeHandler;
class FileManager extends \uCMS\Core\Extensions\Extension {
	private $dirs;
	public function onLoad(){
		$this->dirs = [
		File::CONTENT_PATH,
		File::UPLOADS_PATH,
		Language::PATH,
		ExtensionHandler::PATH,
		ThemeHandler::PATH];
		Permission::Register('manage files', $this->tr('Manage Files'), $this->tr('Allow user to add, edit and delete files.'));
	}

	public function onAdminAction($action){
		ControlPanel::SetTitle($this->tr('File Manager'));
	}

	protected function checkStage(){
		$needDirs = false;
		foreach ($this->dirs as $dir) {
			$path = ABSPATH.$dir;
			if ( !file_exists($path) ) {
				$needDirs = true;
			}
		}
		$status = parent::checkStage();
		if( !$status && $needDirs ){
			return true;
		}
		return $status;
	}

	protected function prepareStage(){
		$result = $this->createDirectories();
		if( !$result ){
			Installer::GetInstance()->setTitle($this->tr('Error creating directories'));
			return ExtensionHandler::NEED_USER_INPUT;
		}
		return parent::prepareStage();
	}

	protected function printStage(){
		$this->p('Installer is unable to create directories due to insufficient permissions.<br>Please make sure that following directories are created to continue installation:');
		print '<ul>';
		foreach ($this->dirs as $dir) {
			$path = ABSPATH.$dir;
			print "<li>$path</li>";
		}
		$check = Page::Install(Installer::CHECK_STAGE);
		print '</ul>';
		print '<br><a class="button" href="'.$check.'">'.$this->tr('Continue').'</a>';
	}

	private function createDirectories(){
		foreach ($this->dirs as $dir) {
			$path = ABSPATH.$dir;
			$parent = dirname($path);
			if( !is_writable($parent) ) return false;
			if ( !file_exists($path) ) {
				mkdir($path);
			}
		}
		return true;
	}

	protected function getSchemas(){
		$schemas['uploaded_files'] = [
			'fields' => [
				'fid' => [
					'type' => 'serial',
					'not null' => true
				],
				'uid' => [
					'type' => 'int',
					'not null' => true
				],
				'name' => [
					'type' => 'varchar',
					'size' => 'big',
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
					'not null' => true
				],
				'location' => [
					'type' => 'varchar',
					'size' => 'big',
					'not null' => true
				],
				'size' => [
					'type' => 'int',
					'size' => 'big',
					'not null' => true
				],
				'changed' => [
					'type' => 'int',
					'default' => time()
				],
			],
			'primary key' => 'fid'
		];
		return $schemas;
	}
}
?>