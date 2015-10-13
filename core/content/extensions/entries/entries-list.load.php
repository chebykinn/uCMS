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
if ( empty($prefix) || !in_array($prefix, Extension::GetUsedActions()) ){
	if( empty($prefix) ){
		$prefix = Settings::Get('list_entry_type');
	}

	$type = (new EntryType())->find($prefix);
	if ( $type != NULL ){
		$entries = $type->entries;
	}else{
		// Try to get entry page
		$entries = (new Entry())->find(array('alias' => $prefix));
		if ( empty($entries) ){
			Theme::LoadErrorPage(uCMS::ERR_NOT_FOUND);
		}
		$isEntryPage = true;
	}
} 
?>