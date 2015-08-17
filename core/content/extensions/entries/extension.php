<?php
namespace uCMS\Core\Extensions\Entries;
use uCMS\Core\Page;
use uCMS\Core\Extensions\Theme;
use uCMS\Core\Block;
use uCMS\Core\Admin\ControlPanel;
use uCMS\Core\uCMS;
use uCMS\Core\Settings;
use uCMS\Core\Database\Query;
class Entries extends \uCMS\Core\Extensions\Extension implements \uCMS\Core\Extensions\IExtension {

	public function onLoad(){
	}
	
	public function onInstall($stage){
		Block::Add("entries-list");
		Settings::Add('entries_amount', 0);
		Settings::Add('entry_types_amount', 2);
		Settings::Add('categories_amount', 1);
	}

	public function onUninstall(){

	}

	public function onShutdown(){

	}
	
	public function onAction($action){
		
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
}
?>