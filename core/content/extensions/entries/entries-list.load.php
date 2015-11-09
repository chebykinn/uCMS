<?php
use uCMS\Core\Settings;
use uCMS\Core\Page;
use uCMS\Core\Debug;
use uCMS\Core\uCMS;
use uCMS\Core\Database\Query;
use uCMS\Core\Extensions\Theme;
use uCMS\Core\Extensions\Extension;
use uCMS\Core\Extensions\Entries\Entry;
use uCMS\Core\Extensions\Entries\EntryType;
use uCMS\Core\Extensions\Users\User;
// get entry types aliases and select $action type
// if action is not home page, then entries will have url prefix
$prefix = Page::GetCurrent()->getAction();
$entriesAmount = (int)Settings::Get('entries_amount');
$commentsEnabled = false;
$commentsCount = 0;
$comments = array();
$entries = array();
$isEntryPage = false;
if( $prefix === Page::INDEX_ACTION ){
	$prefix = "";
}

// TODO: consider exclude this block from these actions
if ( empty($prefix) || ( !in_array($prefix, Extension::GetUsedActions()) || $prefix == Entry::ACTION ) ){
	$found = false;
	$limit = Settings::Get('entries_per_page');
	if( empty($prefix) ){
		$prefix = Settings::Get('list_entry_type');
	}
	$alias = Page::GetCurrent()->getActionValue();

	if( $prefix == Entry::ACTION && !empty($alias) ){
		$eid = intval($alias);
		$entry = (new Entry())->find($eid);
	}

	if( !isset($entry) ){
		$entry = (new Entry())->find(array('alias' => $prefix, 'limit' => 1));
	}

	if( !empty($alias) && !isset($entry) ){
		$entry = (new Entry())->find(array('type' => $prefix, 'alias' => $alias, 'limit' => 1));
	}

	if( $entry !== NULL ){
		$isEntryPage = true;
		$comments = $entry->commentsList;
		$entries[] = $entry;
		$found = true;
	}

	if( !$found && empty($alias) ){
		$entries = (new Entry())->find(array('type' => $prefix, 'orders' => array('changed' => 'desc'), 'limit' => $limit));
		if ( !empty($entries) ){
			$found = true;
		}
	}

	if( !$found ){
		Theme::LoadErrorPage(uCMS::ERR_NOT_FOUND);	
	}
} 
?>