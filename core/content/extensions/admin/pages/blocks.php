<?php
use uCMS\Core\Admin\ManagePage;
use uCMS\Core\Admin\ManageTable;
use uCMS\Core\Database\Query;
use uCMS\Core\Admin\ControlPanel;
use uCMS\Core\Extensions\Theme;
use uCMS\Core\Extensions\ThemeHandler;
use uCMS\Core\Setting;
use uCMS\Core\Page;
use uCMS\Core\Tools;
use uCMS\Core\Notification;
use uCMS\Core\Block;
$page = new ManagePage();
$table = new ManageTable();

$page->doActions();

$blocks = (new Block())->find(['limit' => Setting::Get(Setting::PER_PAGE), 'orders' => ['theme' => 'ASC']]);

$table->addSelectColumn('manage blocks');
$table->addColumn($this->tr('Name'), true, 'manage blocks', 0, true);
$table->addColumn($this->tr('Title'), true, 'manage blocks', 0, false);
$table->addColumn($this->tr('Theme'), true, 'manage blocks', 0, true);
$table->addColumn($this->tr('Region'), true, 'manage blocks', 0, true);
$table->addColumn($this->tr('Owner'), true, 'manage blocks', 0, false);
$table->addColumn($this->tr('Actions'), true, 'manage blocks', 0, false);
$table->setInfo("amount", Setting::Get(Setting::BLOCKS_AMOUNT));
foreach ($blocks as $block) {
	$table->setInfo('idKey', $block->bid);
	if( $block->status == Block::ENABLED ){
		if( $block->visibility == Block::SHOW_LISTED ){
			$actionsMsg = $this->tr("Displayed at:")."<br>".$block->actions;
		}
	
		if( $block->visibility == Block::SHOW_EXCEPT ){
			if( !empty($block->actions) ){
				$actionsMsg = $this->tr("Displayed at every page except:")."<br>".$block->actions;
			}else{
				$actionsMsg = $this->tr("Displayed at every page.");
			}
		}
	
		if( $block->visibility == Block::SHOW_MANUAL ){
			$actionsMsg = $this->tr("Block controls visibility manually.");
		}
	}else{
		$actionsMsg = $this->tr("Block is disabled.");
	}

	$titleMsg = empty($block->title) ? $this->tr("None") : $block->title;
	$table->addRow(
		[
			$block->name,
			$titleMsg,
			$block->theme,
			$block->region,
			$block->owner,
			$actionsMsg,
		]
	);
}

$table->printTable();

?>