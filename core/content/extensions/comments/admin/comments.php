<?php
use uCMS\Core\Admin\ManagePage;
use uCMS\Core\Admin\ManageTable;
use uCMS\Core\Extensions\Comments\Comment;
use uCMS\Core\Settings;
use uCMS\Core\Tools;
$page = new ManagePage();
$table = new ManageTable();
$page->doActions();

$table->addSelectColumn('manage comments');
$table->addColumn(tr('Content'), true, 'manage comments', 0, true);
$table->addColumn(tr('Author'), true, 'manage comments', 0, true);
$table->addColumn(tr('Entry'), true, 'manage comments', 0, true);
$table->addColumn(tr('Email'), true, 'manage comments', 0, true);
$table->addColumn(tr('IP'), true, 'manage comments', 0, true);
$table->addColumn(tr('Added'), true, 'manage comments', 0, true);

$limit = Settings::Get('per_page');
$comments = (new Comment())->find(array('limit' => $limit));
foreach ($comments as $comment) {
	$table->setInfo("idKey", $comment->fid);
	$table->setInfo("status", $comment->status);
	$table->addRow( array(
			$comment->content,
			$comment->name,
			$comment->entry->title,
			$comment->email,
			$comment->ip,
			Tools::FormatTime($comment->created),
		)
	);
}
$table->printTable();
?>