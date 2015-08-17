<?php
use uCMS\Core\Settings;
use uCMS\Core\Page;
use uCMS\Core\Debug;
use uCMS\Core\uCMS;
use uCMS\Core\Database\Query;
use uCMS\Core\Extensions\Theme;
use uCMS\Core\Extensions\Entries\Entry;
use uCMS\Core\Extensions\Users\User;
// get entry types aliases and select $action type
// if action is not home page, then entries will have url prefix
$type = $prefix = Page::GetCurrent()->getAction();
if( $prefix === Page::INDEX_ACTION ){
	$prefix = "";
}
$entriesAmount = Settings::Get('entries_amount');
$commentsEnabled = false;
$commentsCount = 0;
$comments = array();
$entries = array();

$query = new Query("{entry_types}");
$types = $query->select("*")->where()->condition('alias', '=', $type)->execute();
if( empty($types) ){
	$alias = mb_substr(Page::GetCurrent()->getURL(), 1);
	$entryQuery = new Query("{entries}");
	$entries = $entryQuery->select("*")->where()->condition("alias", "=", $alias)->execute();
	if( empty($entries) ){
		Theme::LoadErrorPage(uCMS::ERR_NOT_FOUND);
	}
	$isEntryPage = true;
}else{
	foreach ($types as $type) {
		$names[] = $type['type'];
	}
	$list = "?".str_repeat(",?", count($names)-1);
	// TODO: pagination
	$entryQuery = new Query("SELECT * FROM {entries} WHERE type IN ($list)", $names);
	$entries = $entryQuery->execute();
	$isEntryPage = false;
}
foreach ($entries as &$entry) {
	$entry = Entry::FromArray($entry);
}
unset($entry); // WTF?
?>