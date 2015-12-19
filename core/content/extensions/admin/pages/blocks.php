<?php
use uCMS\Core\Admin\ManagePage;
use uCMS\Core\Admin\ManageTable;
use uCMS\Core\Database\Query;
use uCMS\Core\Admin\ControlPanel;
use uCMS\Core\Extensions\Theme;
use uCMS\Core\Extensions\ThemeHandler;
use uCMS\Core\Settings;
use uCMS\Core\Page;
use uCMS\Core\Tools;
use uCMS\Core\Notification;
use uCMS\Core\Block;
$page = new ManagePage();
$table = new ManageTable();

$page->doActions();

$blocks = (new Block())->find(['limit' => Settings::Get(Settings::PER_PAGE), 'orders' => ['theme' => 'ASC']]);

$table->addSelectColumn('manage blocks');
$table->addColumn(tr('Name'), true, 'manage blocks', 0, true);
$table->addColumn(tr('Title'), true, 'manage blocks', 0, false);
$table->addColumn(tr('Theme'), true, 'manage blocks', 0, true);
$table->addColumn(tr('Region'), true, 'manage blocks', 0, true);
$table->addColumn(tr('Owner'), true, 'manage blocks', 0, false);
$table->addColumn(tr('Actions'), true, 'manage blocks', 0, false);
$table->setInfo("amount", Settings::Get(Settings::BLOCKS_AMOUNT));
foreach ($blocks as $block) {
	$table->setInfo('idKey', $block->bid);
	if( $block->status == Block::ENABLED ){
		if( $block->visibility == Block::SHOW_LISTED ){
			$actionsMsg = tr("Displayed at:")."<br>".$block->actions;
		}
	
		if( $block->visibility == Block::SHOW_EXCEPT ){
			if( !empty($block->actions) ){
				$actionsMsg = tr("Displayed at every page except:")."<br>".$block->actions;
			}else{
				$actionsMsg = tr("Displayed at every page.");
			}
		}
	
		if( $block->visibility == Block::SHOW_MANUAL ){
			$actionsMsg = tr("Block controls visibility manually.");
		}
	}else{
		$actionsMsg = tr("Block is disabled.");
	}

	$titleMsg = empty($block->title) ? tr("None") : $block->title;
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