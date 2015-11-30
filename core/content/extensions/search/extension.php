<?php
namespace uCMS\Core\Extensions\Search;
use uCMS\Core\Extensions\Extension;
use uCMS\Core\Extensions\ExtensionInterface;
use uCMS\Core\Admin\ControlPanel;
use uCMS\Core\Installer;

class Search extends Extension{
	public function onAdminAction($action){
		ControlPanel::SetTitle(tr('Search'));
	}
}
?>